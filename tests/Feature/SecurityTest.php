<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_blocks_sql_injection_attempts()
    {
        $user = User::factory()->create(['user_type' => 'customer']);
        $token = auth('api')->login($user);

        $maliciousInput = "'; DROP TABLE users; --";

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/bookings', [
            'chalet_id' => 1,
            'check_in_date' => '2024-12-01',
            'check_out_date' => '2024-12-05',
            'guests_count' => 2,
            'special_requests' => $maliciousInput,
            'total_amount' => 1000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['special_requests']);
    }

    /** @test */
    public function it_blocks_xss_attempts()
    {
        $user = User::factory()->create(['user_type' => 'customer']);
        $token = auth('api')->login($user);

        $xssPayload = '<script>alert("XSS")</script>';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/bookings', [
            'chalet_id' => 1,
            'check_in_date' => '2024-12-01',
            'check_out_date' => '2024-12-05',
            'guests_count' => 2,
            'special_requests' => $xssPayload,
            'total_amount' => 1000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['special_requests']);
    }

    /** @test */
    public function it_enforces_rate_limiting()
    {
        $user = User::factory()->create(['user_type' => 'customer']);
        $token = auth('api')->login($user);

        // Make multiple requests quickly
        for ($i = 0; $i < 125; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->getJson('/api/chalets');

            if ($i < 120) {
                $this->assertNotEquals(429, $response->getStatusCode());
            }
        }

        // The 125th request should be rate limited
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/chalets');

        $response->assertStatus(429);
        $response->assertJsonStructure([
            'message',
            'retry_after',
            'max_attempts',
        ]);
    }

    /** @test */
    public function it_validates_file_uploads_securely()
    {
        $user = User::factory()->create(['user_type' => 'owner']);
        $token = auth('api')->login($user);

        // Create a malicious file
        $maliciousFile = UploadedFile::fake()->create('malicious.php', 100, 'application/x-php');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/chalets/test-chalet/images', [
            'images' => [$maliciousFile],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['images.0']);
    }

    /** @test */
    public function it_adds_security_headers()
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /** @test */
    public function it_blocks_path_traversal_attempts()
    {
        $user = User::factory()->create(['user_type' => 'customer']);
        $token = auth('api')->login($user);

        $pathTraversalPayload = '../../../etc/passwd';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/bookings', [
            'chalet_id' => 1,
            'check_in_date' => '2024-12-01',
            'check_out_date' => '2024-12-05',
            'guests_count' => 2,
            'special_requests' => $pathTraversalPayload,
            'total_amount' => 1000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['special_requests']);
    }

    /** @test */
    public function it_limits_input_length()
    {
        $user = User::factory()->create(['user_type' => 'customer']);
        $token = auth('api')->login($user);

        $longInput = str_repeat('A', 15000); // Exceeds 10000 char limit

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/bookings', [
            'chalet_id' => 1,
            'check_in_date' => '2024-12-01',
            'check_out_date' => '2024-12-05',
            'guests_count' => 2,
            'special_requests' => $longInput,
            'total_amount' => 1000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['special_requests']);
    }

    /** @test */
    public function it_sanitizes_input_data()
    {
        $user = User::factory()->create(['user_type' => 'customer']);
        $token = auth('api')->login($user);

        $htmlInput = '<b>Bold text</b> with <script>alert("xss")</script>';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/bookings', [
            'chalet_id' => 1,
            'check_in_date' => '2024-12-01',
            'check_out_date' => '2024-12-05',
            'guests_count' => 2,
            'special_requests' => $htmlInput,
            'total_amount' => 1000,
        ]);

        // Should either be rejected or sanitized
        if ($response->getStatusCode() === 201) {
            // If accepted, check that it was sanitized
            $booking = $response->json('data');
            $this->assertStringNotContainsString('<script>', $booking['special_requests']);
            $this->assertStringNotContainsString('<b>', $booking['special_requests']);
        } else {
            // Should be rejected due to security validation
            $response->assertStatus(422);
        }
    }

    /** @test */
    public function it_validates_image_file_types()
    {
        $user = User::factory()->create(['user_type' => 'owner']);
        $token = auth('api')->login($user);

        // Test valid image
        $validImage = UploadedFile::fake()->image('test.jpg', 800, 600);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/chalets/test-chalet/images', [
            'images' => [$validImage],
        ]);

        // Should not fail due to file type (might fail for other reasons like missing chalet)
        $this->assertNotEquals(422, $response->getStatusCode());

        // Test invalid file type
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/chalets/test-chalet/images', [
            'images' => [$invalidFile],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_enforces_file_size_limits()
    {
        $user = User::factory()->create(['user_type' => 'owner']);
        $token = auth('api')->login($user);

        // Create a file larger than 5MB
        $largeFile = UploadedFile::fake()->create('large.jpg', 6000, 'image/jpeg'); // 6MB

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/chalets/test-chalet/images', [
            'images' => [$largeFile],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['images.0']);
    }
}

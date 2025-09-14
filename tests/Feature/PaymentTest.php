<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Chalet;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;
    protected $owner;
    protected $chalet;
    protected $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['user_type' => 'customer']);
        $this->owner = User::factory()->create(['user_type' => 'owner']);
        $this->chalet = Chalet::factory()->create(['owner_id' => $this->owner->id]);
        $this->booking = Booking::factory()->create([
            'customer_id' => $this->customer->id,
            'chalet_id' => $this->chalet->id,
            'status' => 'confirmed',
            'total_amount' => 1000,
            'discount_amount' => 0,
        ]);
    }

    /** @test */
    public function it_can_process_credit_card_payment()
    {
        // Mock Moyasar API response
        Http::fake([
            'api.moyasar.com/v1/payments' => Http::response([
                'id' => 'pay_test_123456',
                'status' => 'paid',
                'amount' => 100000, // 1000 SAR in halalas
                'fee' => 2900, // 29 SAR fee
                'source' => [
                    'type' => 'creditcard',
                    'name' => 'John Doe',
                    'number' => '4111111111111111',
                    'message' => 'Approved',
                ],
                'created_at' => now()->toISOString(),
            ], 201),
        ]);

        $token = auth('api')->login($this->customer);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/bookings/{$this->booking->id}/payment", [
            'payment_method' => 'creditcard',
            'amount' => 1000,
            'card_number' => '4111111111111111',
            'card_holder_name' => 'John Doe',
            'expiry_month' => 12,
            'expiry_year' => 2025,
            'cvc' => '123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'payment_id',
                'moyasar_payment_id',
                'status',
            ],
        ]);

        // Verify payment was created in database
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'credit_card',
            'amount' => 1000,
            'transaction_id' => 'pay_test_123456',
        ]);
    }

    /** @test */
    public function it_validates_payment_data()
    {
        $token = auth('api')->login($this->customer);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/bookings/{$this->booking->id}/payment", [
            'payment_method' => 'creditcard',
            'amount' => 1000,
            // Missing required credit card fields
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'card_number',
            'card_holder_name',
            'expiry_month',
            'expiry_year',
            'cvc',
        ]);
    }

    /** @test */
    public function it_prevents_payment_for_unconfirmed_booking()
    {
        $this->booking->update(['status' => 'pending']);

        $token = auth('api')->login($this->customer);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/bookings/{$this->booking->id}/payment", [
            'payment_method' => 'creditcard',
            'amount' => 1000,
            'card_number' => '4111111111111111',
            'card_holder_name' => 'John Doe',
            'expiry_month' => 12,
            'expiry_year' => 2025,
            'cvc' => '123',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'لا يمكن الدفع إلا للحجوزات المؤكدة',
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_payments()
    {
        // Create existing completed payment
        Payment::factory()->create([
            'booking_id' => $this->booking->id,
            'status' => 'completed',
            'amount' => 1000,
        ]);

        $token = auth('api')->login($this->customer);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/bookings/{$this->booking->id}/payment", [
            'payment_method' => 'creditcard',
            'amount' => 1000,
            'card_number' => '4111111111111111',
            'card_holder_name' => 'John Doe',
            'expiry_month' => 12,
            'expiry_year' => 2025,
            'cvc' => '123',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'تم دفع هذا الحجز مسبقاً',
        ]);
    }

    /** @test */
    public function it_can_handle_moyasar_webhook()
    {
        $payment = Payment::factory()->create([
            'booking_id' => $this->booking->id,
            'transaction_id' => 'pay_test_123456',
            'status' => 'pending',
        ]);

        $webhookData = [
            'type' => 'payment_paid',
            'data' => [
                'id' => 'pay_test_123456',
                'status' => 'paid',
                'amount' => 100000,
                'fee' => 2900,
                'source' => [
                    'type' => 'creditcard',
                    'message' => 'Approved',
                ],
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ],
        ];

        $response = $this->postJson('/api/webhooks/moyasar', $webhookData);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        // Verify payment was updated
        $payment->refresh();
        $this->assertEquals('completed', $payment->status);
        $this->assertNotNull($payment->paid_at);
    }

    /** @test */
    public function it_can_process_refund()
    {
        $payment = Payment::factory()->create([
            'booking_id' => $this->booking->id,
            'status' => 'completed',
            'amount' => 1000,
            'transaction_id' => 'pay_test_123456',
            'payment_details' => [
                'moyasar_payment_id' => 'pay_test_123456',
            ],
        ]);

        // Mock Moyasar refund API response
        Http::fake([
            'api.moyasar.com/v1/payments/pay_test_123456/refund' => Http::response([
                'id' => 'ref_test_789012',
                'amount' => 100000,
                'status' => 'refunded',
                'created_at' => now()->toISOString(),
            ], 200),
        ]);

        $token = auth('api')->login($this->customer);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/payments/{$payment->id}/refund", [
            'amount' => 1000,
            'reason' => 'Customer requested cancellation',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'refund_id',
                'amount',
                'status',
            ],
        ]);

        // Verify payment was updated
        $payment->refresh();
        $this->assertEquals('refunded', $payment->status);
    }

    /** @test */
    public function it_can_get_payment_history()
    {
        Payment::factory()->count(3)->create([
            'booking_id' => $this->booking->id,
        ]);

        $token = auth('api')->login($this->customer);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/payments');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'booking_id',
                        'chalet_name',
                        'payment_method',
                        'amount',
                        'status',
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function it_validates_card_number_format()
    {
        $token = auth('api')->login($this->customer);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/bookings/{$this->booking->id}/payment", [
            'payment_method' => 'creditcard',
            'amount' => 1000,
            'card_number' => '1234', // Invalid card number
            'card_holder_name' => 'John Doe',
            'expiry_month' => 12,
            'expiry_year' => 2025,
            'cvc' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['card_number']);
    }

    /** @test */
    public function it_validates_cvc_format()
    {
        $token = auth('api')->login($this->customer);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/bookings/{$this->booking->id}/payment", [
            'payment_method' => 'creditcard',
            'amount' => 1000,
            'card_number' => '4111111111111111',
            'card_holder_name' => 'John Doe',
            'expiry_month' => 12,
            'expiry_year' => 2025,
            'cvc' => '12', // Invalid CVC
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['cvc']);
    }

    /** @test */
    public function it_can_get_refund_policies()
    {
        $token = auth('api')->login($this->customer);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/refund-policies');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'general',
                'processing_time',
                'fees',
            ],
        ]);
    }
}

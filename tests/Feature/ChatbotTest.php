<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure tests don't use stale cached routes.
        $this->artisan('route:clear');
    }

    public function test_chat_endpoint_proxies_to_rag_service_and_returns_success(): void
    {
        config()->set('services.rag.base_url', 'http://rag.local');
        config()->set('services.rag.proxy_secret', 'secret');

        Http::fake([
            'http://rag.local/rag/chat' => Http::response([
                'answer' => 'سياسة الاسترداد: يمكن الاسترداد الكامل قبل 24 ساعة.',
                'citations' => [
                    [
                        'doc_id' => 'policy:refund',
                        'chunk_id' => 'policy:refund:0',
                        'source' => 'db:policies',
                        'snippet' => 'يمكن الاسترداد الكامل إذا تم الإلغاء قبل 24 ساعة',
                    ],
                ],
                'confidence' => 0.8,
                'needs_human_handoff' => false,
                'fallback_message' => null,
            ], 200),
        ]);

        $user = User::factory()->create(['user_type' => User::TYPE_CUSTOMER]);
        $token = auth('api')->login($user);

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/chat', [
                'question' => 'ما هي سياسة الاسترداد؟',
                'language' => 'ar',
            ]);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.answer', 'سياسة الاسترداد: يمكن الاسترداد الكامل قبل 24 ساعة.')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'answer',
                    'citations',
                    'confidence',
                    'needs_human_handoff',
                    'fallback_message',
                ],
            ]);
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChatRequest;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    use ApiResponseTrait;

    public function chat(ChatRequest $request): JsonResponse
    {
        $user = auth('api')->user();

        if (!$user) {
            return $this->unauthorizedResponse();
        }

        $baseUrl = rtrim((string) config('services.rag.base_url'), '/');
        $proxySecret = (string) config('services.rag.proxy_secret');
        $timeoutSeconds = (int) config('services.rag.timeout_seconds', 30);

        if ($baseUrl === '' || $proxySecret === '') {
            return $this->errorResponse('خدمة المساعد غير مُهيّأة حالياً', 500);
        }

        $payload = [
            'question' => $request->validated('question'),
            'language' => $request->validated('language') ?? 'ar',
            'conversation_id' => $request->validated('conversation_id'),
            'user_context' => [
                'user_type' => $user->user_type,
                'user_id' => $user->id,
            ],
            'public_only' => true,
        ];

        try {
            $res = Http::timeout($timeoutSeconds)
                ->withHeaders([
                    'X-Rag-Proxy-Secret' => $proxySecret,
                    'Accept' => 'application/json',
                ])
                ->post("{$baseUrl}/rag/chat", $payload);

            if (!$res->ok()) {
                Log::warning('RAG service returned non-200', [
                    'status' => $res->status(),
                    'body' => $res->body(),
                ]);

                return $this->errorResponse('تعذر الحصول على إجابة الآن، حاول لاحقاً', 502);
            }

            $data = $res->json();
            if (!is_array($data)) {
                return $this->errorResponse('استجابة غير متوقعة من خدمة المساعد', 502);
            }

            return $this->successResponse($data, 'تم الحصول على الإجابة');
        } catch (\Throwable $e) {
            Log::error('RAG service request failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('تعذر الاتصال بخدمة المساعد', 502);
        }
    }
}


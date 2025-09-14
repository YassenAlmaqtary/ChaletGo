<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Payment\MoyasarPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Moyasar webhook
     */
    public function moyasar(Request $request): JsonResponse
    {
        try {
            // Verify webhook signature
            $signature = $request->header('X-Moyasar-Signature');
            $payload = $request->getContent();
            $webhookSecret = config('services.moyasar.webhook_secret');

            if (!$this->verifyMoyasarSignature($payload, $signature, $webhookSecret)) {
                Log::warning('Invalid Moyasar webhook signature', [
                    'signature' => $signature,
                    'payload' => $payload,
                ]);

                return response()->json(['error' => 'Invalid signature'], 401);
            }

            // Process webhook
            $webhookData = $request->all();
            $moyasarService = new MoyasarPaymentService();

            $result = $moyasarService->handleWebhook($webhookData);

            if ($result) {
                Log::info('Moyasar webhook processed successfully', [
                    'type' => $webhookData['type'] ?? 'unknown',
                    'payment_id' => $webhookData['data']['id'] ?? 'unknown',
                ]);

                return response()->json(['status' => 'success'], 200);
            } else {
                Log::error('Failed to process Moyasar webhook', [
                    'webhook_data' => $webhookData,
                ]);

                return response()->json(['error' => 'Processing failed'], 500);
            }

        } catch (\Exception $e) {
            Log::error('Moyasar webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Handle STC Pay webhook
     */
    public function stcPay(Request $request): JsonResponse
    {
        try {
            // Verify webhook signature for STC Pay
            $signature = $request->header('X-STC-Signature');
            $payload = $request->getContent();
            $webhookSecret = config('services.stc_pay.webhook_secret');

            if (!$this->verifyStcPaySignature($payload, $signature, $webhookSecret)) {
                Log::warning('Invalid STC Pay webhook signature', [
                    'signature' => $signature,
                ]);

                return response()->json(['error' => 'Invalid signature'], 401);
            }

            // Process STC Pay webhook
            $webhookData = $request->all();

            Log::info('STC Pay webhook received', [
                'type' => $webhookData['type'] ?? 'unknown',
                'transaction_id' => $webhookData['transaction_id'] ?? 'unknown',
            ]);

            // TODO: Implement STC Pay webhook processing

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('STC Pay webhook error', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Verify Moyasar webhook signature
     */
    protected function verifyMoyasarSignature(string $payload, ?string $signature, ?string $secret): bool
    {
        if (!$signature || !$secret) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify STC Pay webhook signature
     */
    protected function verifyStcPaySignature(string $payload, ?string $signature, ?string $secret): bool
    {
        if (!$signature || !$secret) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Handle payment callback (for redirect-based payments)
     */
    public function paymentCallback(Request $request): JsonResponse
    {
        try {
            $paymentId = $request->get('id');

            if (!$paymentId) {
                return response()->json(['error' => 'Payment ID missing'], 400);
            }

            // Verify payment with Moyasar
            $moyasarService = new MoyasarPaymentService();
            $verificationResult = $moyasarService->verifyPayment($paymentId);

            if ($verificationResult['success']) {
                // Redirect to success page or return success response
                return response()->json([
                    'status' => 'success',
                    'payment_status' => $verificationResult['status'],
                    'message' => 'تم التحقق من الدفع بنجاح',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'فشل في التحقق من الدفع',
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Payment callback error', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}

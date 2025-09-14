<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MoyasarPaymentService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected bool $isLive;

    public function __construct()
    {
        $this->isLive = config('services.moyasar.live', false);
        $this->apiKey = $this->isLive 
            ? config('services.moyasar.live_secret_key')
            : config('services.moyasar.test_secret_key');
        $this->baseUrl = 'https://api.moyasar.com/v1';
    }

    /**
     * Create payment intent
     */
    public function createPayment(Booking $booking, array $paymentData): array
    {
        try {
            $amount = $this->convertToHalalas($booking->total_amount - $booking->discount_amount);
            
            $payload = [
                'amount' => $amount,
                'currency' => 'SAR',
                'description' => "حجز شاليه {$booking->chalet->name} - رقم الحجز: {$booking->booking_number}",
                'callback_url' => route('payment.callback'),
                'source' => $this->buildSourceData($paymentData),
                'metadata' => [
                    'booking_id' => $booking->id,
                    'customer_id' => $booking->customer_id,
                    'chalet_id' => $booking->chalet_id,
                    'booking_number' => $booking->booking_number,
                ],
            ];

            $response = Http::withBasicAuth($this->apiKey, '')
                ->post("{$this->baseUrl}/payments", $payload);

            if ($response->successful()) {
                $paymentData = $response->json();
                
                // Create payment record
                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'payment_method' => $this->getPaymentMethod($paymentData['source']),
                    'amount' => $booking->total_amount - $booking->discount_amount,
                    'status' => $this->mapMoyasarStatus($paymentData['status']),
                    'transaction_id' => $paymentData['id'],
                    'payment_details' => [
                        'moyasar_payment_id' => $paymentData['id'],
                        'moyasar_status' => $paymentData['status'],
                        'source' => $paymentData['source'],
                        'created_at' => $paymentData['created_at'],
                        'fee' => $paymentData['fee'] ?? null,
                    ],
                ]);

                return [
                    'success' => true,
                    'payment_id' => $payment->id,
                    'moyasar_payment_id' => $paymentData['id'],
                    'status' => $paymentData['status'],
                    'source' => $paymentData['source'],
                    'transaction_url' => $paymentData['source']['transaction_url'] ?? null,
                ];
            }

            throw new Exception('فشل في إنشاء عملية الدفع: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Moyasar payment creation failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'payload' => $payload ?? null,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(string $moyasarPaymentId): array
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->get("{$this->baseUrl}/payments/{$moyasarPaymentId}");

            if ($response->successful()) {
                $paymentData = $response->json();
                
                return [
                    'success' => true,
                    'status' => $paymentData['status'],
                    'amount' => $paymentData['amount'],
                    'fee' => $paymentData['fee'] ?? 0,
                    'source' => $paymentData['source'],
                    'created_at' => $paymentData['created_at'],
                    'updated_at' => $paymentData['updated_at'],
                ];
            }

            throw new Exception('فشل في التحقق من حالة الدفع');

        } catch (Exception $e) {
            Log::error('Moyasar payment verification failed', [
                'moyasar_payment_id' => $moyasarPaymentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process refund
     */
    public function refundPayment(Payment $payment, float $amount = null): array
    {
        try {
            $refundAmount = $amount ? $this->convertToHalalas($amount) : null;
            $moyasarPaymentId = $payment->payment_details['moyasar_payment_id'];

            $payload = array_filter([
                'amount' => $refundAmount,
                'description' => "استرداد للحجز رقم: {$payment->booking->booking_number}",
            ]);

            $response = Http::withBasicAuth($this->apiKey, '')
                ->post("{$this->baseUrl}/payments/{$moyasarPaymentId}/refund", $payload);

            if ($response->successful()) {
                $refundData = $response->json();
                
                // Update payment record
                $payment->update([
                    'status' => 'refunded',
                    'payment_details' => array_merge($payment->payment_details, [
                        'refund_id' => $refundData['id'],
                        'refund_amount' => $refundData['amount'],
                        'refund_status' => $refundData['status'],
                        'refunded_at' => now(),
                    ]),
                ]);

                return [
                    'success' => true,
                    'refund_id' => $refundData['id'],
                    'amount' => $this->convertFromHalalas($refundData['amount']),
                    'status' => $refundData['status'],
                ];
            }

            throw new Exception('فشل في عملية الاسترداد: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Moyasar refund failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle webhook
     */
    public function handleWebhook(array $webhookData): bool
    {
        try {
            $eventType = $webhookData['type'];
            $paymentData = $webhookData['data'];
            
            $payment = Payment::where('transaction_id', $paymentData['id'])->first();
            
            if (!$payment) {
                Log::warning('Payment not found for webhook', ['moyasar_payment_id' => $paymentData['id']]);
                return false;
            }

            switch ($eventType) {
                case 'payment_paid':
                    $this->handlePaymentPaid($payment, $paymentData);
                    break;
                    
                case 'payment_failed':
                    $this->handlePaymentFailed($payment, $paymentData);
                    break;
                    
                case 'payment_refunded':
                    $this->handlePaymentRefunded($payment, $paymentData);
                    break;
                    
                default:
                    Log::info('Unhandled webhook event', ['type' => $eventType]);
            }

            return true;

        } catch (Exception $e) {
            Log::error('Webhook handling failed', [
                'error' => $e->getMessage(),
                'webhook_data' => $webhookData,
            ]);

            return false;
        }
    }

    /**
     * Build source data based on payment method
     */
    protected function buildSourceData(array $paymentData): array
    {
        switch ($paymentData['payment_method']) {
            case 'creditcard':
                return [
                    'type' => 'creditcard',
                    'name' => $paymentData['card_holder_name'],
                    'number' => $paymentData['card_number'],
                    'cvc' => $paymentData['cvc'],
                    'month' => $paymentData['expiry_month'],
                    'year' => $paymentData['expiry_year'],
                ];
                
            case 'sadad':
                return [
                    'type' => 'sadad',
                    'username' => $paymentData['sadad_username'],
                    'password' => $paymentData['sadad_password'],
                ];
                
            case 'applepay':
                return [
                    'type' => 'applepay',
                    'token' => $paymentData['apple_pay_token'],
                ];
                
            default:
                throw new Exception('طريقة دفع غير مدعومة');
        }
    }

    /**
     * Convert SAR to Halalas (smallest currency unit)
     */
    protected function convertToHalalas(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Convert Halalas to SAR
     */
    protected function convertFromHalalas(int $halalas): float
    {
        return $halalas / 100;
    }

    /**
     * Map Moyasar status to our status
     */
    protected function mapMoyasarStatus(string $moyasarStatus): string
    {
        return match ($moyasarStatus) {
            'paid' => 'completed',
            'failed' => 'failed',
            'pending' => 'pending',
            'refunded' => 'refunded',
            default => 'pending',
        };
    }

    /**
     * Get payment method from source
     */
    protected function getPaymentMethod(array $source): string
    {
        return match ($source['type']) {
            'creditcard' => 'credit_card',
            'sadad' => 'sadad',
            'applepay' => 'apple_pay',
            default => 'unknown',
        };
    }

    /**
     * Handle payment paid webhook
     */
    protected function handlePaymentPaid(Payment $payment, array $paymentData): void
    {
        $payment->update([
            'status' => 'completed',
            'paid_at' => now(),
            'payment_details' => array_merge($payment->payment_details, [
                'moyasar_status' => $paymentData['status'],
                'fee' => $paymentData['fee'] ?? 0,
                'updated_at' => $paymentData['updated_at'],
            ]),
        ]);

        // Update booking status
        $payment->booking->update(['status' => 'confirmed']);
        
        Log::info('Payment completed via webhook', ['payment_id' => $payment->id]);
    }

    /**
     * Handle payment failed webhook
     */
    protected function handlePaymentFailed(Payment $payment, array $paymentData): void
    {
        $payment->update([
            'status' => 'failed',
            'payment_details' => array_merge($payment->payment_details, [
                'moyasar_status' => $paymentData['status'],
                'failure_reason' => $paymentData['source']['message'] ?? 'Unknown error',
                'updated_at' => $paymentData['updated_at'],
            ]),
        ]);
        
        Log::info('Payment failed via webhook', ['payment_id' => $payment->id]);
    }

    /**
     * Handle payment refunded webhook
     */
    protected function handlePaymentRefunded(Payment $payment, array $paymentData): void
    {
        $payment->update([
            'status' => 'refunded',
            'payment_details' => array_merge($payment->payment_details, [
                'moyasar_status' => $paymentData['status'],
                'refunded_at' => now(),
                'updated_at' => $paymentData['updated_at'],
            ]),
        ]);
        
        Log::info('Payment refunded via webhook', ['payment_id' => $payment->id]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Payment;
use App\Services\Payment\MoyasarPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RefundController extends Controller
{
    use ApiResponseTrait;

    /**
     * Process refund for a payment
     */
    public function processRefund(Request $request, int $paymentId): JsonResponse
    {
        $user = Auth::guard('api')->user();
        
        $payment = Payment::with(['booking.chalet', 'booking.customer'])->find($paymentId);
        
        if (!$payment) {
            return $this->notFoundResponse('المدفوعة غير موجودة');
        }

        // Check permissions
        if (!$this->canRefund($user, $payment)) {
            return $this->forbiddenResponse('ليس لديك صلاحية لاسترداد هذه المدفوعة');
        }

        // Validate payment status
        if ($payment->status !== 'completed') {
            return $this->errorResponse('لا يمكن استرداد مدفوعة غير مكتملة');
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'nullable|numeric|min:0.01|max:' . $payment->amount,
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $refundAmount = $request->amount ?? $payment->amount;
            
            // Process refund with Moyasar
            $moyasarService = new MoyasarPaymentService();
            $refundResult = $moyasarService->refundPayment($payment, $refundAmount);

            if ($refundResult['success']) {
                // Log refund
                Log::info('Refund processed successfully', [
                    'payment_id' => $payment->id,
                    'refund_amount' => $refundAmount,
                    'refund_id' => $refundResult['refund_id'],
                    'processed_by' => $user->id,
                ]);

                return $this->successResponse([
                    'refund_id' => $refundResult['refund_id'],
                    'amount' => $refundResult['amount'],
                    'status' => $refundResult['status'],
                    'payment_id' => $payment->id,
                    'booking_number' => $payment->booking->booking_number,
                ], 'تم معالجة الاسترداد بنجاح');

            } else {
                return $this->errorResponse($refundResult['error'] ?? 'فشل في معالجة الاسترداد');
            }

        } catch (\Exception $e) {
            Log::error('Refund processing failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return $this->errorResponse('حدث خطأ أثناء معالجة الاسترداد');
        }
    }

    /**
     * Get refund history for a payment
     */
    public function getRefunds(int $paymentId): JsonResponse
    {
        $user = Auth::guard('api')->user();
        
        $payment = Payment::with(['booking.chalet', 'booking.customer'])->find($paymentId);
        
        if (!$payment) {
            return $this->notFoundResponse('المدفوعة غير موجودة');
        }

        // Check permissions
        if (!$this->canViewRefunds($user, $payment)) {
            return $this->forbiddenResponse('ليس لديك صلاحية لعرض استردادات هذه المدفوعة');
        }

        $refunds = [];
        $paymentDetails = $payment->payment_details;

        if (isset($paymentDetails['refund_id'])) {
            $refunds[] = [
                'refund_id' => $paymentDetails['refund_id'],
                'amount' => $paymentDetails['refund_amount'] ?? 0,
                'status' => $paymentDetails['refund_status'] ?? 'unknown',
                'refunded_at' => $paymentDetails['refunded_at'] ?? null,
            ];
        }

        return $this->successResponse([
            'payment_id' => $payment->id,
            'original_amount' => $payment->amount,
            'refunds' => $refunds,
            'total_refunded' => collect($refunds)->sum('amount'),
        ], 'تم جلب بيانات الاستردادات بنجاح');
    }

    /**
     * Check if user can refund payment
     */
    protected function canRefund($user, Payment $payment): bool
    {
        // Admin can refund any payment
        if ($user->user_type === 'admin') {
            return true;
        }

        // Owner can refund payments for their chalets
        if ($user->user_type === 'owner' && $payment->booking->chalet->owner_id === $user->id) {
            return true;
        }

        // Customer can request refund for their own payments (within certain conditions)
        if ($user->user_type === 'customer' && $payment->booking->customer_id === $user->id) {
            // Check if booking is still cancellable (e.g., 24 hours before check-in)
            $checkInDate = $payment->booking->check_in_date;
            $hoursUntilCheckIn = now()->diffInHours($checkInDate, false);
            
            return $hoursUntilCheckIn > 24; // Can refund if more than 24 hours until check-in
        }

        return false;
    }

    /**
     * Check if user can view refunds
     */
    protected function canViewRefunds($user, Payment $payment): bool
    {
        // Admin can view all refunds
        if ($user->user_type === 'admin') {
            return true;
        }

        // Owner can view refunds for their chalets
        if ($user->user_type === 'owner' && $payment->booking->chalet->owner_id === $user->id) {
            return true;
        }

        // Customer can view their own refunds
        if ($user->user_type === 'customer' && $payment->booking->customer_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Get refund policies
     */
    public function getRefundPolicies(): JsonResponse
    {
        $policies = [
            'general' => [
                'title' => 'سياسة الاسترداد العامة',
                'description' => 'يمكن استرداد المدفوعات وفقاً للشروط التالية',
                'rules' => [
                    'يمكن الاسترداد الكامل إذا تم الإلغاء قبل 24 ساعة من تاريخ الوصول',
                    'استرداد 50% إذا تم الإلغاء قبل 12 ساعة من تاريخ الوصول',
                    'لا يمكن الاسترداد إذا تم الإلغاء خلال 12 ساعة من تاريخ الوصول',
                    'في حالة الظروف الاستثنائية، يتم النظر في كل حالة على حدة',
                ],
            ],
            'processing_time' => [
                'title' => 'مدة معالجة الاسترداد',
                'description' => 'المدة المتوقعة لمعالجة طلبات الاسترداد',
                'timeframes' => [
                    'credit_card' => '3-5 أيام عمل',
                    'bank_transfer' => '1-2 أيام عمل',
                    'digital_wallet' => 'فوري إلى 24 ساعة',
                ],
            ],
            'fees' => [
                'title' => 'رسوم الاسترداد',
                'description' => 'الرسوم المطبقة على عمليات الاسترداد',
                'fee_structure' => [
                    'admin_fee' => '0 ريال (مجاني)',
                    'gateway_fee' => 'حسب بوابة الدفع',
                    'note' => 'قد تطبق بوابة الدفع رسوماً إضافية',
                ],
            ],
        ];

        return $this->successResponse($policies, 'تم جلب سياسات الاسترداد بنجاح');
    }
}

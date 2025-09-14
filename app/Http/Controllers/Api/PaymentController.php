<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payment\MoyasarPaymentService;
use App\Http\Requests\SecureBookingRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    use ApiResponseTrait;

    /**
     * Process payment for a booking
     */
    public function processPayment(Request $request, int $bookingId): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $booking = Booking::with(['chalet', 'customer'])->find($bookingId);

        if (!$booking) {
            return $this->notFoundResponse(__('messages.booking_not_found'));
        }

        // Check if user can pay for this booking
        if ($booking->customer_id !== $user->id && $user->user_type !== 'admin') {
            return $this->forbiddenResponse(__('messages.no_permission_view_booking'));
        }

        // Check if booking is confirmed
        if ($booking->status !== 'confirmed') {
            return $this->errorResponse('لا يمكن الدفع إلا للحجوزات المؤكدة');
        }

        // Check if already paid
        $existingPayment = $booking->payments()->where('status', 'completed')->first();
        if ($existingPayment) {
            return $this->errorResponse('تم دفع هذا الحجز مسبقاً');
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:creditcard,sadad,applepay,stcpay',
            'amount' => 'required|numeric|min:0',
            // Credit Card fields
            'card_number' => 'required_if:payment_method,creditcard|string|regex:/^[0-9]{13,19}$/',
            'card_holder_name' => 'required_if:payment_method,creditcard|string|max:255',
            'expiry_month' => 'required_if:payment_method,creditcard|integer|between:1,12',
            'expiry_year' => 'required_if:payment_method,creditcard|integer|min:' . date('Y'),
            'cvc' => 'required_if:payment_method,creditcard|string|regex:/^[0-9]{3,4}$/',
            // SADAD fields
            'sadad_username' => 'required_if:payment_method,sadad|string',
            'sadad_password' => 'required_if:payment_method,sadad|string',
            // Apple Pay fields
            'apple_pay_token' => 'required_if:payment_method,applepay|string',
            // STC Pay fields
            'stc_pay_mobile' => 'required_if:payment_method,stcpay|string|regex:/^(05|5)[0-9]{8}$/',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Validate amount matches booking total
        $totalAmount = $booking->total_amount - $booking->discount_amount;
        if ($request->amount != $totalAmount) {
            return $this->errorResponse('المبلغ المدفوع لا يطابق إجمالي الحجز');
        }

        try {
            DB::beginTransaction();

            // Initialize Moyasar service
            $moyasarService = new MoyasarPaymentService();

            // Process payment with Moyasar
            $paymentResult = $moyasarService->createPayment($booking, $request->all());

            if ($paymentResult['success']) {
                DB::commit();

                return $this->successResponse([
                    'payment_id' => $paymentResult['payment_id'],
                    'moyasar_payment_id' => $paymentResult['moyasar_payment_id'],
                    'status' => $paymentResult['status'],
                    'transaction_url' => $paymentResult['transaction_url'] ?? null,
                    'message' => 'تم إنشاء عملية الدفع بنجاح',
                ], 'تم إنشاء عملية الدفع بنجاح');

            } else {
                DB::rollback();
                return $this->errorResponse($paymentResult['error'] ?? 'فشل في معالجة الدفع');
            }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment processing failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('حدث خطأ أثناء معالجة الدفع');
        }
    }

    /**
     * Get payment history for user
     */
    public function getPayments(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $query = Payment::with(['booking.chalet'])
            ->whereHas('booking', function ($q) use ($user) {
                if ($user->user_type === 'customer') {
                    $q->where('customer_id', $user->id);
                } elseif ($user->user_type === 'owner') {
                    $q->whereHas('chalet', function ($q2) use ($user) {
                        $q2->where('owner_id', $user->id);
                    });
                }
            });

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        $payments->getCollection()->transform(function ($payment) {
            return [
                'id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'chalet_name' => $payment->booking->chalet->name,
                'payment_method' => $payment->payment_method,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'transaction_id' => $payment->transaction_id,
                'paid_at' => $payment->paid_at,
                'created_at' => $payment->created_at,
            ];
        });

        return $this->successResponse($payments, __('messages.payments_retrieved'));
    }

    /**
     * Get payment details
     */
    public function getPayment(int $paymentId): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $payment = Payment::with(['booking.chalet', 'booking.customer'])
            ->whereHas('booking', function ($q) use ($user) {
                if ($user->user_type === 'customer') {
                    $q->where('customer_id', $user->id);
                } elseif ($user->user_type === 'owner') {
                    $q->whereHas('chalet', function ($q2) use ($user) {
                        $q2->where('owner_id', $user->id);
                    });
                }
            })
            ->find($paymentId);

        if (!$payment) {
            return $this->notFoundResponse(__('messages.payment_not_found'));
        }

        return $this->successResponse([
            'id' => $payment->id,
            'booking' => [
                'id' => $payment->booking->id,
                'booking_number' => $payment->booking->booking_number,
                'chalet_name' => $payment->booking->chalet->name,
                'customer_name' => $payment->booking->customer->name,
                'check_in_date' => $payment->booking->check_in_date,
                'check_out_date' => $payment->booking->check_out_date,
                'total_amount' => $payment->booking->total_amount,
                'discount_amount' => $payment->booking->discount_amount,
            ],
            'payment_method' => $payment->payment_method,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'transaction_id' => $payment->transaction_id,
            'paid_at' => $payment->paid_at,
            'created_at' => $payment->created_at,
        ], __('messages.payment_details_retrieved'));
    }

    /**
     * Prepare payment details based on method
     */
    private function preparePaymentDetails(Request $request): array
    {
        $details = [
            'payment_method' => $request->payment_method,
            'processed_at' => now(),
        ];

        if ($request->payment_method === 'credit_card') {
            $details['card_last_four'] = substr($request->card_number, -4);
            $details['card_holder'] = $request->card_holder;
            $details['expiry'] = $request->expiry_month . '/' . $request->expiry_year;
        }

        return $details;
    }

    /**
     * Process payment by method (Mock implementation)
     */
    private function processPaymentByMethod(Payment $payment, Request $request): array
    {
        switch ($request->payment_method) {
            case 'credit_card':
                return $this->processCreditCardPayment($payment, $request);
            case 'bank_transfer':
                return $this->processBankTransferPayment($payment, $request);
            case 'digital_wallet':
                return $this->processDigitalWalletPayment($payment, $request);
            case 'cash':
                return $this->processCashPayment($payment, $request);
            default:
                return ['success' => false, 'error' => 'طريقة دفع غير مدعومة'];
        }
    }

    /**
     * Process credit card payment (Mock)
     */
    private function processCreditCardPayment(Payment $payment, Request $request): array
    {
        // Mock credit card processing
        // In real implementation, integrate with payment gateway like:
        // - Moyasar (Saudi)
        // - PayTabs (MENA)
        // - Stripe
        // - PayPal

        // Simulate processing delay
        sleep(1);

        // Mock success/failure (90% success rate)
        if (rand(1, 10) <= 9) {
            return [
                'success' => true,
                'transaction_id' => 'CC_' . time() . '_' . rand(1000, 9999),
                'details' => [
                    'gateway' => 'mock_gateway',
                    'gateway_response' => 'approved',
                    'authorization_code' => 'AUTH_' . rand(100000, 999999),
                ]
            ];
        } else {
            return [
                'success' => false,
                'error' => 'فشل في معالجة البطاقة الائتمانية'
            ];
        }
    }

    /**
     * Process bank transfer payment (Mock)
     */
    private function processBankTransferPayment(Payment $payment, Request $request): array
    {
        // For bank transfer, usually requires manual verification
        return [
            'success' => true,
            'transaction_id' => 'BT_' . time() . '_' . rand(1000, 9999),
            'details' => [
                'method' => 'bank_transfer',
                'status' => 'pending_verification',
                'note' => 'يتطلب التحقق اليدوي من التحويل البنكي'
            ]
        ];
    }

    /**
     * Process digital wallet payment (Mock)
     */
    private function processDigitalWalletPayment(Payment $payment, Request $request): array
    {
        // Mock digital wallet processing (Apple Pay, STC Pay, etc.)
        return [
            'success' => true,
            'transaction_id' => 'DW_' . time() . '_' . rand(1000, 9999),
            'details' => [
                'wallet_type' => 'stc_pay', // or apple_pay, google_pay, etc.
                'wallet_transaction_id' => 'STC_' . rand(100000000, 999999999),
            ]
        ];
    }

    /**
     * Process cash payment
     */
    private function processCashPayment(Payment $payment, Request $request): array
    {
        // Cash payment - usually handled at property
        return [
            'success' => true,
            'transaction_id' => 'CASH_' . time() . '_' . rand(1000, 9999),
            'details' => [
                'method' => 'cash',
                'note' => 'دفع نقدي عند الوصول'
            ]
        ];
    }
}

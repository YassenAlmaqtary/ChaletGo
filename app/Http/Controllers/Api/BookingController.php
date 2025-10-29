<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Chalet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display user's bookings
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $query = Booking::with(['chalet.images', 'payments', 'extras', 'review'])
            ->where('customer_id', $user->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('check_in_date', '>=', $request->get('from_date'));
        }
        if ($request->has('to_date')) {
            $query->where('check_out_date', '<=', $request->get('to_date'));
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        return $this->paginatedResponse(
            $bookings->through(fn($booking) => new BookingResource($booking)),
            'تم جلب الحجوزات بنجاح'
        );
    }

    /**
     * Create a new booking
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        if ($user->user_type !== 'customer') {
            return $this->forbiddenResponse('يمكن للعملاء فقط إجراء الحجوزات');
        }

        $validator = Validator::make($request->all(), [
            'chalet_id' => 'required|exists:chalets,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'guests_count' => 'required|integer|min:1',
            'special_requests' => 'nullable|string|max:1000',
            'extras' => 'nullable|array',
            'extras.*.name' => 'required|string|max:255',
            'extras.*.price' => 'required|numeric|min:0',
            'extras.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $chalet = Chalet::with('owner')->find($request->chalet_id);

        if (!$chalet->is_active) {
            return $this->errorResponse('الشاليه غير متاح للحجز حالياً');
        }

        if ($request->guests_count > $chalet->max_guests) {
            return $this->errorResponse("عدد الضيوف يتجاوز الحد المسموح ({$chalet->max_guests} ضيف)");
        }

        // Check availability
        if (!$chalet->isAvailable($request->check_in_date, $request->check_out_date)) {
            return $this->errorResponse('الشاليه غير متاح في هذه التواريخ');
        }

        DB::beginTransaction();
        try {
            // Calculate total amount
            $checkIn = Carbon::parse($request->check_in_date);
            $checkOut = Carbon::parse($request->check_out_date);
            $nights = $checkIn->diffInDays($checkOut);
            $totalAmount = $nights * $chalet->price_per_night;

            // Create booking
            $booking = Booking::create([
                'chalet_id' => $chalet->id,
                'customer_id' => $user->id,
                'booking_number' => Booking::generateBookingNumber(),
                'check_in_date' => $request->check_in_date,
                'check_out_date' => $request->check_out_date,
                'guests_count' => $request->guests_count,
                'total_amount' => $totalAmount,
                'discount_amount' => 0,
                'status' =>Booking::STATUS_CONFIRMED, //Booking::STATUS_PENDING,
                'special_requests' => $request->special_requests,
                'booking_details' => [
                    'nights' => $nights,
                    'price_per_night' => $chalet->price_per_night,
                    'chalet_name' => $chalet->name,
                    'customer_name' => $user->name,
                    'customer_phone' => $user->phone,
                ],
            ]);

            // Add extras if provided
            if ($request->has('extras')) {
                foreach ($request->extras as $extra) {
                    $booking->extras()->create([
                        'extra_name' => $extra['name'],
                        'extra_price' => $extra['price'],
                        'quantity' => $extra['quantity'],
                    ]);
                }
            }

            DB::commit();

            $booking->load(['chalet.images', 'extras']);

            return $this->successResponse(
                new BookingResource($booking),
                'تم إنشاء الحجز بنجاح',
                201
            );

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('حدث خطأ أثناء إنشاء الحجز');
        }
    }

    /**
     * Display the specified booking
     */
    public function show(string $bookingNumber): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $booking = Booking::with(['chalet.images', 'customer', 'payments', 'extras', 'review'])
            ->where('booking_number', $bookingNumber)
            ->first();

        if (!$booking) {
            return $this->notFoundResponse('الحجز غير موجود');
        }

        // Check if user can access this booking
        if ($booking->customer_id !== $user->id &&
            $booking->chalet->owner_id !== $user->id &&
            $user->user_type !== 'admin') {
            return $this->forbiddenResponse('ليس لديك صلاحية لعرض هذا الحجز');
        }

        return $this->successResponse(
            new BookingResource($booking),
            'تم جلب تفاصيل الحجز بنجاح'
        );
    }

    /**
     * Update booking status (for owners and admins)
     */
    public function update(Request $request, string $bookingNumber): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $booking = Booking::with(['chalet', 'customer'])
            ->where('booking_number', $bookingNumber)
            ->first();

        if (!$booking) {
            return $this->notFoundResponse('الحجز غير موجود');
        }

        // Check permissions
        if ($booking->chalet->owner_id !== $user->id && $user->user_type !== 'admin') {
            return $this->forbiddenResponse('ليس لديك صلاحية لتعديل هذا الحجز');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:confirmed,cancelled',
            'cancellation_reason' => 'required_if:status,cancelled|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Check if booking can be updated
        if (!in_array($booking->status, [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])) {
            return $this->errorResponse('لا يمكن تعديل هذا الحجز');
        }

        $oldStatus = $booking->status;
        $booking->status = $request->status;

        if ($request->status === 'cancelled') {
            $bookingDetails = $booking->booking_details ?? [];
            $bookingDetails['cancellation_reason'] = $request->cancellation_reason;
            $bookingDetails['cancelled_by'] = $user->name;
            $bookingDetails['cancelled_at'] = now()->format('Y-m-d H:i:s');
            $booking->booking_details = $bookingDetails;
        }

        $booking->save();

        $message = $request->status === 'confirmed' ? 'تم تأكيد الحجز بنجاح' : 'تم إلغاء الحجز بنجاح';

        return $this->successResponse(
            new BookingResource($booking),
            $message
        );
    }

    /**
     * Cancel booking (for customers)
     */
    public function cancel(Request $request, string $bookingNumber): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $booking = Booking::with(['chalet'])
            ->where('booking_number', $bookingNumber)
            ->where('customer_id', $user->id)
            ->first();

        if (!$booking) {
            return $this->notFoundResponse('الحجز غير موجود');
        }

        if (!$booking->canBeCancelled()) {
            return $this->errorResponse('لا يمكن إلغاء هذا الحجز');
        }

        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $booking->status = Booking::STATUS_CANCELLED;
        $bookingDetails = $booking->booking_details ?? [];
        $bookingDetails['cancellation_reason'] = $request->cancellation_reason;
        $bookingDetails['cancelled_by'] = $user->name;
        $bookingDetails['cancelled_at'] = now()->format('Y-m-d H:i:s');
        $booking->booking_details = $bookingDetails;
        $booking->save();

        return $this->successResponse(
            new BookingResource($booking),
            'تم إلغاء الحجز بنجاح'
        );
    }

    /**
     * Get bookings for chalet owner
     */
    public function ownerBookings(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        if ($user->user_type !== 'owner') {
            return $this->forbiddenResponse('هذه الخدمة متاحة لمالكي الشاليات فقط');
        }

        $query = Booking::with(['chalet', 'customer', 'payments'])
            ->whereHas('chalet', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            });

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by chalet
        if ($request->has('chalet_id')) {
            $query->where('chalet_id', $request->get('chalet_id'));
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        return $this->paginatedResponse(
            $bookings->through(fn($booking) => new BookingResource($booking)),
            'تم جلب الحجوزات بنجاح'
        );
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Booking;
use App\Models\Review;
use App\Rules\SecureInput;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of reviews with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $query = Review::with(['customer', 'chalet', 'booking']);

        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        } elseif ($user->isOwner()) {
            $query->whereHas('chalet', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            });
        }

        if ($request->filled('chalet_id')) {
            $query->where('chalet_id', $request->get('chalet_id'));
        }

        if ($request->filled('booking_id')) {
            $query->where('booking_id', $request->get('booking_id'));
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->get('rating'));
        }

        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', $request->get('min_rating'));
        }

        if ($request->filled('max_rating')) {
            $query->where('rating', '<=', $request->get('max_rating'));
        }

        if ($request->filled('is_approved')) {
            $isApproved = filter_var($request->get('is_approved'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (!is_null($isApproved)) {
                $query->where('is_approved', $isApproved);
            }
        }

        $allowedSorts = ['created_at', 'rating'];
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) $request->get('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 50) : 15;

        $reviews = $query->paginate($perPage);

        return $this->paginatedResponse(
            $reviews->through(fn ($review) => new ReviewResource($review)),
            'تم جلب التقييمات بنجاح'
        );
    }

    /**
     * Store a newly created review.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        if (!$user->isCustomer()) {
            return $this->forbiddenResponse('يمكن للعملاء فقط إضافة التقييمات');
        }

        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => ['nullable', 'string', 'max:1000', new SecureInput()],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $booking = Booking::with('chalet', 'review')
            ->where('id', $request->booking_id)
            ->first();

        if (!$booking) {
            return $this->notFoundResponse('الحجز غير موجود');
        }

        if ($booking->customer_id !== $user->id) {
            return $this->forbiddenResponse('ليس لديك صلاحية لتقييم هذا الحجز');
        }

        if ($booking->review) {
            return $this->errorResponse('تم إضافة تقييم لهذا الحجز مسبقاً');
        }
     
        $canReview = $booking->status === Booking::STATUS_COMPLETED || (
            $booking->status === Booking::STATUS_CONFIRMED &&
            $booking->check_out_date instanceof Carbon
            // &&
            // $booking->check_out_date->isPast()
        );

        if (!$canReview) {
            return $this->errorResponse('لا يمكن إضافة تقييم قبل إكمال الحجز');
        }

        $review = Review::create([
            'chalet_id' => $booking->chalet_id,
            'customer_id' => $user->id,
            'booking_id' => $booking->id,
            'rating' => $request->rating,
            'comment' => $request->comment ? strip_tags($request->comment) : null,
            'is_approved' => false,
        ]);

        $review->load(['customer', 'chalet', 'booking']);

        return $this->successResponse(
            new ReviewResource($review),
            'تم إرسال التقييم بنجاح، سيتم مراجعته قريباً',
            201
        );
    }

    /**
     * Display the specified review.
     */
    public function show(string $id): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $review = Review::with(['customer', 'chalet', 'booking'])
            ->find($id);

        if (!$review) {
            return $this->notFoundResponse('التقييم غير موجود');
        }

        $ownsReview = $review->customer_id === $user->id;
        $ownsChalet = $user->isOwner() && $review->chalet && $review->chalet->owner_id === $user->id;

        if (!$user->isAdmin() && !$ownsReview && !$ownsChalet) {
            return $this->forbiddenResponse('ليس لديك صلاحية لعرض هذا التقييم');
        }

        return $this->successResponse(
            new ReviewResource($review),
            'تم جلب التقييم بنجاح'
        );
    }
}

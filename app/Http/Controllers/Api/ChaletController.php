<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Resources\ChaletResource;
use App\Models\Chalet;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ChaletController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of chalets with filters and search
     */
    public function index(Request $request): JsonResponse
    {
        $query = Chalet::with(['images', 'amenities', 'owner'])
            ->active();

        // Search by name or location
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by location
        if ($request->has('location')) {
            $query->where('location', 'like', "%{$request->get('location')}%");
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price_per_night', '>=', $request->get('min_price'));
        }
        if ($request->has('max_price')) {
            $query->where('price_per_night', '<=', $request->get('max_price'));
        }

        // Filter by guests count
        if ($request->has('guests')) {
            $query->where('max_guests', '>=', $request->get('guests'));
        }

        // Filter by bedrooms
        if ($request->has('bedrooms')) {
            $query->where('bedrooms', '>=', $request->get('bedrooms'));
        }

        // Filter by amenities
        if ($request->has('amenities')) {
            $amenityIds = explode(',', $request->get('amenities'));
            $query->whereHas('amenities', function ($q) use ($amenityIds) {
                $q->whereIn('amenities.id', $amenityIds);
            });
        }

        // Filter featured chalets
        if ($request->has('featured') && $request->get('featured') == 'true') {
            $query->where('is_featured', true);
        }

        // Filter by availability dates
        if ($request->has('check_in') && $request->has('check_out')) {
            $checkIn = $request->get('check_in');
            $checkOut = $request->get('check_out');

            $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->where('status', '!=', 'cancelled')
                  ->where(function ($query) use ($checkIn, $checkOut) {
                      $query->whereBetween('check_in_date', [$checkIn, $checkOut])
                            ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                            ->orWhere(function ($q) use ($checkIn, $checkOut) {
                                $q->where('check_in_date', '<=', $checkIn)
                                  ->where('check_out_date', '>=', $checkOut);
                            });
                  });
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSorts = ['created_at', 'price_per_night', 'name'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 50); // Max 50 items per page
        $chalets = $query->paginate($perPage);

        return $this->paginatedResponse(
            $chalets->through(fn($chalet) => new ChaletResource($chalet)),
            'تم جلب الشاليات بنجاح'
        );
    }

    /**
     * Store a newly created chalet (for owners only)
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        if ($user->user_type !== 'owner') {
            return $this->forbiddenResponse('يمكن لمالكي الشاليات فقط إضافة شاليات جديدة');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'price_per_night' => 'required|numeric|min:0',
            'max_guests' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:1',
            'bathrooms' => 'required|integer|min:1',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $chalet = Chalet::create([
            'owner_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'price_per_night' => $request->price_per_night,
            'max_guests' => $request->max_guests,
            'bedrooms' => $request->bedrooms,
            'bathrooms' => $request->bathrooms,
            'is_active' => true,
        ]);

        // Attach amenities if provided
        if ($request->has('amenities')) {
            $chalet->amenities()->attach($request->amenities);
        }

        $chalet->load(['images', 'amenities', 'owner']);

        return $this->successResponse(
            new ChaletResource($chalet),
            'تم إنشاء الشاليه بنجاح',
            201
        );
    }

    /**
     * Display the specified chalet
     */
    public function show(string $slug): JsonResponse
    {
        $chalet = Chalet::with(['images', 'amenities', 'owner', 'reviews.customer'])
            ->where('slug', $slug)
            ->active()
            ->first();

        if (!$chalet) {
            return $this->notFoundResponse('الشاليه غير موجود');
        }

        return $this->successResponse(
            new ChaletResource($chalet),
            'تم جلب تفاصيل الشاليه بنجاح'
        );
    }

    /**
     * Update the specified chalet (owner only)
     */
    public function update(Request $request, string $slug): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $chalet = Chalet::where('slug', $slug)->first();

        if (!$chalet) {
            return $this->notFoundResponse('الشاليه غير موجود');
        }

        // Check if user owns this chalet
        if ($chalet->owner_id !== $user->id && $user->user_type !== 'admin') {
            return $this->forbiddenResponse('ليس لديك صلاحية لتعديل هذا الشاليه');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'location' => 'sometimes|required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'price_per_night' => 'sometimes|required|numeric|min:0',
            'max_guests' => 'sometimes|required|integer|min:1',
            'bedrooms' => 'sometimes|required|integer|min:1',
            'bathrooms' => 'sometimes|required|integer|min:1',
            'is_active' => 'sometimes|boolean',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $updateData = $request->only([
            'name', 'description', 'location', 'latitude', 'longitude',
            'price_per_night', 'max_guests', 'bedrooms', 'bathrooms', 'is_active'
        ]);

        $chalet->update($updateData);

        // Update amenities if provided
        if ($request->has('amenities')) {
            $chalet->amenities()->sync($request->amenities);
        }

        $chalet->load(['images', 'amenities', 'owner']);

        return $this->successResponse(
            new ChaletResource($chalet),
            'تم تحديث الشاليه بنجاح'
        );
    }

    /**
     * Remove the specified chalet (owner only)
     */
    public function destroy(string $slug): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $chalet = Chalet::where('slug', $slug)->first();

        if (!$chalet) {
            return $this->notFoundResponse('الشاليه غير موجود');
        }

        // Check if user owns this chalet
        if ($chalet->owner_id !== $user->id && $user->user_type !== 'admin') {
            return $this->forbiddenResponse('ليس لديك صلاحية لحذف هذا الشاليه');
        }

        // Check if chalet has active bookings
        $activeBookings = $chalet->bookings()->active()->exists();
        if ($activeBookings) {
            return $this->errorResponse('لا يمكن حذف الشاليه لوجود حجوزات نشطة');
        }

        $chalet->delete();

        return $this->successResponse(null, 'تم حذف الشاليه بنجاح');
    }

    /**
     * Check availability for specific dates
     */
    public function checkAvailability(Request $request, string $slug): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $chalet = Chalet::where('slug', $slug)->active()->first();

        if (!$chalet) {
            return $this->notFoundResponse('الشاليه غير موجود');
        }

        $isAvailable = $chalet->isAvailable(
            $request->check_in_date,
            $request->check_out_date
        );

        return $this->successResponse([
            'available' => $isAvailable,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'chalet' => [
                'id' => $chalet->id,
                'name' => $chalet->name,
                'slug' => $chalet->slug,
                'price_per_night' => (float) $chalet->price_per_night,
            ]
        ], $isAvailable ? 'الشاليه متاح للحجز' : 'الشاليه غير متاح في هذه التواريخ');
    }

    /**
     * Get chalets owned by authenticated user
     */
    public function myCharets(): JsonResponse
    {
        $user = Auth::guard('api')->user();

        if ($user->user_type !== 'owner') {
            return $this->forbiddenResponse('هذه الخدمة متاحة لمالكي الشاليات فقط');
        }

        $chalets = Chalet::with(['images', 'amenities'])
            ->where('owner_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return $this->paginatedResponse(
            $chalets->through(fn($chalet) => new ChaletResource($chalet)),
            'تم جلب شاليهاتك بنجاح'
        );
    }
}

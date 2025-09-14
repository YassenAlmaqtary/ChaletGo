<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Chalet extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'location',
        'latitude',
        'longitude',
        'price_per_night',
        'max_guests',
        'bedrooms',
        'bathrooms',
        'is_active',
        'is_featured',
        'availability_calendar',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'price_per_night' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'availability_calendar' => 'array',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Owner relationship
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Images relationship
     */
    public function images(): HasMany
    {
        return $this->hasMany(ChaletImage::class)->orderBy('sort_order');
    }

    /**
     * Primary image relationship
     */
    public function primaryImage(): HasMany
    {
        return $this->hasMany(ChaletImage::class)->where('is_primary', true);
    }

    /**
     * Amenities relationship
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'chalet_amenity');
    }

    /**
     * Bookings relationship
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Reviews relationship
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    /**
     * All reviews (including unapproved)
     */
    public function allReviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Scope for active chalets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for featured chalets
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('is_active', true);
    }

    /**
     * Get average rating
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Get total reviews count
     */
    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }

    /**
     * Check if chalet is available for given dates
     */
    public function isAvailable($checkIn, $checkOut)
    {
        // Check if there are any conflicting bookings
        $conflictingBookings = $this->bookings()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in_date', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in_date', '<=', $checkIn)
                          ->where('check_out_date', '>=', $checkOut);
                    });
            })
            ->exists();

        return !$conflictingBookings;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return number_format((float) $this->price_per_night, 2) . ' ريال';
    }
}

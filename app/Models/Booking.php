<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'chalet_id',
        'customer_id',
        'booking_number',
        'check_in_date',
        'check_out_date',
        'guests_count',
        'total_amount',
        'discount_amount',
        'status',
        'special_requests',
        'booking_details',
    ];

    protected $casts = [
        'check_in_date' => 'datetime',
        'check_out_date' => 'datetime',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'booking_details' => 'array',
    ];

    /**
     * Booking statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    /**
     * Get booking statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => __('enums.booking_statuses.pending'),
            self::STATUS_CONFIRMED => __('enums.booking_statuses.confirmed'),
            self::STATUS_CANCELLED => __('enums.booking_statuses.cancelled'),
            self::STATUS_COMPLETED => __('enums.booking_statuses.completed'),
        ];
    }

    /**
     * Chalet relationship
     */
    public function chalet(): BelongsTo
    {
        return $this->belongsTo(Chalet::class);
    }

    /**
     * Customer relationship
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Payments relationship
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Review relationship
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Booking extras relationship
     */
    public function extras(): HasMany
    {
        return $this->hasMany(BookingExtra::class);
    }

    /**
     * Get the completed payment for the booking
     */
    public function completedPayment()
    {
        return $this->hasOne(Payment::class)->where('status', 'completed');
    }

    /**
     * Check if booking is paid
     */
    public function isPaid(): bool
    {
        return $this->payments()->where('status', 'completed')->exists();
    }

    /**
     * Get total paid amount
     */
    public function getTotalPaidAmount(): float
    {
        return $this->payments()
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Generate unique booking number
     */
    public static function generateBookingNumber()
    {
        do {
            $number = 'BK' . date('Y') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('booking_number', $number)->exists());

        return $number;
    }

    /**
     * Get total nights
     */
    public function getTotalNightsAttribute()
    {
        return Carbon::parse($this->check_in_date)->diffInDays(Carbon::parse($this->check_out_date));
    }

    /**
     * Get final amount (total - discount)
     */
    public function getFinalAmountAttribute()
    {
        return $this->total_amount - $this->discount_amount;
    }

    /**
     * Check if booking is active
     */
    public function isActive()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Check if booking can be cancelled
     */
    public function canBeCancelled()
    {
        return $this->status === self::STATUS_PENDING ||
               ($this->status === self::STATUS_CONFIRMED && Carbon::parse($this->check_in_date)->isFuture());
    }

    /**
     * Scope for active bookings
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for current bookings
     */
    public function scopeCurrent($query)
    {
        $today = Carbon::today();
        return $query->where('check_in_date', '<=', $today)
                    ->where('check_out_date', '>=', $today)
                    ->where('status', self::STATUS_CONFIRMED);
    }
}

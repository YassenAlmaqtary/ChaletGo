<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'payment_method',
        'amount',
        'status',
        'transaction_id',
        'payment_details',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'paid_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'في الانتظار',
            self::STATUS_COMPLETED => 'مكتمل',
            self::STATUS_FAILED => 'فاشل',
            self::STATUS_REFUNDED => 'مسترد',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}

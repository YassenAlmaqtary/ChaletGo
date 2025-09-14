<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingExtra extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'extra_name',
        'extra_price',
        'quantity',
    ];

    protected $casts = [
        'extra_price' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function getTotalPriceAttribute()
    {
        return $this->extra_price * $this->quantity;
    }
}

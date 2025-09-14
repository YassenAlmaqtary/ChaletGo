<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'check_in_date' => $this->check_in_date->format('Y-m-d'),
            'check_out_date' => $this->check_out_date->format('Y-m-d'),
            'guests_count' => $this->guests_count,
            'total_nights' => $this->total_nights,
            'total_amount' => (float) $this->total_amount,
            'discount_amount' => (float) $this->discount_amount,
            'final_amount' => (float) $this->final_amount,
            'status' => $this->status,
            'status_label' => $this->getStatuses()[$this->status] ?? $this->status,
            'special_requests' => $this->special_requests,
            'booking_details' => $this->booking_details,
            'can_be_cancelled' => $this->canBeCancelled(),
            'chalet' => $this->whenLoaded('chalet', function () {
                return [
                    'id' => $this->chalet->id,
                    'name' => $this->chalet->name,
                    'slug' => $this->chalet->slug,
                    'location' => $this->chalet->location,
                    'price_per_night' => (float) $this->chalet->price_per_night,
                    'primary_image' => $this->chalet->images->where('is_primary', true)->first()?->image_url,
                ];
            }),
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                ];
            }),
            'payments' => $this->whenLoaded('payments', function () {
                return $this->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => (float) $payment->amount,
                        'status' => $payment->status,
                        'payment_method' => $payment->payment_method,
                        'paid_at' => $payment->paid_at?->format('Y-m-d H:i:s'),
                    ];
                });
            }),
            'extras' => $this->whenLoaded('extras', function () {
                return $this->extras->map(function ($extra) {
                    return [
                        'id' => $extra->id,
                        'name' => $extra->extra_name,
                        'price' => (float) $extra->extra_price,
                        'quantity' => $extra->quantity,
                        'total_price' => (float) $extra->total_price,
                    ];
                });
            }),
            'review' => $this->whenLoaded('review', function () {
                return $this->review ? [
                    'id' => $this->review->id,
                    'rating' => $this->review->rating,
                    'comment' => $this->review->comment,
                    'is_approved' => $this->review->is_approved,
                    'created_at' => $this->review->created_at->format('Y-m-d H:i:s'),
                ] : null;
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}

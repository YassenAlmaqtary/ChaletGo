<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_approved' => $this->is_approved,
            'customer' => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
            ],
            'chalet' => $this->whenLoaded('chalet', [
                'id' => $this->chalet->id,
                'name' => $this->chalet->name,
                'slug' => $this->chalet->slug,
            ]),
            'booking' => $this->whenLoaded('booking', [
                'id' => $this->booking->id,
                'booking_number' => $this->booking->booking_number,
            ]),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}

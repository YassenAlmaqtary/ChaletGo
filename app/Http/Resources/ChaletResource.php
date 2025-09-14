<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChaletResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'location' => $this->location,
            'coordinates' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'price_per_night' => (float) $this->price_per_night,
            'formatted_price' => $this->formatted_price,
            'max_guests' => $this->max_guests,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'is_featured' => $this->is_featured,
            'rating' => [
                'average' => round($this->average_rating, 1),
                'total_reviews' => $this->total_reviews,
            ],
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->image_url,
                        'alt_text' => $image->alt_text,
                        'is_primary' => $image->is_primary,
                        'sort_order' => $image->sort_order,
                    ];
                });
            }),
            'primary_image' => $this->whenLoaded('images', function () {
                $primaryImage = $this->images->where('is_primary', true)->first();
                return $primaryImage ? [
                    'id' => $primaryImage->id,
                    'url' => $primaryImage->image_url,
                    'alt_text' => $primaryImage->alt_text,
                ] : null;
            }),
            'amenities' => $this->whenLoaded('amenities', function () {
                return $this->amenities->map(function ($amenity) {
                    return [
                        'id' => $amenity->id,
                        'name' => $amenity->name,
                        'icon' => $amenity->icon,
                        'category' => $amenity->category,
                    ];
                });
            }),
            'owner' => $this->whenLoaded('owner', function () {
                return [
                    'id' => $this->owner->id,
                    'name' => $this->owner->name,
                    'phone' => $this->owner->phone,
                ];
            }),
            'availability' => $this->availability_calendar,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}

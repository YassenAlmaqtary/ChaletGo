<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ChaletImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chalet_id',
        'image_path',
        'alt_text',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Chalet relationship
     */
    public function chalet(): BelongsTo
    {
        return $this->belongsTo(Chalet::class);
    }

    /**
     * Get full image URL
     */
    public function getImageUrlAttribute()
    {
        return Storage::url($this->image_path);
    }

    /**
     * Scope for primary images
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}

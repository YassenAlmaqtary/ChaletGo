<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Amenity extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'icon',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Owner relationship
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Chalets relationship
     */
    public function chalets(): BelongsToMany
    {
        return $this->belongsToMany(Chalet::class, 'chalet_amenity');
    }

    /**
     * Scope for active amenities
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get amenity categories
     */
    public static function getCategories()
    {
        return [
            'general' => 'عام',
            'entertainment' => 'ترفيه',
            'safety' => 'أمان',
            'comfort' => 'راحة',
            'outdoor' => 'خارجي',
        ];
    }
}

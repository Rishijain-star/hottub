<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotTub extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand', 'brand_id', 'product_type', 'model', 'tier', 'seats', 'jets', 'dimensions', 'power_requirements',
        'status', 'featured_on_homepage', 'comfort', 'efficiency', 'features', 'quality', 'value', 'overall', 'pros', 'cons', 'images', 'description', 'slug'
    ];

    protected $casts = [
        'featured_on_homepage' => 'boolean',
        'pros' => 'array',
        'cons' => 'array',
        'images' => 'array',
        'brand_id' => 'integer',
        'seats' => 'integer',
        'jets' => 'integer',
        'comfort' => 'float',
        'efficiency' => 'float',
        'features' => 'float',
        'quality' => 'float',
        'value' => 'float',
        'overall' => 'float',
    ];
    public function featuredContents()
    {
        return $this->hasMany(FeaturedContent::class, 'hot_tub_id');
    }
}

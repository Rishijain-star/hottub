<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutdoorProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand', 'brand_id', 'product_type', 'model', 'tier', 'dimensions',
        'status', 'quality', 'durability', 'features', 'value', 'overall', 'pros', 'cons', 'images', 'description', 'slug'
    ];

    protected $casts = [
        'pros' => 'array',
        'cons' => 'array',
        'images' => 'array',
        'brand_id' => 'integer',
        'quality' => 'float',
        'durability' => 'float',
        'features' => 'float',
        'value' => 'float',
        'overall' => 'float',
    ];
}

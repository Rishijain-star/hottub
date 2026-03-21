<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'part_number',
        'category',
        'price',
        'description',
        'images',
        'compatible_brand_ids',
        'status',
        'slug',
    ];

    protected $casts = [
        'images' => 'array',
        'compatible_brand_ids' => 'array',
        'price' => 'decimal:2',
    ];
}

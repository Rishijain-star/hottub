<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'image_url',
        'images',
        'description',
        'includes',
        'status',
    ];

    protected $casts = [
        'includes' => 'array',
        'images' => 'array',
        'price' => 'decimal:2',
    ];
}

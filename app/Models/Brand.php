<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'types',
        'website',
        'description',
        'featured',
        'slug',
        'country_of_origin',
        'logo_path',
        'is_active',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'is_active' => 'boolean',
        'types' => 'array',
    ];
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'credits',
        'price',
        'savings_label',
        'most_popular',
        'position',
    ];

    protected $casts = [
        'most_popular' => 'boolean',
        'price' => 'decimal:2',
        'credits' => 'integer',
        'position' => 'integer',
    ];
}

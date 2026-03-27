<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPlan extends Model
{
    protected $fillable = [
        'name',
        'credits',
        'price',
        'validity_days',
        'badge_type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];
}

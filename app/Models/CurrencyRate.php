<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    protected $fillable = [
        'rate_date',
        'base_currency',
        'rates',
        'source',
        'fetched_at',
    ];

    protected $casts = [
        'rate_date' => 'date',
        'rates' => 'array',
        'fetched_at' => 'datetime',
    ];
}

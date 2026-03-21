<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'enquiry_prices',
        'lead_credit_costs',
        'featured_prices',
    ];

    protected $casts = [
        'enquiry_prices' => 'array',
        'lead_credit_costs' => 'array',
        'featured_prices' => 'array',
    ];
}

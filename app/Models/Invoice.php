<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'dealer_id',
        'credits',
        'amount',
        'credit_plan_id',
        'plan_name',
        'plan_description',
        'currency',
        'status',
        'payment_id',
        'stripe_session_id',
        'payment_details',
    ];

    protected $casts = [
        'credits' => 'integer',
        'amount' => 'decimal:2',
        'payment_details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }
}


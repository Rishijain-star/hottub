<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPurchase extends Model
{
    protected $fillable = [
        'user_id',
        'credit_plan_id',
        'amount',
        'credits_added',
        'payment_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(CreditPlan::class, 'credit_plan_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentProcessorSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'active_processor', // manual|paypal|stripe
        'mode', // test|live
        'paypal_client_id',
        'paypal_secret',
        'stripe_publishable_key',
        'stripe_secret_key',
        'stripe_webhook_secret',
    ];
}


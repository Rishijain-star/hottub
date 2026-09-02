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

    /**
     * Publishable key: .env / config first, then admin DB (Pricing processor page).
     */
    public static function stripePublishableKey(): ?string
    {
        $fromEnv = config('services.stripe.key');
        if (filled($fromEnv)) {
            return $fromEnv;
        }

        return static::query()->value('stripe_publishable_key');
    }

    /**
     * Secret key: .env / config first, then admin DB.
     */
    public static function stripeSecretKey(): ?string
    {
        $fromEnv = config('services.stripe.secret');
        if (filled($fromEnv)) {
            return $fromEnv;
        }

        return static::query()->value('stripe_secret_key');
    }

    /**
     * Webhook signing secret: .env / config first, then admin DB.
     */
    public static function stripeWebhookSecret(): ?string
    {
        $fromEnv = config('services.stripe.webhook_secret');
        if (filled($fromEnv)) {
            return $fromEnv;
        }

        return static::query()->value('stripe_webhook_secret');
    }

    public static function stripeIsConfigured(): bool
    {
        $secret = static::stripeSecretKey();
        $publishable = static::stripePublishableKey();

        return filled($secret)
            && filled($publishable)
            && str_starts_with($secret, 'sk_')
            && str_starts_with($publishable, 'pk_');
    }
}


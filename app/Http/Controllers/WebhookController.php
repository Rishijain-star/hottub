<?php

namespace App\Http\Controllers;

use App\Models\PaymentProcessorSetting;
use App\Models\CreditRequest;
use App\Services\Payment\StripeService;
use App\Services\Payment\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleStripe(Request $request)
    {
        $settings = PaymentProcessorSetting::first();
        if (! PaymentProcessorSetting::stripeIsConfigured()) {
            return response()->json(['error' => 'Stripe not configured'], 400);
        }

        $payload = $request->all();
        $sigHeader = $request->header('Stripe-Signature');
        
        $stripeService = new StripeService();
        $success = $stripeService->handleWebhook($payload, $sigHeader, $settings);

        return $success ? response()->json(['status' => 'success']) : response()->json(['status' => 'failed'], 400);
    }

    public function handlePayPal(Request $request)
    {
        $settings = PaymentProcessorSetting::first();
        if (!$settings || !$settings->paypal_client_id) {
            return response()->json(['error' => 'PayPal not configured'], 400);
        }

        $payload = $request->all();
        $paypalService = new PayPalService();
        $success = $paypalService->handleWebhook($payload, '', $settings);

        return $success ? response()->json(['status' => 'success']) : response()->json(['status' => 'failed'], 400);
    }
}

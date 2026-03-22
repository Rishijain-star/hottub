<?php

namespace App\Services\Payment;

use App\Models\CreditRequest;
use App\Models\PaymentProcessorSetting;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;

class StripeService implements PaymentInterface
{
    public function createCheckoutSession(CreditRequest $creditRequest, PaymentProcessorSetting $settings): string
    {
        if (str_starts_with($settings->stripe_secret_key, 'pk_')) {
            throw new \Exception('Configuration Error: You have entered a Publishable Key (pk_...) in the Secret Key field. Please use your Secret Key (sk_...) instead.');
        }

        Stripe::setApiKey($settings->stripe_secret_key);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => [
                        'name' => $creditRequest->credits . ' Credits Purchase',
                    ],
                    'unit_amount' => (int)($creditRequest->amount * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('payment.success'),
            'cancel_url' => route('dashboard') . '?payment=cancel',
            'metadata' => [
                'credit_request_id' => $creditRequest->id,
                'user_id' => $creditRequest->user_id,
            ],
        ]);

        return $session->url;
    }

    public function handleWebhook(array $payload, string $signature, PaymentProcessorSetting $settings): bool
    {
        try {
            $event = Webhook::constructEvent(
                json_encode($payload),
                $signature,
                $settings->stripe_webhook_secret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe Webhook: Invalid payload');
            return false;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Stripe Webhook: Invalid signature');
            return false;
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $creditRequestId = $session->metadata->credit_request_id;
            
            $creditRequest = CreditRequest::find($creditRequestId);
            if ($creditRequest && $creditRequest->status === 'pending') {
                $user = $creditRequest->user;
                
                // Update Credit Request
                $creditRequest->status = 'approved';
                $creditRequest->save();

                // Add Credits to User
                $user->credits += $creditRequest->credits;
                $user->save();

                // Create Invoice
                Invoice::create([
                    'invoice_number' => 'INV-' . time() . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
                    'dealer_id' => $user->id,
                    'credits' => $creditRequest->credits,
                    'amount' => $creditRequest->amount,
                    'status' => 'paid',
                    'payment_id' => $session->payment_intent,
                ]);

                return true;
            }
        }

        return true;
    }
}

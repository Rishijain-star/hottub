<?php

namespace App\Services\Payment;

use App\Models\CreditRequest;
use App\Models\PaymentProcessorSetting;
use App\Models\Invoice;
use App\Models\User;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use Illuminate\Support\Facades\Log;

class PayPalService implements PaymentInterface
{
    private function getClient(PaymentProcessorSetting $settings)
    {
        $environment = $settings->mode === 'live'
            ? new ProductionEnvironment($settings->paypal_client_id, $settings->paypal_secret)
            : new SandboxEnvironment($settings->paypal_client_id, $settings->paypal_secret);

        return new PayPalHttpClient($environment);
    }

    public function createCheckoutSession(CreditRequest $creditRequest, PaymentProcessorSetting $settings): string
    {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => 'credit_request_' . $creditRequest->id,
                'amount' => [
                    'value' => number_format($creditRequest->amount, 2, '.', ''),
                    'currency_code' => 'GBP'
                ],
                'description' => $creditRequest->credits . ' Credits Purchase'
            ]],
            'application_context' => [
                'cancel_url' => route('dashboard') . '?payment=cancel',
                'return_url' => route('payment.success') . '?credit_request_id=' . $creditRequest->id
            ]
        ];

        $client = $this->getClient($settings);
        $response = $client->execute($request);

        foreach ($response->result->links as $link) {
            if ($link->rel === 'approve') {
                return $link->href;
            }
        }

        throw new \Exception('PayPal: Approve link not found');
    }

    public function handleWebhook(array $payload, string $signature, PaymentProcessorSetting $settings): bool
    {
        // PayPal webhooks are complex to verify manually without SDK.
        // For credit requests, we can also use the return_url capture method or formal webhook.
        // Implementation for webhook event 'CHECKOUT.ORDER.APPROVED' or 'PAYMENT.CAPTURE.COMPLETED'
        
        Log::info('PayPal Webhook received', $payload);
        
        if ($payload['event_type'] === 'PAYMENT.CAPTURE.COMPLETED') {
            $resource = $payload['resource'];
            $amount = $resource['amount']['value'];
            $paymentId = $resource['id'];
            
            // In a real webhook, we would need to find the credit request.
            // PayPal custom metadata or looking up via amount/user is needed.
        }

        return true;
    }

    public function captureOrder(string $orderId, CreditRequest $creditRequest, PaymentProcessorSetting $settings)
    {
        $request = new OrdersCaptureRequest($orderId);
        $client = $this->getClient($settings);
        $response = $client->execute($request);

        if ($response->result->status === 'COMPLETED') {
            $user = $creditRequest->user;
            
            $creditRequest->status = 'approved';
            $creditRequest->save();

            $user->credits += $creditRequest->credits;
            $user->save();

            Invoice::create([
                'invoice_number' => 'INV-' . time() . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'dealer_id' => $user->id,
                'credits' => $creditRequest->credits,
                'amount' => $creditRequest->amount,
                'status' => 'paid',
                'payment_id' => $response->result->id,
            ]);

            return true;
        }

        return false;
    }
}

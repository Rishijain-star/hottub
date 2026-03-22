<?php

namespace App\Services\Payment;

use App\Models\CreditRequest;
use App\Models\PaymentProcessorSetting;

interface PaymentInterface
{
    public function createCheckoutSession(CreditRequest $creditRequest, PaymentProcessorSetting $settings): string;
    public function handleWebhook(array $payload, string $signature, PaymentProcessorSetting $settings): bool;
}

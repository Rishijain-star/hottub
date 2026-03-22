<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingSetting;
use Illuminate\Http\Request;

class PaymentProcessorController extends Controller
{
    public function index()
    {
        $settings = \App\Models\PaymentProcessorSetting::query()->first();
        return view('admin.pricing-processor', compact('settings'));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'active_processor' => ['required','in:manual,paypal,stripe'],
            'mode' => ['required','in:test,live'],
            'paypal_client_id' => ['nullable','string','max:255'],
            'paypal_secret' => ['nullable','string','max:255'],
            'stripe_publishable_key' => ['nullable','string','max:255'],
            'stripe_secret_key' => ['nullable','string','max:255'],
            'stripe_webhook_secret' => ['nullable','string','max:255'],
        ]);
        $settings = \App\Models\PaymentProcessorSetting::query()->first();
        if (!$settings) $settings = new \App\Models\PaymentProcessorSetting();
        $settings->fill($data);
        $settings->save();
        return back()->with('success', 'Payment processor settings saved.');
    }
}


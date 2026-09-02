<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\PricingSetting;
use App\Services\GeoRestrictionService;
use Illuminate\Http\Request;
use App\Services\GeocodingService;

class EnquiryController extends Controller
{
    public function submit(Request $request)
    {
        $geo = app(GeoRestrictionService::class);
        if ($geo->isAccessDenied($request)
            || $geo->isBlockedPhone($request->input('phone'))
            || $geo->isBlockedPostcode($request->input('postcode'))) {
            return back()->withErrors([
                'email' => $geo->genericDenyMessage(),
            ])->withInput();
        }

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255'],
            'phone' => ['nullable','string','max:255'],
            'postcode' => ['required','string','max:50'],
            'message' => ['nullable','string'],
            'timeframe' => ['nullable','string','max:100'],
            'type' => ['nullable','in:hot_tub,swim_spa,pool,sauna,outdoor_kitchen,outdoor_product,part,service,other'],
            'product_id' => ['nullable', 'integer'],
        ]);

        $type = $data['type'] ?? 'hot_tub';
        $canonicalType = (string) config('pricing.enquiry_type_aliases.' . $type, $type);

        $product = null;
        if (! empty($data['product_id'])) {
            if ($canonicalType === 'outdoor_product') {
                $product = \App\Models\OutdoorProduct::find($data['product_id']);
            } elseif (in_array($canonicalType, ['hot_tub', 'swim_spa'], true)) {
                $product = \App\Models\HotTub::find($data['product_id']);
            }
        }

        $settings = PricingSetting::query()->first();
        $enquiryPrices = $settings?->enquiry_prices ?? [];
        $leadCreditCosts = $settings?->lead_credit_costs ?? [];

        $priceRaw = null;
        if ($canonicalType === 'service') {
            // Service enquiries must use Admin > Lead Credit Costs (Legacy) > Service Enquiries.
            $priceRaw = $leadCreditCosts['service'] ?? null;
        } elseif ($canonicalType === 'part') {
            // Parts enquiries must use Admin > Lead Credit Costs (Legacy) > Parts Enquiries.
            $priceRaw = $leadCreditCosts['parts'] ?? null;
        } else {
            // Keep existing pricing behaviour: one admin price per canonical enquiry type.
            $priceRaw = $enquiryPrices[$canonicalType] ?? null;
        }

        $price = $priceRaw !== null ? (float) $priceRaw : 0.0;

        // If it's a "part" enquiry, it's national level (no postcode restriction logic needed here, 
        // as the actual restriction is usually in the lead matching/purchase logic)
        $geo = app(GeocodingService::class)->geocode($data['postcode']);
        $lead = Lead::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'session_id' => session()->getId(),
            'phone' => $data['phone'] ?? null,
            'postcode' => $data['postcode'],
            'lead_postcode' => $data['postcode'],
            'lead_lat' => $geo['lat'] ?? null,
            'lead_lng' => $geo['lng'] ?? null,
            'interests' => [$canonicalType],
            'timeframe' => $data['timeframe'] ?? null,
            'message' => $data['message'] ?? null,
            'price' => $price,
            'status' => 'new',
            'assigned_dealer_id' => null,
            'is_national' => in_array($canonicalType, ['part', 'service'], true), // Mark as national for distribution logic
            'delivery_details' => $product ? [
                'product_id' => $product->id,
                'make' => $product->brand,
                'model' => $product->model,
            ] : null,
        ]);

        // Log creation and add auto-tasks
        if ($lead) {
            $this->logInitialLeadActivities($lead);
        }

        return response()->json(['ok' => true, 'lead_id' => $lead->id]);
    }

    private function logInitialLeadActivities(Lead $lead): void
    {
        // Activity: Lead Created
        $lead->activities()->create([
            'type' => 'activity',
            'dealer_id' => null, // System activity
            'content' => 'Lead created via customer enquiry form.',
        ]);
    }
}

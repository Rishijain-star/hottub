<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\PricingSetting;
use Illuminate\Http\Request;
use App\Services\GeocodingService;

class EnquiryController extends Controller
{
    public function submit(Request $request)
    {
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
        $product = null;
        if (!empty($data['product_id'])) {
            if ($type === 'outdoor_product') {
                $product = \App\Models\OutdoorProduct::find($data['product_id']);
            } elseif (in_array($type, ['hot_tub', 'swim_spa'])) {
                $product = \App\Models\HotTub::find($data['product_id']);
            }
        }

        $settings = PricingSetting::query()->first();
        $prices = $settings?->enquiry_prices ?? [];
        $price = isset($prices[$type]) ? (float) $prices[$type] : 0.0;

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
            'interests' => [$type],
            'timeframe' => $data['timeframe'] ?? null,
            'message' => $data['message'] ?? null,
            'price' => $price,
            'status' => 'new',
            'assigned_dealer_id' => null,
            'is_national' => in_array($type, ['part', 'service']), // Mark as national for distribution logic
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

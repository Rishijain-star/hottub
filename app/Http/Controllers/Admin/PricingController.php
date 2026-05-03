<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditPackage;
use App\Models\CreditPlan;
use App\Models\PricingSetting;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        $packages = CreditPackage::orderBy('position')->get();
        $settings = PricingSetting::query()->first();

        // If packages exist but no active checkout plans (e.g. before sync was added), mirror once on load.
        if ($packages->isNotEmpty() && CreditPlan::where('is_active', true)->doesntExist()) {
            CreditPlan::query()->update(['is_active' => false]);
            foreach ($packages as $pkg) {
                CreditPlan::create([
                    'name' => $pkg->credits . ' Credits',
                    'credits' => $pkg->credits,
                    'price' => $pkg->price,
                    'validity_days' => 365,
                    'badge_type' => $pkg->most_popular ? 'Popular' : ($pkg->savings_label ?: null),
                    'description' => $pkg->savings_label,
                    'is_active' => true,
                ]);
            }
        }

        return view('admin.pricing', compact('packages', 'settings'));
    }

    public function savePackages(Request $request)
    {
        $data = $request->validate([
            'credits' => ['required', 'array'],
            'credits.*' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'array'],
            'price.*' => ['required', 'numeric', 'min:0'],
            'savings_label' => ['nullable', 'array'],
            'savings_label.*' => ['nullable', 'string', 'max:255'],
            'most_popular' => ['nullable', 'array'],
        ]);
        CreditPackage::query()->delete();
        $count = count($data['credits']);
        for ($i = 0; $i < $count; $i++) {
            CreditPackage::create([
                'credits' => (int) $data['credits'][$i],
                'price' => (float) $data['price'][$i],
                'savings_label' => $data['savings_label'][$i] ?? null,
                'most_popular' => isset($data['most_popular'][$i]) && $data['most_popular'][$i] ? true : false,
                'position' => $i,
            ]);
        }

        // Dealer/manufacturer credit checkout reads CreditPlan — mirror admin packages there.
        CreditPlan::query()->update(['is_active' => false]);
        foreach (CreditPackage::orderBy('position')->get() as $pkg) {
            CreditPlan::create([
                'name' => $pkg->credits . ' Credits',
                'credits' => $pkg->credits,
                'price' => $pkg->price,
                'validity_days' => 365,
                'badge_type' => $pkg->most_popular ? 'Popular' : ($pkg->savings_label ?: null),
                'description' => $pkg->savings_label,
                'is_active' => true,
            ]);
        }

        return back()->with('success', 'Credit packages saved.');
    }

    public function saveEnquiryPricing(Request $request)
    {
        $data = $request->validate([
            'hot_tub' => ['required', 'numeric', 'min:0'],
            'swim_spa' => ['required', 'numeric', 'min:0'],
            'pool' => ['required', 'numeric', 'min:0'],
            'sauna' => ['required', 'numeric', 'min:0'],
            'outdoor_kitchen' => ['required', 'numeric', 'min:0'],
            'other' => ['required', 'numeric', 'min:0'],
        ]);
        $settings = PricingSetting::query()->first() ?: new PricingSetting();
        $settings->enquiry_prices = $data;
        $settings->save();
        return back()->with('success', 'Enquiry pricing saved.');
    }

    public function saveLeadPricing(Request $request)
    {
        $data = $request->validate([
            'budget' => ['required', 'numeric', 'min:0'],
            'mid_range' => ['required', 'numeric', 'min:0'],
            'premium' => ['required', 'numeric', 'min:0'],
            'luxury' => ['required', 'numeric', 'min:0'],
            'swim_spa' => ['required', 'numeric', 'min:0'],
            'service' => ['required', 'numeric', 'min:0'],
            'parts' => ['required', 'numeric', 'min:0'],
            'manufacturer_multiplier' => ['required', 'numeric', 'min:0'],
        ]);
        $settings = PricingSetting::query()->first() ?: new PricingSetting();
        $settings->lead_credit_costs = $data;
        $settings->save();
        return back()->with('success', 'Lead pricing saved.');
    }

    public function saveFeaturedPricing(Request $request)
    {
        $data = $request->validate([
            'product_of_month' => ['required', 'numeric', 'min:0'],
            'delivery_of_week' => ['required', 'numeric', 'min:0'],
        ]);
        $settings = PricingSetting::query()->first() ?: new PricingSetting();
        $settings->featured_prices = $data;
        $settings->save();
        return back()->with('success', 'Featured pricing saved.');
    }
}

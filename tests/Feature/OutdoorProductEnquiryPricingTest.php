<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\PricingSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutdoorProductEnquiryPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_outdoor_product_enquiry_uses_admin_enquiry_price(): void
    {
        PricingSetting::create([
            'enquiry_prices' => [
                'hot_tub' => 10,
                'swim_spa' => 11,
                'pool' => 12,
                'sauna' => 13,
                'outdoor_product' => 44.99,
                'other' => 14,
            ],
            'lead_credit_costs' => [
                'service' => 5,
                'parts' => 3,
                'manufacturer_multiplier' => 1,
            ],
        ]);

        $this->postJson(route('enquiry.submit'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '07000000000',
            'postcode' => 'SW1A 1AA',
            'message' => '',
            'type' => 'outdoor_product',
            'product_id' => null,
        ])->assertOk()->assertJson(['ok' => true]);

        $lead = Lead::query()->first();
        $this->assertNotNull($lead);
        $this->assertSame(44.99, (float) $lead->price);
        $this->assertSame(['outdoor_product'], $lead->interests);
    }

    public function test_legacy_outdoor_kitchen_type_maps_to_outdoor_product_price_and_interest(): void
    {
        PricingSetting::create([
            'enquiry_prices' => [
                'hot_tub' => 10,
                'swim_spa' => 11,
                'pool' => 12,
                'sauna' => 13,
                'outdoor_product' => 55,
                'other' => 14,
            ],
            'lead_credit_costs' => [],
        ]);

        $this->postJson(route('enquiry.submit'), [
            'name' => 'Legacy',
            'email' => 'legacy@example.com',
            'postcode' => 'M1 1AA',
            'type' => 'outdoor_kitchen',
        ])->assertOk()->assertJson(['ok' => true]);

        $lead = Lead::query()->where('email', 'legacy@example.com')->first();
        $this->assertNotNull($lead);
        $this->assertSame(55.0, (float) $lead->price);
        $this->assertSame(['outdoor_product'], $lead->interests);
    }
}

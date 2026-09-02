<?php

use App\Models\PricingSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (PricingSetting::query()->cursor() as $settings) {
            $ep = $settings->enquiry_prices;
            if (! is_array($ep)) {
                continue;
            }
            if (! array_key_exists('outdoor_product', $ep) && array_key_exists('outdoor_kitchen', $ep)) {
                $ep['outdoor_product'] = $ep['outdoor_kitchen'];
            }
            unset($ep['outdoor_kitchen']);
            $settings->enquiry_prices = $ep;
            $settings->save();
        }
    }

    public function down(): void
    {
        foreach (PricingSetting::query()->cursor() as $settings) {
            $ep = $settings->enquiry_prices;
            if (! is_array($ep)) {
                continue;
            }
            if (! array_key_exists('outdoor_kitchen', $ep) && array_key_exists('outdoor_product', $ep)) {
                $ep['outdoor_kitchen'] = $ep['outdoor_product'];
            }
            unset($ep['outdoor_product']);
            $settings->enquiry_prices = $ep;
            $settings->save();
        }
    }
};

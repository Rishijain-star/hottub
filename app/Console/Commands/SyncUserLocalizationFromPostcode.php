<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LocalizationService;
use Illuminate\Console\Command;

class SyncUserLocalizationFromPostcode extends Command
{
    protected $signature = 'users:sync-localization-from-postcode {--force : Re-apply even when preferences already set}';

    protected $description = 'Set country, locale, and currency from coordinates (first) or postcode (fallback)';

    public function handle(LocalizationService $localization): int
    {
        $query = User::query();

        if (! $this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('preferred_locale')
                    ->orWhere('preferred_currency', 'GBP');
            });
        }

        $count = 0;
        foreach ($query->cursor() as $user) {
            $lat = $user->registration_lat
                ?? $user->dealer_lat
                ?? $user->manufacturer_lat;
            $lng = $user->registration_lng
                ?? $user->dealer_lng
                ?? $user->manufacturer_lng;

            $lat = is_numeric($lat) ? (float) $lat : null;
            $lng = is_numeric($lng) ? (float) $lng : null;

            if ($lat === null && $lng === null && empty($user->postcode)) {
                continue;
            }

            $localization->resolveAndPersistForUser(
                $user,
                $user->postcode,
                $lat,
                $lng,
            );
            $count++;
        }

        $this->info("Updated {$count} user(s).");

        return self::SUCCESS;
    }
}

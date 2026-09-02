<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use App\Services\LocalizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocalePreferenceController extends Controller
{
    public function update(Request $request, LocalizationService $localization, CurrencyService $currency): RedirectResponse
    {
        $preference = (string) $request->input('preference', 'locale');

        if ($preference === 'currency') {
            $request->validate([
                'currency' => 'required|string|in:'.implode(',', array_keys(config('localization.currencies', []))),
            ]);

            session(['currency_manual' => true]);
            $currency->setCurrency((string) $request->input('currency'));

            return back();
        }

        $request->validate([
            'locale' => 'required|string|in:'.implode(',', array_keys(config('localization.locales', []))),
        ]);

        session()->forget(['geo_lat', 'geo_lng', 'geo_resolved_at', 'geo_unavailable']);
        session(['locale_manual' => true, 'currency_manual' => false]);

        $localization->setLocale((string) $request->input('locale'));

        return back();
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\GeoRestrictionService;
use App\Services\LocalizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationPreferenceController extends Controller
{
    public function store(Request $request, LocalizationService $localization, GeoRestrictionService $geo): JsonResponse
    {
        if ($request->boolean('unavailable')) {
            if (! session('locale_manual')) {
                $localization->markGeoUnavailable();
            }

            return response()->json([
                'ok' => true,
                'locale' => config('localization.default_locale', 'en_GB'),
                'currency' => config('localization.default_currency', 'GBP'),
                'unavailable' => true,
            ]);
        }

        $data = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($geo->isBlockedCoordinates((float) $data['latitude'], (float) $data['longitude'])) {
            $geo->persistBlock($request, 'gps_pk');

            return response()->json([
                'message' => $geo->genericDenyMessage(),
            ], 403)->withCookie($geo->blockCookie());
        }

        session()->forget('geo_unavailable');

        $region = $localization->storeLiveCoordinates(
            (float) $data['latitude'],
            (float) $data['longitude'],
            Auth::user(),
        );

        return response()->json([
            'ok' => true,
            'locale' => $region['locale'],
            'currency' => $region['currency'],
            'country_code' => $region['country_code'],
            'source' => $region['source'],
        ]);
    }
}

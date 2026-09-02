<?php

namespace App\Services;

use App\Models\CurrencyRate;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Convert stored amounts (GBP credit plans, GBP catalog prices) to the visitor's currency
 * using daily rates in currency_rates (base: GBP).
 */
class CurrencyService
{
    protected ?array $ratesCache = null;

    public function currentCurrency(): string
    {
        $currency = session('currency', config('localization.default_currency', 'GBP'));
        $supported = array_keys(config('localization.currencies', []));

        return in_array($currency, $supported, true) ? $currency : 'GBP';
    }

    public function setCurrency(string $currency): void
    {
        $supported = array_keys(config('localization.currencies', []));
        if (in_array($currency, $supported, true)) {
            session(['currency' => $currency]);
            $this->ratesCache = null;
        }
    }

    /**
     * @return array<string, float>
     */
    public function ratesForToday(): array
    {
        if ($this->ratesCache !== null) {
            return $this->ratesCache;
        }

        $base = config('localization.base_currency', 'GBP');
        $row = CurrencyRate::query()
            ->where('base_currency', $base)
            ->orderByDesc('rate_date')
            ->first();

        if ($row && is_array($row->rates)) {
            $this->ratesCache = $row->rates;

            return $this->ratesCache;
        }

        $this->ratesCache = [$base => 1.0];

        return $this->ratesCache;
    }

    public function convert(float $amount, string $fromCurrency, ?string $toCurrency = null): float
    {
        $from = strtoupper($fromCurrency);
        $to = strtoupper($toCurrency ?? $this->currentCurrency());
        $base = strtoupper(config('localization.base_currency', 'GBP'));

        if ($from === $to) {
            return round($amount, 2);
        }

        $rates = $this->ratesForToday();
        $rates[$base] = 1.0;

        $fromRate = (float) ($rates[$from] ?? 0);
        $toRate = (float) ($rates[$to] ?? 0);

        if ($fromRate <= 0 || $toRate <= 0) {
            return round($amount, 2);
        }

        $inBase = $from === $base ? $amount : $amount / $fromRate;
        $converted = $to === $base ? $inBase : $inBase * $toRate;

        return round($converted, 2);
    }

    /** @deprecated Use convert($amount, 'GBP') */
    public function convertFromGbp(float $amountGbp, ?string $toCurrency = null): float
    {
        return $this->convert($amountGbp, 'GBP', $toCurrency);
    }

    public function format(float $amount, ?string $currency = null, ?string $fromCurrency = null): string
    {
        $from = strtoupper($fromCurrency ?? config('localization.storage.products', 'GBP'));
        $currency = strtoupper($currency ?? $this->currentCurrency());
        $meta = config("localization.currencies.{$currency}", []);
        $decimals = (int) ($meta['decimals'] ?? 2);
        $symbol = (string) ($meta['symbol'] ?? $currency.' ');

        $converted = $this->convert($amount, $from, $currency);
        $formatted = number_format($converted, $decimals);

        if (in_array($currency, ['EUR', 'USD', 'GBP', 'CHF'], true)) {
            return $symbol.$formatted;
        }

        if ($currency === 'INR') {
            return '₹'.$formatted;
        }

        return $formatted.' '.$symbol;
    }

    /**
     * Fetch today's rates (GBP base) from open.er-api.com — includes INR, USD, GBP, etc.
     */
    public function fetchAndStoreDailyRates(): CurrencyRate
    {
        $base = config('localization.base_currency', 'GBP');
        $url = config('localization.api_url', 'https://open.er-api.com/v6/latest/GBP');

        $response = Http::timeout(30)->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException('Currency API failed: HTTP '.$response->status());
        }

        $json = $response->json();
        if (($json['result'] ?? '') !== 'success' && empty($json['rates'])) {
            throw new \RuntimeException('Currency API returned invalid payload.');
        }

        $allRates = is_array($json['rates'] ?? null) ? $json['rates'] : [];
        $supported = array_keys(config('localization.currencies', []));
        $rates = [$base => 1.0];

        foreach ($supported as $code) {
            if ($code === $base) {
                continue;
            }
            if (isset($allRates[$code])) {
                $rates[$code] = (float) $allRates[$code];
            }
        }

        $rateDate = isset($json['time_last_update_utc'])
            ? date('Y-m-d', strtotime($json['time_last_update_utc']))
            : now()->toDateString();

        return CurrencyRate::query()->updateOrCreate(
            [
                'rate_date' => $rateDate,
                'base_currency' => $base,
            ],
            [
                'rates' => $rates,
                'source' => 'open.er-api',
                'fetched_at' => now(),
            ],
        );
    }

    public function ensureRatesExist(): void
    {
        try {
            $base = config('localization.base_currency', 'GBP');
            $latest = CurrencyRate::query()
                ->where('base_currency', $base)
                ->orderByDesc('rate_date')
                ->first();

            if ($latest === null || $latest->rate_date->lt(now()->subDay())) {
                $this->fetchAndStoreDailyRates();
            }
        } catch (Throwable) {
            // Site still works in default currency if API unavailable.
        }
    }
}

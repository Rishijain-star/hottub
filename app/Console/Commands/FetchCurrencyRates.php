<?php

namespace App\Console\Commands;

use App\Services\CurrencyService;
use Illuminate\Console\Command;
use Throwable;

class FetchCurrencyRates extends Command
{
    protected $signature = 'currency:fetch-rates';

    protected $description = 'Fetch daily FX rates (GBP base) from Frankfurter API and store per day';

    public function handle(CurrencyService $currency): int
    {
        try {
            $row = $currency->fetchAndStoreDailyRates();
            $this->info('Stored rates for '.$row->rate_date->format('Y-m-d').' ('.count($row->rates).' currencies).');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}

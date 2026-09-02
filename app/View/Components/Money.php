<?php

namespace App\View\Components;

use App\Services\CurrencyService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Money extends Component
{
    public string $formatted;

    public function __construct(
        CurrencyService $currency,
        float|int|string|null $amount,
        ?string $currencyCode = null,
        ?string $from = null,
    ) {
        $fromCurrency = $from ?? config('localization.storage.products', 'GBP');
        $this->formatted = $currency->format((float) ($amount ?? 0), $currencyCode, $fromCurrency);
    }

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
<span class="money">{{ $formatted }}</span>
BLADE;
    }
}

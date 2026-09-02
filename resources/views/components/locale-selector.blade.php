@props(['showCurrency' => false])

@php
    $currentLocaleMeta = $availableLocales[$currentLocale] ?? ['flag' => '🌐', 'label' => $currentLocale];
    $currencies = config('localization.currencies', []);
    $currentCurrencyMeta = $currencies[$currentCurrency] ?? ['symbol' => $currentCurrency, 'name' => $currentCurrency];
    $currentSymbol = trim($currentCurrencyMeta['symbol'] ?? $currentCurrency);
@endphp

<div class="locale-selector" data-locale-picker-root>
    {{-- Language --}}
    <div class="locale-picker" data-locale-picker>
        <button
            type="button"
            class="locale-picker__trigger locale-picker__trigger--flag"
            aria-haspopup="listbox"
            aria-expanded="false"
            aria-label="{{ __('nav.language') }}: {{ $currentLocaleMeta['label'] ?? $currentLocale }}"
            title="{{ $currentLocaleMeta['label'] ?? $currentLocale }}"
        >
            <span class="locale-picker__value" aria-hidden="true"><x-country-flag :locale="$currentLocale" size="md" /></span>
            <span class="locale-picker__chevron" aria-hidden="true"></span>
        </button>
        <ul class="locale-picker__menu" role="listbox" aria-label="{{ __('nav.language') }}">
            @foreach($availableLocales as $code => $meta)
                <li role="none">
                    <form method="POST" action="{{ route('locale.preference') }}" class="locale-picker__form">
                        @csrf
                        <input type="hidden" name="preference" value="locale">
                        <input type="hidden" name="locale" value="{{ $code }}">
                        <button
                            type="submit"
                            class="locale-picker__option {{ $currentLocale === $code ? 'is-selected' : '' }}"
                            role="option"
                            aria-selected="{{ $currentLocale === $code ? 'true' : 'false' }}"
                        >
                            <x-country-flag :locale="$code" size="md" class="locale-picker__option-flag" />
                            <span class="locale-picker__option-label">
                                {{ $meta['label'] }}
                                @if(!empty($meta['country']))
                                    <span class="locale-picker__option-country">{{ $meta['country'] }}</span>
                                @endif
                            </span>
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
    </div>

    @if($showCurrency)
    {{-- Currency (dealer & manufacturer panels only) --}}
    <div class="locale-picker" data-locale-picker>
        <button
            type="button"
            class="locale-picker__trigger locale-picker__trigger--currency"
            aria-haspopup="listbox"
            aria-expanded="false"
            aria-label="{{ __('nav.currency') }}: {{ $currentCurrencyMeta['name'] ?? $currentCurrency }}"
            title="{{ ($currentCurrencyMeta['name'] ?? $currentCurrency).' ('.$currentCurrency.')' }}"
        >
            <span class="locale-picker__value locale-picker__value--symbol" aria-hidden="true">{{ $currentSymbol }}</span>
            <span class="locale-picker__chevron" aria-hidden="true"></span>
        </button>
        <ul class="locale-picker__menu" role="listbox" aria-label="{{ __('nav.currency') }}">
            @foreach($currencies as $code => $meta)
                @php $symbol = trim($meta['symbol'] ?? $code); @endphp
                <li role="none">
                    <form method="POST" action="{{ route('locale.preference') }}" class="locale-picker__form">
                        @csrf
                        <input type="hidden" name="preference" value="currency">
                        <input type="hidden" name="currency" value="{{ $code }}">
                        <button
                            type="submit"
                            class="locale-picker__option {{ $currentCurrency === $code ? 'is-selected' : '' }}"
                            role="option"
                            aria-selected="{{ $currentCurrency === $code ? 'true' : 'false' }}"
                        >
                            <span class="locale-picker__option-symbol">{{ $symbol }}</span>
                            <span class="locale-picker__option-label">{{ $meta['name'] ?? $code }}</span>
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>

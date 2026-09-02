@extends('layouts.dealer')
@section('title', __('panel.quotes.title', ['count' => $items->count()]).' - '.__('panel.dealer_title'))
@section('content')
<div class="panel-page-header"><div><h1 class="panel-page-title">{{ __('panel.nav.available_leads') }}</h1><p class="panel-page-sub">{{ __('panel.quotes.sub') }}</p></div></div>
<div class="card" style="padding:0;">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('panel.common.customer') }}</th>
                <th>{{ __('panel.common.postcode') }}</th>
                <th>{{ __('panel.common.interests') }}</th>
                <th>{{ __('panel.common.credit') }}</th>
                <th>{{ __('panel.common.purchased') }}</th>
                <th>{{ __('panel.common.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $it)
            <tr>
                <td>
                    <div class="fw-700 text-dark">{{ __('panel.common.name_hidden') }}</div>
                    <div class="text-sm text-muted">{{ __('panel.common.email_hidden') }}</div>
                </td>
                <td>{{ $it->postcode }}</td>
                <td>
                    @if(is_array($it->interests))
                        @foreach($it->interests as $tag)<span class="badge">{{ \App\Support\PanelTranslator::interestLabel($tag) }}</span> @endforeach
                    @else — @endif
                </td>
                <td>@if(!is_null($it->price)) {{ number_format($it->price, 2) }} @else — @endif</td>
                <td>{{ trans_choice('panel.common.dealers_purchased', $it->purchases->count(), ['count' => $it->purchases->count()]) }}</td>
                <td>
                    @if($it->purchases->count() >= 3)
                        <span class="badge badge--danger">{{ __('panel.common.sold_out') }}</span>
                    @else
                        <form action="{{ route('dealer.leads.purchase', $it) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn--primary btn--sm">{{ __('panel.common.buy') }}</button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted" style="text-align:center;padding:1rem">{{ __('panel.common.no_leads') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($items->hasPages())
    <div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
@endif
@endsection

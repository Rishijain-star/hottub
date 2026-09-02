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
            @php $cnt = (int) ($counts[$it->id] ?? 0); $iBought = in_array($it->id, $mine ?? []); @endphp
            <tr data-lead-id="{{ $it->id }}">
                <td>
                    <div class="fw-700 text-dark">{{ $iBought ? $it->name : __('panel.common.name_hidden') }}</div>
                    <div class="text-sm text-muted">{{ $iBought ? $it->email : __('panel.common.email_hidden') }}</div>
                </td>
                <td>{{ $it->postcode }}</td>
                <td>
                    @if(is_array($it->interests) && count($it->interests))
                        @foreach($it->interests as $tag)
                            <span class="badge">{{ \App\Support\PanelTranslator::interestLabel($tag) }}</span>
                        @endforeach
                    @else
                        —
                    @endif
                </td>
                <td>@if(!is_null($it->price)) {{ number_format($it->price, 2) }} @else — @endif</td>
                <td><span class="text-sm">{{ trans_choice('panel.common.dealers_purchased', $cnt, ['count' => $cnt]) }}</span></td>
                <td>
                    @if($iBought)
                        <a class="btn btn--ghost btn--sm" href="{{ route('dealer.leads.view', $it) }}">{{ __('panel.common.view') }}</a>
                    @elseif($cnt >= 3)
                        <button class="btn btn--ghost btn--sm" disabled>{{ __('panel.common.sold_out') }}</button>
                    @else
                        <button class="btn btn--primary btn--sm js-buy-lead" data-id="{{ $it->id }}">{{ __('panel.common.buy') }}</button>
                        <form action="{{ route('dealer.leads.decline', $it->id) }}" method="POST" style="display:inline-block; margin-left: 6px;" onsubmit='event.preventDefault(); showConfirmationModal(this, @json(__('panel.quotes.decline_confirm_title')), @json(__('panel.quotes.decline_confirm_body')), @json(__('panel.quotes.decline_confirm_yes')));'>
                            @csrf
                            <button class="btn btn--danger-soft btn--sm">{{ __('panel.common.decline') }}</button>
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
    <div class="mt-4 quotes-available-pagination" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
@endif

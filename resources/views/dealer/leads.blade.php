@extends('layouts.dealer')
@section('title', __('panel.leads.title').' - '.__('panel.dealer_title'))
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">{{ __('panel.leads.title') }}</h1><p class="panel-page-sub">{{ __('panel.leads.sub') }}</p></div>
    <button class="btn btn--primary btn--pill" onclick="document.getElementById('addLeadCard').style.display='block'">{{ __('panel.leads.create_private') }}</button>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif

<div class="card" id="addLeadCard" style="display:none; margin-bottom: 20px;">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">{{ __('panel.leads.create_private_title') }}</div>
    <form method="POST" action="{{ route('dealer.leads.private.store') }}">
        @csrf
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">{{ __('panel.leads.customer_name') }}</label><input name="name" class="form-input" required></div>
            <div class="form-group"><label class="form-label">{{ __('panel.leads.email_required') }}</label><input name="email" type="email" class="form-input" required></div>
            <div class="form-group"><label class="form-label">{{ __('panel.leads.phone') }}</label><input name="phone" class="form-input"></div>
            <div class="form-group"><label class="form-label">{{ __('panel.leads.postcode') }}</label><input name="postcode" class="form-input"></div>
        </div>
        <div class="form-group"><label class="form-label">{{ __('panel.leads.address') }}</label><textarea name="address" class="form-input" rows="2"></textarea></div>
        <div class="form-group">
            <label class="form-label">{{ __('panel.leads.lead_source') }}</label>
            <select name="source" class="form-input">
                <option value="">{{ __('panel.leads.select_source') }}</option>
                <option value="Email">{{ __('panel.leads.source_email') }}</option>
                <option value="Social Media">{{ __('panel.leads.source_social_media') }}</option>
                <option value="Show">{{ __('panel.leads.source_show') }}</option>
                <option value="Phone">{{ __('panel.leads.source_phone') }}</option>
                <option value="Walk-in">{{ __('panel.leads.source_walk_in') }}</option>
            </select>
        </div>
        <div class="modal-actions" style="justify-content: flex-start;">
            <button class="btn btn--primary">{{ __('panel.leads.save_lead') }}</button>
            <button type="button" class="btn btn--ghost" onclick="document.getElementById('addLeadCard').style.display='none'">{{ __('panel.common.cancel') }}</button>
        </div>
    </form>
</div>

{{-- Search Bar --}}
<div class="card mb-4">
    <form method="GET" action="{{ route('dealer.leads.index') }}" class="panel-filter-form panel-filter-form--3">
        @if(request('lead_status'))
            <input type="hidden" name="lead_status" value="{{ request('lead_status') }}">
        @endif
        <div class="form-group mb-0">
            <label class="form-label">{{ __('panel.common.search') }}</label>
            <input type="text" name="search" class="form-input" placeholder="{{ __('panel.leads.search_placeholder') }}" value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">{{ __('panel.common.status') }}</label>
            <select name="status" class="form-input">
                <option value="">{{ __('panel.common.all_status') }}</option>
                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>{{ __('panel.stages.new_lead') }}</option>
                <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>{{ __('panel.stages.contacted') }}</option>
                <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>{{ __('panel.leads.converted') }}</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>{{ __('panel.leads.closed') }}</option>
            </select>
        </div>
        <div class="form-group mb-0 panel-filter-actions-col">
            <label class="form-label panel-filter-actions__label-spacer" aria-hidden="true">&nbsp;</label>
            <div class="panel-filter-actions">
            <button type="submit" class="btn btn--primary">{{ __('panel.common.filter') }}</button>
            <a href="{{ route('dealer.leads.index') }}" class="btn btn--ghost">{{ __('panel.common.clear') }}</a>
            </div>
        </div>
    </form>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div class="fw-800" style="font-size:1.1rem; color:var(--gray-900)">{{ __('panel.leads.private_leads') }}</div>
    <div>
        <form method="GET" action="{{ route('dealer.leads.index') }}" id="privateStatusFilterForm">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            @if(request('lead_status'))
                <input type="hidden" name="lead_status" value="{{ request('lead_status') }}">
            @endif
            <select name="private_status" class="form-input" style="width: 160px; padding: 0.5rem;" onchange="document.getElementById('privateStatusFilterForm').submit()">
                <option value="">{{ __('panel.leads.all_private_leads') }}</option>
                <option value="active" {{ request('private_status') === 'active' ? 'selected' : '' }}>{{ __('panel.leads.active') }}</option>
                <option value="converted" {{ request('private_status') === 'converted' ? 'selected' : '' }}>{{ __('panel.leads.converted') }}</option>
                <option value="lost" {{ request('private_status') === 'lost' ? 'selected' : '' }}>{{ __('panel.leads.lost') }}</option>
            </select>
        </form>
    </div>
</div>
<div class="card" style="padding:0; margin-bottom: 2rem;">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('panel.leads.customer') }}</th>
                <th>{{ __('panel.leads.postcode_col') }}</th>
                <th>{{ __('panel.leads.source') }}</th>
                <th>{{ __('panel.leads.created_on') }}</th>
                <th>{{ __('panel.leads.status') }}</th>
                <th>{{ __('panel.leads.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($privateLeads as $it)
            @php
                $privateStatus = __('panel.leads.active');
                if ($it->status === 'converted') {
                    $privateStatus = __('panel.leads.converted');
                } elseif ($it->stage === 'Lost' || $it->status === 'closed') {
                    $privateStatus = __('panel.leads.lost');
                }
            @endphp
            <tr>
                <td>
                    <div class="fw-700 text-dark">{{ $it->name }}</div>
                    <div class="text-sm text-muted">{{ $it->email }}</div>
                </td>
                <td>{{ $it->postcode ?: '—' }}</td>
                <td><span class="badge" style="background: #f1f5f9; color: #475569;">{{ $it->source ?: __('panel.leads.direct') }}</span></td>
                <td class="text-sm text-muted">{{ $it->created_at->format('d M Y') }}</td>
                <td>
                    <span class="fw-800" style="font-size:0.85rem; color: var(--primary-600);">{{ $privateStatus }}</span>
                </td>
                <td>
                    <a href="{{ route('dealer.leads.view', $it->id) }}" class="btn btn--ghost btn--sm">{{ __('panel.common.view') }}</a>
                    <form action="{{ route('dealer.leads.private.destroy', $it->id) }}" method="POST" style="display:inline-block; margin-left: 6px;" onsubmit="event.preventDefault(); showConfirmationModal(this, '{{ __('panel.leads.delete_lead_title') }}', '{{ __('panel.leads.delete_lead_body') }}', '{{ __('panel.leads.delete_lead_confirm') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn--danger-soft btn--sm">{{ __('panel.lead.delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted" style="text-align:center;padding:2rem">{{ __('panel.leads.no_private_leads') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($privateLeads->hasPages())
    <div class="mt-4 mb-4">
        {{ $privateLeads->appends(request()->except('private_page'))->links('components.pagination') }}
    </div>
@endif

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div class="fw-800" style="font-size:1.1rem; color:var(--gray-900)">{{ __('panel.leads.won_purchased') }}</div>
    <div>
        <form method="GET" action="{{ route('dealer.leads.index') }}" id="statusFilterForm">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <select name="lead_status" class="form-input" style="width: 150px; padding: 0.5rem;" onchange="document.getElementById('statusFilterForm').submit()">
                <option value="">{{ __('panel.leads.all_leads') }}</option>
                <option value="active" {{ request('lead_status') === 'active' ? 'selected' : '' }}>{{ __('panel.leads.active') }}</option>
                <option value="won" {{ request('lead_status') === 'won' ? 'selected' : '' }}>{{ __('panel.leads.won') }}</option>
                <option value="closed" {{ request('lead_status') === 'closed' ? 'selected' : '' }}>{{ __('panel.leads.closed') }}</option>
            </select>
        </form>
    </div>
</div>
<div class="card" style="padding:0; margin-bottom: 2rem;">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('panel.leads.customer') }}</th>
                <th>{{ __('panel.leads.postcode_col') }}</th>
                <th>{{ __('panel.leads.interests') }}</th>
                <th>{{ __('panel.leads.price') }}</th>
                <th>{{ __('panel.leads.purchased_on') }}</th>
                <th>{{ __('panel.leads.status') }}</th>
                <th>{{ __('panel.leads.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($myLeads as $it)
            @php
                $purchase = \App\Models\LeadPurchase::where('lead_id', $it->id)->where('dealer_id', auth()->id())->where('buyer_role', 'dealer')->first();
                
                $status = __('panel.leads.active');
                $statusClass = ''; // Default
                
                if ($it->assigned_dealer_id === auth()->id() && $it->status === 'converted' && $it->stage === 'Delivered') {
                    $status = __('panel.leads.won');
                    $statusClass = 'text-success';
                } elseif (($purchase && $purchase->stage === 'Lost') || ($it->status === 'converted' && $it->assigned_dealer_id && $it->assigned_dealer_id !== auth()->id())) {
                    $status = __('panel.leads.closed');
                    $statusClass = 'text-warning';
                }

                $purchasedOnFormatted = null;
                if (!empty($it->latest_purchase_date)) {
                    $purchasedOnFormatted = \Illuminate\Support\Carbon::parse($it->latest_purchase_date)->format('d M Y');
                } elseif ($purchase && $purchase->created_at) {
                    $purchasedOnFormatted = $purchase->created_at->format('d M Y');
                } else {
                    $purchasedOnFormatted = optional($it->created_at)->format('d M Y') ?? '—';
                }

                $purchasePrice = $purchase
                    ? (float) ($purchase->amount ?? $it->price ?? 0)
                    : (float) ($it->price ?? 0);
            @endphp
            <tr>
                <td>
                    <div class="fw-700 text-dark">{{ ($status === __('panel.leads.closed')) ? __('panel.common.name_hidden') : $it->name }}</div>
                    <div class="text-sm text-muted">{{ ($status === __('panel.leads.closed')) ? __('panel.common.email_hidden') : $it->email }}</div>
                </td>
                <td>{{ $it->postcode ?: '—' }}</td>
                <td>
                    <div style="display:flex; flex-direction:column; gap:2px">
                        @if(is_array($it->interests) && count($it->interests))
                            @foreach($it->interests as $tag)
                                <div class="fw-700 text-dark" style="font-size:0.85rem">{{ \App\Support\PanelTranslator::interestLabel($tag) }}</div>
                                <div class="text-xs text-muted">{{ __('panel.interests.hot_tub') }}</div>
                            @endforeach
                        @else
                            —
                        @endif
                    </div>
                </td>
                <td><div class="fw-700 text-dark">£{{ number_format($purchasePrice, 2) }}</div></td>
                <td class="text-sm text-muted">{{ $purchasedOnFormatted }}</td>
                <td>
                    @if($status === __('panel.leads.won'))
                        <span class="fw-800 text-success" style="font-size:0.85rem">{{ __('panel.leads.won') }}</span>
                    @elseif($status === __('panel.leads.closed'))
                        <span class="badge" style="background: #ffedd5; color: #9a3412; font-weight: 800; font-size: 0.75rem; padding: 4px 8px;">{{ __('panel.leads.closed') }}</span>
                    @else
                        <span class="fw-800" style="font-size:0.85rem; color: #1e293b;">{{ __('panel.leads.active') }}</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('dealer.leads.view', $it->id) }}" class="fw-700 text-dark" style="text-decoration:none">{{ __('panel.common.view') }}</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-muted" style="text-align:center;padding:2rem">{{ __('panel.leads.no_won_leads') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($myLeads->hasPages())
    <div class="mt-4">
        {{ $myLeads->appends(request()->except('won_page'))->links('components.pagination') }}
    </div>
@endif
@endsection

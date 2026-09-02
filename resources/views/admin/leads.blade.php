@extends('layouts.admin')
@section('title', __('panel.admin.nav.leads') . ' - ' . __('panel.admin_title'))
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">{{ __('panel.admin.leads.title') }}</h1><p class="panel-page-sub">{{ __('panel.admin.leads.sub') }}</p></div>
    <button class="btn btn--primary btn--pill" id="toggleCreateLead">{{ __('panel.admin.leads.create_manually') }}</button>
</div>
@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert--danger">{{ $errors->first() }}</div> @endif

{{-- ─── FILTERS ─────────────────────────────────────────────────── --}}
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.leads') }}" class="panel-filter-form panel-filter-form--3">
        <div class="form-group mb-0">
            <label class="form-label">{{ __('panel.admin.common.search') }}</label>
            <input type="text" name="search" class="form-input" placeholder="{{ __('panel.admin.leads.name_email_phone') }}" value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">{{ __('panel.admin.common.status') }}</label>
            <select name="status" class="form-input">
                <option value="">{{ __('panel.admin.common.all_status') }}</option>
                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>{{ __('panel.admin.leads.new') }}</option>
                <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>{{ __('panel.admin.leads.contacted') }}</option>
                <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>{{ __('panel.admin.leads.converted') }}</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>{{ __('panel.admin.leads.closed') }}</option>
            </select>
        </div>
        <div class="form-group mb-0 panel-filter-actions-col">
            <label class="form-label panel-filter-actions__label-spacer" aria-hidden="true">&nbsp;</label>
            <div class="panel-filter-actions">
            <button type="submit" class="btn btn--primary">{{ __('panel.admin.common.filter') }}</button>
            <a href="{{ route('admin.leads') }}" class="btn btn--ghost">{{ __('panel.admin.common.clear') }}</a>
            </div>
        </div>
    </form>
</div>

<div class="panel-stats-grid">
    <div class="panel-stat-card"><div class="panel-stat-card__label">{{ __('panel.admin.overview.total_leads') }}</div><div class="panel-stat-card__value">{{ $total }}</div></div>
    <div class="panel-stat-card"><div class="panel-stat-card__label">{{ __('panel.admin.leads.converted') }}</div><div class="panel-stat-card__value">{{ $converted }}</div></div>
    <div class="panel-stat-card"><div class="panel-stat-card__label">{{ __('panel.admin.leads.total_sales_value') }}</div><div class="panel-stat-card__value">£{{ number_format($totalValue,2) }}</div></div>
    <div class="panel-stat-card"><div class="panel-stat-card__label">{{ __('panel.admin.overview.conversion_rate') }}</div><div class="panel-stat-card__value">{{ number_format($conversionRate,1) }}%</div></div>
</div>

<div class="card" id="createLeadCard" style="display:none">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">{{ __('panel.admin.leads.create_manually') }}</div>
    <form method="POST" action="{{ route('admin.leads.store') }}">
        @csrf
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">{{ __('panel.admin.common.name') }} *</label><input name="name" class="form-input" required></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.common.email') }} *</label><input name="email" class="form-input" type="email" required></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.leads.temporary_password') }} *</label><input name="temporary_password" class="form-input" type="text" minlength="8" required></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.common.phone') }} *</label><input name="phone" class="form-input"></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.common.postcode') }} *</label><input name="postcode" class="form-input" required></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.common.price') }} (GBP) *</label><input name="price" class="form-input" type="number" step="0.01" min="0" required></div>
            <div class="form-group" style="display:flex;align-items:center;gap:8px;margin-top:25px">
                <input type="checkbox" name="is_national" id="is_national" value="1">
                <label for="is_national" class="form-label" style="margin-bottom:0">{{ __('panel.admin.leads.national_lead') }}</label>
            </div>
        </div>
        <div class="form-group"><label class="form-label">{{ __('panel.admin.leads.looking_for') }} *</label>
            <div class="grid grid--3">
                @foreach(['hot_tub'=>'Hot Tub','swim_spa'=>'Swim Spa','pool'=>'Pool','sauna'=>'Sauna','outdoor_product'=>'Outdoor Product','other'=>'Other'] as $key=>$label)
                <label class="form-check" style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="interests[]" value="{{ $key }}"> {{ $label }}</label>
                @endforeach
            </div>
        </div>
        <div class="form-group"><label class="form-label">{{ __('panel.admin.leads.preferred_timeframe') }}</label>
            <select name="timeframe" class="form-input">
                <option>{{ __('panel.admin.leads.not_specified') }}</option>
                <option>{{ __('panel.admin.leads.immediate') }}</option>
                <option>{{ __('panel.admin.leads.months_1_3') }}</option>
                <option>{{ __('panel.admin.leads.months_3_6') }}</option>
                <option>{{ __('panel.admin.leads.months_6_plus') }}</option>
            </select>
        </div>
        <div class="form-group"><label class="form-label">{{ __('panel.admin.common.message') }}</label><textarea name="message" class="form-input" rows="4" placeholder="{{ __('panel.admin.leads.customer_notes') }}"></textarea></div>
        <input type="hidden" name="status" value="new">
        @include('components.upload-progress')
        <div class="modal-actions" style="justify-content:flex-start"><button class="btn btn--primary">{{ __('panel.admin.common.create') }} {{ __('panel.admin.nav.leads') }}</button></div>
    </form>
    <script>document.getElementById('toggleCreateLead')?.addEventListener('click',function(){const el=document.getElementById('createLeadCard');el.style.display=el.style.display==='none'?'':'';});</script>
</div>

<div class="card table-responsive--leads" style="padding:0;margin-top:1rem;">
    <table class="table table--leads">
        <thead><tr><th>{{ __('panel.admin.leads.lead_id') }}</th><th>{{ __('panel.admin.leads.customer') }}</th><th>{{ __('panel.admin.leads.product') }}</th><th>{{ __('panel.admin.leads.stage') }}</th><th>{{ __('panel.admin.common.status') }}</th><th>{{ __('panel.admin.leads.purchased_by') }}</th><th>{{ __('panel.admin.leads.winning_dealer') }}</th><th>{{ __('panel.admin.leads.sale_value') }}</th><th>{{ __('panel.admin.common.actions') }}</th></tr></thead>
        <tbody>
        @forelse($items as $it)
            @php
                $dd = $it->delivery_details ?? [];
                $hasMakeModel = !empty($dd['make']) || !empty($dd['model']);
                $hasInterests = is_array($it->interests) && count($it->interests);
            @endphp
            <tr>
                <td class="text-sm">{{ $it->id }}</td>
                <td class="leads-col-customer"><div class="fw-700 text-dark">{{ $it->name }}</div><div class="text-sm text-muted">{{ $it->email }}</div><div class="text-sm text-muted">{{ $it->postcode }}</div></td>
                <td class="leads-col-product">
                    <div class="leads-product-cell">
                        @if($hasMakeModel)
                            <div class="fw-700 text-dark leads-product-cell__title">
                                {{ trim(($dd['make'] ?? '') . ' ' . ($dd['model'] ?? '')) }}
                            </div>
                        @endif
                        @if($hasInterests)
                            <div class="leads-product-cell__tags">
                                @foreach($it->interests as $tag)
                                    <span class="badge" style="font-size:10px;padding:2px 6px">{{ ucwords(str_replace('_',' ',$tag)) }}</span>
                                @endforeach
                            </div>
                        @endif
                        @if($it->timeframe)
                            <div class="text-xs text-muted">{{ $it->timeframe }}</div>
                        @endif
                        @if(!$hasMakeModel && !$hasInterests && !$it->timeframe)
                            <span class="text-muted text-sm">—</span>
                        @endif
                    </div>
                </td>
                <td><span class="badge">{{ $it->stage ?? __('panel.admin.leads.new') }}</span></td>
                <td>
                    @php($showStageAsStatus = ($it->stage && $it->stage !== __('panel.admin.leads.new') && $it->status !== 'converted' && $it->status !== 'closed'))
                    @if($it->is_private)
                        <span class="badge badge--dark">Private (Dealer)</span>
                    @elseif($showStageAsStatus)
                        <span class="badge">{{ $it->stage }}</span>
                    @elseif($it->status==='converted')
                        <span class="badge badge--success">{{ __('panel.admin.leads.converted') }}</span>
                    @elseif($it->status==='contacted')
                        <span class="badge">{{ __('panel.admin.leads.contacted') }}</span>
                    @elseif($it->status==='closed')
                        <span class="badge badge--dark">{{ __('panel.admin.leads.closed') }}</span>
                    @else
                        <span class="badge">{{ __('panel.admin.leads.new') }}</span>
                    @endif
                </td>
                <td>
                    @php($b = $buyers[$it->id] ?? collect())
                    @if($b->count() > 0)
                        @foreach($b as $bp)
                            <span class="badge">{{ $bp->dealer_name }}</span>
                        @endforeach
                    @else — @endif
                </td>
                <td>
                    @if($it->assigned_dealer_id && isset($winners[$it->assigned_dealer_id]))
                        <span class="badge badge--success">{{ $winners[$it->assigned_dealer_id]->name }}</span>
                    @else — @endif
                </td>
                <td>@if(!is_null($it->price)) £{{ number_format($it->price, 2) }} @else — @endif</td>
                <td class="leads-col-actions">
                    <div class="actions-row">
                        <a href="{{ route('admin.leads.activity', $it) }}" class="icon-btn" title="{{ __('panel.admin.leads.activity') }}">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </a>
                        <a href="{{ route('admin.leads.edit', $it) }}" class="icon-btn" title="{{ __('panel.admin.common.edit') }}">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                            </svg>
                        </a>
                        <form method="POST" action="{{ route('admin.leads.destroy', $it) }}" onsubmit="return confirm('{{ __('panel.admin.leads.delete_confirm') }}');" style="display:inline-flex;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-btn" title="{{ __('panel.admin.common.delete') }}" style="color:#dc2626;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4.75A1.75 1.75 0 0 1 9.75 3h4.5A1.75 1.75 0 0 1 16 4.75V6m-8 0 1 13a2 2 0 0 0 2 1.85h4a2 2 0 0 0 2-1.85L18 6M10 10v6M14 10v6"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-muted">{{ __('panel.admin.leads.no_leads_found') }}</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
</div>
@endsection

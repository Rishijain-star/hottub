@extends('layouts.admin')
@section('title', 'Leads – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Lead Management & Analytics</h1><p class="panel-page-sub">Monitor lead progress and track dealer performance</p></div>
    <button class="btn btn--primary btn--pill" id="toggleCreateLead">Create Lead Manually</button>
</div>
@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert--danger">{{ $errors->first() }}</div> @endif

{{-- ─── FILTERS ─────────────────────────────────────────────────── --}}
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.leads') }}" class="grid grid--4" style="align-items: flex-end; gap: 1rem;">
        <div class="form-group mb-0">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Name, Email, Phone..." value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Status</label>
            <select name="status" class="form-input">
                <option value="">All Status</option>
                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>Contacted</option>
                <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </div>
      
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn--primary" style="flex: 1;">Filter</button>
            <a href="{{ route('admin.leads') }}" class="btn btn--ghost">Clear</a>
        </div>
    </form>
</div>

<div class="panel-stats-grid">
    <div class="panel-stat-card"><div class="panel-stat-card__label">Total Leads</div><div class="panel-stat-card__value">{{ $total }}</div></div>
    <div class="panel-stat-card"><div class="panel-stat-card__label">Converted</div><div class="panel-stat-card__value">{{ $converted }}</div></div>
    <div class="panel-stat-card"><div class="panel-stat-card__label">Total Sales Value</div><div class="panel-stat-card__value">£{{ number_format($totalValue,2) }}</div></div>
    <div class="panel-stat-card"><div class="panel-stat-card__label">Conversion Rate</div><div class="panel-stat-card__value">{{ number_format($conversionRate,1) }}%</div></div>
</div>

<div class="card" id="createLeadCard" style="display:none">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Create Lead Manually</div>
    <form method="POST" action="{{ route('admin.leads.store') }}">
        @csrf
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">Name *</label><input name="name" class="form-input" required></div>
            <div class="form-group"><label class="form-label">Email *</label><input name="email" class="form-input" type="email" required></div>
            <div class="form-group"><label class="form-label">Phone *</label><input name="phone" class="form-input"></div>
            <div class="form-group"><label class="form-label">Postcode *</label><input name="postcode" class="form-input" required></div>
            <div class="form-group"><label class="form-label">Price (£) *</label><input name="price" class="form-input" type="number" step="0.01" min="0" required></div>
            <div class="form-group" style="display:flex;align-items:center;gap:8px;margin-top:25px">
                <input type="checkbox" name="is_national" id="is_national" value="1">
                <label for="is_national" class="form-label" style="margin-bottom:0">National Lead (No postcode restriction for dealers)</label>
            </div>
        </div>
        <div class="form-group"><label class="form-label">What are they looking for? *</label>
            <div class="grid grid--3">
                @foreach(['hot_tub'=>'Hot Tub','swim_spa'=>'Swim Spa','pool'=>'Pool','sauna'=>'Sauna','outdoor_kitchen'=>'Outdoor Kitchen','other'=>'Other'] as $key=>$label)
                <label class="form-check" style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="interests[]" value="{{ $key }}"> {{ $label }}</label>
                @endforeach
            </div>
        </div>
        <div class="form-group"><label class="form-label">Preferred Timeframe</label>
            <select name="timeframe" class="form-input">
                <option>Not specified</option>
                <option>Immediate</option>
                <option>1–3 months</option>
                <option>3–6 months</option>
                <option>6+ months</option>
            </select>
        </div>
        <div class="form-group"><label class="form-label">Message</label><textarea name="message" class="form-input" rows="4" placeholder="Customer requirements or notes..."></textarea></div>
        <input type="hidden" name="status" value="new">
        @include('components.upload-progress')
        <div class="modal-actions" style="justify-content:flex-start"><button class="btn btn--primary">Create Lead</button></div>
    </form>
    <script>document.getElementById('toggleCreateLead')?.addEventListener('click',function(){const el=document.getElementById('createLeadCard');el.style.display=el.style.display==='none'?'':'';});</script>
</div>

<div class="card" style="padding:0;margin-top:1rem;">
    <table class="table">
        <thead><tr><th>Lead ID</th><th>Customer</th><th>Product</th><th>Stage</th><th>Status</th><th>Purchased By</th><th>Winning Dealer</th><th>Sale Value</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($items as $it)
            <tr>
                <td class="text-sm">{{ $it->id }}</td>
                <td><div class="fw-700 text-dark">{{ $it->name }}</div><div class="text-sm text-muted">{{ $it->email }}</div><div class="text-sm text-muted">{{ $it->postcode }}</div></td>
                <td>
                    <div style="display:flex;flex-direction:column;gap:4px">
                        @if(!empty($it->delivery_details['make']) || !empty($it->delivery_details['model']))
                            <div class="fw-700 text-dark" style="font-size:0.85rem">
                                {{ $it->delivery_details['make'] ?? '' }} {{ $it->delivery_details['model'] ?? '' }}
                            </div>
                        @endif
                        <div style="display:flex;flex-wrap:wrap;gap:4px">
                            @if(is_array($it->interests))
                                @foreach($it->interests as $tag)
                                    <span class="badge" style="font-size:10px;padding:2px 6px">{{ ucwords(str_replace('_',' ',$tag)) }}</span>
                                @endforeach
                            @endif
                        </div>
                        @if($it->timeframe)
                            <div class="text-xs text-muted">🕒 {{ $it->timeframe }}</div>
                        @endif
                    </div>
                </td>
                <td><span class="badge">{{ $it->stage ?? 'New Lead' }}</span></td>
                <td>
                    @php($showStageAsStatus = ($it->stage && $it->stage !== 'New Lead' && $it->status !== 'converted' && $it->status !== 'closed'))
                    @if($it->is_private)
                        <span class="badge badge--dark">Private (Dealer)</span>
                    @elseif($showStageAsStatus)
                        <span class="badge">{{ $it->stage }}</span>
                    @elseif($it->status==='converted')
                        <span class="badge badge--success">Converted</span>
                    @elseif($it->status==='contacted')
                        <span class="badge">Contacted</span>
                    @elseif($it->status==='closed')
                        <span class="badge badge--dark">Closed</span>
                    @else
                        <span class="badge">New</span>
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
                <td>
                    <div class="actions-row">
                        <a href="{{ route('admin.leads.activity', $it) }}" class="icon-btn" title="Activity">&#128221;</a>
                        <a href="{{ route('admin.leads.edit', $it) }}" class="icon-btn" title="Edit">✎</a>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-muted">No leads found.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
</div>
@endsection

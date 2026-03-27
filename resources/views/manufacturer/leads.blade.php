@extends('layouts.manufacturer')
@section('title', 'Leads – Manufacturer Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">My Leads</h1><p class="panel-page-sub">Leads you have purchased or created</p></div>
    <button class="btn btn--primary btn--pill" onclick="document.getElementById('addLeadCard').style.display='block'">+ Create Private Lead</button>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif

<div class="card" id="addLeadCard" style="display:none; margin-bottom: 20px;">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Create Private Lead</div>
    <form method="POST" action="{{ route('manufacturer.leads.private.store') }}">
        @csrf
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">Customer Name *</label><input name="name" class="form-input" required></div>
            <div class="form-group"><label class="form-label">Email *</label><input name="email" type="email" class="form-input" required></div>
            <div class="form-group"><label class="form-label">Phone</label><input name="phone" class="form-input"></div>
            <div class="form-group"><label class="form-label">Postcode</label><input name="postcode" class="form-input"></div>
        </div>
        <div class="form-group"><label class="form-label">Address</label><textarea name="address" class="form-input" rows="2"></textarea></div>
        <div class="form-group">
            <label class="form-label">Lead Source</label>
            <select name="source" class="form-input">
                <option value="">Select Source</option>
                <option value="Email">Email</option>
                <option value="Social Media">Social Media</option>
                <option value="Show">Show</option>
                <option value="Phone">Phone</option>
                <option value="Walk-in">Walk-in</option>
            </select>
        </div>
        <div class="modal-actions" style="justify-content: flex-start;">
            <button class="btn btn--primary">Save Lead</button>
            <button type="button" class="btn btn--ghost" onclick="document.getElementById('addLeadCard').style.display='none'">Cancel</button>
        </div>
    </form>
</div>

{{-- Search Bar --}}
<div class="card mb-4">
    <form method="GET" action="{{ route('manufacturer.leads') }}" class="grid grid--3" style="align-items: flex-end; gap: 1rem;">
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
            <a href="{{ route('manufacturer.leads') }}" class="btn btn--ghost">Clear</a>
        </div>
    </form>
</div>

<div class="fw-800 mb-3" style="font-size:1.1rem; color:var(--gray-900)">Available Leads</div>
<div class="card" style="padding:0; margin-bottom: 2rem;">
    <table class="table">
        <thead>
            <tr>
                <th>CUSTOMER</th>
                <th>POSTCODE</th>
                <th>INTERESTS</th>
                <th>STATUS</th>
                <th>ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($availableLeads as $it)
            <tr>
                <td>
                    <div class="fw-700 text-dark">{{ $it->name }}</div>
                    <div class="text-sm text-muted">{{ $it->email }}</div>
                </td>
                <td>{{ $it->postcode ?: '—' }}</td>
                <td>
                    <div style="display:flex; flex-direction:column; gap:2px">
                        @if(is_array($it->interests) && count($it->interests))
                            @foreach($it->interests as $tag)
                                <div class="fw-700 text-dark" style="font-size:0.85rem">{{ ucwords(str_replace('_',' ',$tag)) }}</div>
                                <div class="text-xs text-muted">HOT TUB</div>
                            @endforeach
                        @else
                            —
                        @endif
                    </div>
                </td>
                <td><div class="fw-700 text-dark">£{{ number_format($it->price ?: 0, 2) }}</div></td>
                <td>
                    <form action="{{ route('manufacturer.leads.buy', $it->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        <button class="btn btn--primary btn--sm">Buy Now</button>
                    </form>
                    <form action="{{ route('manufacturer.leads.decline', $it->id) }}" method="POST" style="display:inline-block; margin-left: 6px;" onsubmit="event.preventDefault(); showConfirmationModal(this, 'Decline Lead?', 'Are you sure you want to decline this lead?', 'Yes, Decline');">
                        @csrf
                        <button class="btn btn--danger-soft btn--sm">Decline</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-muted" style="text-align:center;padding:2rem">No available leads found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($availableLeads->hasPages())
    <div class="mt-4 mb-4">
        {{ $availableLeads->appends(request()->except('available_page'))->links('components.pagination') }}
    </div>
@endif

<div class="fw-800 mb-3" style="font-size:1.1rem; color:var(--gray-900)">Private Leads</div>
<div class="card" style="padding:0; margin-bottom: 2rem;">
    <table class="table">
        <thead>
            <tr>
                <th>CUSTOMER</th>
                <th>POSTCODE</th>
                <th>SOURCE</th>
                <th>CREATED ON</th>
                <th>STATUS</th>
                <th>ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($privateLeads as $it)
            <tr>
                <td>
                    <div class="fw-700 text-dark">{{ $it->name }}</div>
                    <div class="text-sm text-muted">{{ $it->email }}</div>
                </td>
                <td>{{ $it->postcode ?: '—' }}</td>
                <td><span class="badge" style="background: #f1f5f9; color: #475569;">{{ $it->source ?: 'Direct' }}</span></td>
                <td class="text-sm text-muted">{{ $it->created_at->format('d M Y') }}</td>
                <td>
                    <span class="fw-800" style="font-size:0.85rem; color: var(--primary-600);">{{ strtoupper($it->status) }}</span>
                </td>
                <td>
                    <a href="{{ route('manufacturer.leads.view', $it->id) }}" class="btn btn--ghost btn--sm">View</a>
                    <form action="{{ route('manufacturer.leads.private.destroy', $it->id) }}" method="POST" style="display:inline-block; margin-left: 6px;" onsubmit="event.preventDefault(); showConfirmationModal(this, 'Delete Lead?', 'Are you sure you want to delete this private lead? This action cannot be undone.', 'Yes, Delete');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn--danger-soft btn--sm">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted" style="text-align:center;padding:2rem">No private leads found.</td></tr>
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
    <div class="fw-800" style="font-size:1.1rem; color:var(--gray-900)">Won / Purchased Leads</div>
    <div>
        <form method="GET" action="{{ route('manufacturer.leads.index') }}" id="statusFilterForm">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            <select name="lead_status" class="form-input" style="width: 150px; padding: 0.5rem;" onchange="document.getElementById('statusFilterForm').submit()">
                <option value="">All Leads</option>
                <option value="active" {{ request('lead_status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="won" {{ request('lead_status') === 'won' ? 'selected' : '' }}>Won</option>
                <option value="closed" {{ request('lead_status') === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </form>
    </div>
</div>
<div class="card" style="padding:0; margin-bottom: 2rem;">
    <table class="table">
        <thead>
            <tr>
                <th>CUSTOMER</th>
                <th>POSTCODE</th>
                <th>INTERESTS</th>
                <th>PRICE</th>
                <th>PURCHASED ON</th>
                <th>STATUS</th>
                <th>ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($myLeads as $it)
            @php
                $purchase = \App\Models\LeadPurchase::where('lead_id', $it->id)->where('dealer_id', auth()->id())->where('buyer_role', 'manufacturer')->first();
                
                $status = 'ACTIVE';
                $statusClass = ''; // Default
                
                if ($it->assigned_dealer_id === auth()->id() && $it->status === 'converted' && $it->stage === 'Delivered') {
                    $status = 'WON';
                    $statusClass = 'text-success';
                } elseif (($purchase && $purchase->stage === 'Lost') || ($it->status === 'converted' && $it->assigned_dealer_id && $it->assigned_dealer_id !== auth()->id())) {
                    $status = 'CLOSED';
                    $statusClass = 'text-warning';
                }
            @endphp
            <tr>
                <td>
                    <div class="fw-700 text-dark">{{ ($status === 'CLOSED') ? 'Name Hidden' : $it->name }}</div>
                    <div class="text-sm text-muted">{{ ($status === 'CLOSED') ? 'Email Hidden' : $it->email }}</div>
                </td>
                <td>{{ $it->postcode ?: '—' }}</td>
                <td>
                    <div style="display:flex; flex-direction:column; gap:2px">
                        @if(is_array($it->interests) && count($it->interests))
                            @foreach($it->interests as $tag)
                                <div class="fw-700 text-dark" style="font-size:0.85rem">{{ ucwords(str_replace('_',' ',$tag)) }}</div>
                                <div class="text-xs text-muted">HOT TUB</div>
                            @endforeach
                        @else
                            —
                        @endif
                    </div>
                </td>
                <td><div class="fw-700 text-dark">£{{ number_format($it->price ?: 0, 2) }}</div></td>
                <td class="text-sm text-muted">{{ $it->created_at->format('d M Y') }}</td>
                <td>
                    @if($status === 'WON')
                        <span class="fw-800 text-success" style="font-size:0.85rem">WON</span>
                    @elseif($status === 'CLOSED')
                        <span class="badge" style="background: #ffedd5; color: #9a3412; font-weight: 800; font-size: 0.75rem; padding: 4px 8px;">CLOSED</span>
                    @else
                        <span class="fw-800" style="font-size:0.85rem; color: #1e293b;">ACTIVE</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('manufacturer.leads.view', $it->id) }}" class="fw-700 text-dark" style="text-decoration:none">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-muted" style="text-align:center;padding:2rem">No won leads found.</td></tr>
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

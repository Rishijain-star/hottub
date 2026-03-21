@extends('layouts.dealer')
@section('title', 'My Leads – Dealer Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">My Leads</h1><p class="panel-page-sub">Leads you have purchased or created</p></div>
    <button class="btn btn--primary btn--pill" onclick="document.getElementById('addLeadCard').style.display='block'">+ Create Private Lead</button>
</div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif

<div class="card" id="addLeadCard" style="display:none; margin-bottom: 20px;">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Create Private Lead</div>
    <form method="POST" action="{{ route('dealer.leads.private.store') }}">
        @csrf
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">Customer Name *</label><input name="name" class="form-input" required></div>
            <div class="form-group"><label class="form-label">Email</label><input name="email" type="email" class="form-input"></div>
            <div class="form-group"><label class="form-label">Phone</label><input name="phone" class="form-input"></div>
            <div class="form-group">
                <label class="form-label">Lead Source *</label>
                <select name="lead_source" class="form-input" required>
                    <option value="">Select Source...</option>
                    @foreach(['Phone Call','Email','Social Media','Showroom Visit','Show','Other'] as $src)
                        <option value="{{ $src }}">{{ $src }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group"><label class="form-label">Notes / Details</label><textarea name="message" class="form-input" rows="2"></textarea></div>
        <div class="modal-actions" style="justify-content: flex-start;">
            <button class="btn btn--primary">Save Lead</button>
            <button type="button" class="btn btn--ghost" onclick="document.getElementById('addLeadCard').style.display='none'">Cancel</button>
        </div>
    </form>
</div>

<div class="card" style="padding:0;">
    <table class="table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Postcode</th>
                <th>Interests</th>
                <th>Price</th>
                <th>Purchased On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $it)
            @php $purchase = $purchases[$it->id] ?? null; @endphp
            <tr>
                <td>
                    @if($purchase && $purchase->stage === 'Lost')
                        <div class="fw-700 text-dark">Name Hidden</div>
                        <div class="text-sm text-muted">Email Hidden</div>
                    @else
                        <div class="fw-700 text-dark">{{ $it->name }}</div>
                        <div class="text-sm text-muted">{{ $it->email }}</div>
                    @endif
                </td>
                <td>{{ $it->postcode }}</td>
                <td>
                    <div style="display:flex;flex-direction:column;gap:4px">
                        @if(!empty($it->delivery_details['make']) || !empty($it->delivery_details['model']))
                            <div class="fw-700 text-dark" style="font-size:0.85rem">
                                {{ $it->delivery_details['make'] ?? '' }} {{ $it->delivery_details['model'] ?? '' }}
                            </div>
                        @endif
                        <div style="display:flex;flex-wrap:wrap;gap:4px">
                            @if(is_array($it->interests) && count($it->interests))
                                @foreach($it->interests as $tag)
                                    <span class="badge" style="font-size:10px;padding:2px 6px">{{ ucwords(str_replace('_',' ',$tag)) }}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </td>
                <td>@if(!is_null($it->price)) £{{ number_format($it->price, 2) }} @else — @endif</td>
                <td class="text-sm">{{ $purchase ? $purchase->created_at->format('d M Y') : '—' }}</td>
                <td>
                    <a href="{{ route('dealer.leads.view', $it->id) }}" class="btn btn--ghost btn--sm">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted" style="text-align:center;padding:1rem">No leads purchased yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($items->hasPages())
    <div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
@endif
@endsection

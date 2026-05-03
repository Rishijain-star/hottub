@extends('layouts.manufacturer')
@section('title', 'Credits – Manufacturer Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Credits</h1>
        <p class="panel-page-sub">Request and manage your brand's account credits</p>
    </div>
    <div class="panel-stat-card" style="padding: 0.5rem 1rem; flex-direction: row; gap: 1rem; margin-bottom: 0;">
        <div class="text-sm text-muted">Current Balance:</div>
        <div class="fw-800 text-primary">{{ number_format($me->credits) }} Credits</div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert--success" style="margin-bottom: 1.5rem; padding: 1rem; background: #ecfdf5; color: #065f46; border-radius: 8px; border: 1px solid #a7f3d0;">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid--2" style="gap: 1.5rem; align-items: start;">
    {{-- Purchase Credit Plans --}}
    <div class="card">
        <div class="fw-800 mb-4" style="font-size:1.125rem;color:var(--gray-900)">Buy Credit Plans</div>
        
        <div class="plans-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:20px;margin-bottom:20px">
            @forelse($plans as $plan)
                <div class="plan-card" style="border:1px solid var(--gray-200);border-radius:12px;padding:20px;text-align:center;display:flex;flex-direction:column;gap:10px;transition:all 0.2s;background:white">
                    <div class="fw-800" style="font-size:1.5rem;color:var(--primary-600)">{{ number_format($plan->credits) }}</div>
                    <div class="text-xs text-muted mb-1">CREDITS</div>
                    <div class="fw-700" style="font-size:1.25rem">£{{ number_format($plan->price, 2) }}</div>
                    
                    @if($plan->badge_type)
                        <div class="badge badge--success" style="font-size:0.75rem;margin:5px auto">{{ $plan->badge_type }}</div>
                    @endif
                    
                    <div class="text-xs text-muted">{{ $plan->validity_days }} Days Validity</div>
                    
                    @if($plan->description)
                        <div class="text-xs mt-2" style="color:var(--gray-600)">{{ $plan->description }}</div>
                    @endif

                    <button class="btn btn--primary btn--sm mt-3 js-buy-plan" data-id="{{ $plan->id }}" style="width:100%">Buy Now</button>
                </div>
            @empty
                <div class="text-muted text-center p-4">No credit plans available.</div>
            @endforelse
        </div>
    </div>

    {{-- Credit History --}}
    <div class="card" style="padding: 0;">
        <div style="padding: 1.25rem; border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;">
            <div class="fw-800" style="color:var(--gray-900)">Request History</div>
            <form method="GET" action="{{ route('manufacturer.credits') }}" style="display: flex; gap: 0.5rem; align-items: center;">
                <select name="status" class="form-input" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; width: auto;">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
                <button type="submit" class="btn btn--primary btn--sm">Filter</button>
                @if(request('status'))
                    <a href="{{ route('manufacturer.credits') }}" class="btn btn--ghost btn--sm">Clear</a>
                @endif
            </form>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Credits</th>
                    <th>Status</th>
                    <th>Requested On</th>
                </tr>
            </thead>
            <tbody>
                @forelse($creditRequests as $req)
                <tr>
                    <td class="fw-700">{{ number_format($req->credits_added ?? $req->credits ?? 0) }}</td>
                    <td>
                        @if(($req->status === 'approved') || ($req->status === 'completed'))
                            <span class="badge badge--success">Completed</span>
                        @elseif($req->status === 'rejected' || $req->status === 'failed')
                            <span class="badge badge--danger">Failed</span>
                        @else
                            <span class="badge badge--warning">Pending</span>
                        @endif
                    </td>
                    <td class="text-sm text-muted">{{ $req->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center text-muted" style="padding: 2rem;">No requests found.</td>
                </tr>
                @endforelse
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $creditRequests->links('components.pagination') }}</div>
</div>
</div>
@endsection

@section('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripeKey = @json($stripePublishableKey ?? '');
    if (!stripeKey) {
        console.error('Stripe publishable key is missing.');
    }
    const stripe = stripeKey ? Stripe(stripeKey) : null;

    document.querySelectorAll('.js-buy-plan').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!stripe) {
                alert('Stripe is not configured. Please contact support.');
                return;
            }
            const planId = this.getAttribute('data-id');
            const originalText = this.innerText;
            
            this.disabled = true;
            this.innerText = 'Redirecting...';

            try {
                const res = await fetch('{{ route('manufacturer.credits.purchase') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ plan_id: planId })
                });
                
                const data = await res.json();
                
                if (data.id) {
                    stripe.redirectToCheckout({ sessionId: data.id });
                } else {
                    alert(data.error || 'Failed to initialize payment');
                    this.disabled = false;
                    this.innerText = originalText;
                }
            } catch (e) {
                alert('Connection error');
                this.disabled = false;
                this.innerText = originalText;
            }
        });
    });
</script>
@endsection

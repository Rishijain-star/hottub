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
    {{-- Request Credits Form --}}
    <div class="card">
        <div class="fw-800 mb-4" style="font-size:1.125rem;color:var(--gray-900)">Request New Credits</div>
        
        @if($paymentSettings && $paymentSettings->active_processor !== 'manual')
            <div class="packages-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(150px, 1fr));gap:15px;margin-bottom:20px">
                @foreach($packages as $package)
                    <div class="package-card" style="border:1px solid var(--gray-200);border-radius:12px;padding:15px;text-align:center;cursor:pointer;transition:all 0.2s" onclick="selectPackage(this, {{ $package->credits }}, {{ $package->price }})">
                        <div class="fw-800" style="font-size:1.25rem;color:var(--primary-600)">{{ $package->credits }}</div>
                        <div class="text-xs text-muted mb-2">CREDITS</div>
                        <div class="fw-700" style="font-size:1.1rem">£{{ number_format($package->price, 2) }}</div>
                        @if($package->savings_label)
                            <div class="badge badge--success" style="font-size:0.65rem;margin-top:5px">{{ $package->savings_label }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('manufacturer.credits.request') }}" method="POST" id="creditRequestForm">
            @csrf
            <div class="form-group">
                <label class="form-label">Number of Credits</label>
                <input type="number" id="credits_input" name="credits" class="form-input" required min="1" placeholder="e.g. 100">
                <p class="text-xs text-muted" style="margin-top: 0.25rem;">Enter the amount of credits you wish to add to your account.</p>
            </div>
            <div class="form-group">
                <label class="form-label">Total Amount (£)</label>
                <input type="number" id="amount_input" name="amount" class="form-input" step="0.01" min="0" placeholder="e.g. 300.00">
                <p class="text-xs text-muted" style="margin-top: 0.25rem;">Optional: Enter the payment amount agreed upon.</p>
            </div>

            @if($paymentSettings && $paymentSettings->active_processor !== 'manual')
                <div class="payment-method-selector mb-4">
                    <label class="form-label">Select Payment Method</label>
                    <div style="display:flex;gap:15px;margin-top:5px">
                        {{-- PayPal commented out for now --}}
                        {{-- @if($paymentSettings->active_processor === 'paypal' || ($paymentSettings->paypal_client_id && $paymentSettings->paypal_secret))
                            <label class="form-check" style="display:flex;align-items:center;gap:8px;cursor:pointer">
                                <input type="radio" name="payment_method" value="paypal" @checked($paymentSettings->active_processor === 'paypal')> PayPal
                            </label>
                        @endif --}}
                        @if($paymentSettings->active_processor === 'stripe' || ($paymentSettings->stripe_publishable_key && $paymentSettings->stripe_secret_key))
                            <label class="form-check" style="display:flex;align-items:center;gap:8px;cursor:pointer">
                                <input type="radio" name="payment_method" value="stripe" @checked($paymentSettings->active_processor === 'stripe')> Stripe
                            </label>
                        @endif
                    </div>
                </div>
            @endif

            <button type="submit" class="btn btn--primary" style="width: 100%; margin-top: 1rem;">
                {{ ($paymentSettings && $paymentSettings->active_processor !== 'manual') ? 'Pay & Submit Request' : 'Submit Request' }}
            </button>
        </form>
    </div>

    @section('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        function selectPackage(el, credits, price) {
            document.querySelectorAll('.package-card').forEach(c => {
                c.style.borderColor = 'var(--gray-200)';
                c.style.background = 'white';
            });
            el.style.borderColor = 'var(--primary-600)';
            el.style.background = 'var(--primary-50)';
            document.getElementById('credits_input').value = credits;
            document.getElementById('amount_input').value = price;
        }

        document.getElementById('creditRequestForm').addEventListener('submit', function(e) {
            const method = document.querySelector('input[name="payment_method"]:checked')?.value || 'manual';
            
            if (method === 'manual') return; // Let standard submission happen

            e.preventDefault();
            const form = this;
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerText;
            
            btn.disabled = true;
            btn.innerText = 'Processing Payment...';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.ok && data.url) {
                    // Open in a new small window (modal-like popup)
                    const width = 600;
                    const height = 800;
                    const left = (window.innerWidth / 2) - (width / 2);
                    const top = (window.innerHeight / 2) - (height / 2);
                    
                    const paymentWindow = window.open(data.url, 'Payment', `width=${width},height=${height},top=${top},left=${left}`);
                    
                    // Check if window is closed to refresh
                    const timer = setInterval(() => {
                        if (paymentWindow.closed) {
                            clearInterval(timer);
                            window.location.reload();
                        }
                    }, 1000);
                } else if (!data.ok) {
                    alert(data.msg || 'An error occurred');
                    btn.disabled = false;
                    btn.innerText = originalText;
                } else {
                    window.location.reload();
                }
            })
            .catch(err => {
                console.error(err);
                alert('Connection error. Please try again.');
                btn.disabled = false;
                btn.innerText = originalText;
            });
        });
    </script>
    @endsection

    {{-- Credit History --}}
    <div class="card" style="padding: 0;">
        <div style="padding: 1.25rem; border-bottom: 1px solid var(--gray-200);">
            <div class="fw-800" style="color:var(--gray-900)">Request History</div>
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
                    <td class="fw-700">{{ number_format($req->credits) }}</td>
                    <td>
                        @if($req->status === 'approved')
                            <span class="badge badge--success">Approved</span>
                        @elseif($req->status === 'rejected')
                            <span class="badge badge--danger">Rejected</span>
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
    </div>
</div>
@endsection

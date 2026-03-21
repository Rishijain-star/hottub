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
        <form action="{{ route('manufacturer.credits.request') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Number of Credits</label>
                <input type="number" name="credits" class="form-input" required min="1" placeholder="e.g. 100">
                <p class="text-xs text-muted" style="margin-top: 0.25rem;">Enter the amount of credits you wish to add to your account.</p>
            </div>
            <div class="form-group">
                <label class="form-label">Total Amount (£)</label>
                <input type="number" name="amount" class="form-input" step="0.01" min="0" placeholder="e.g. 300.00">
                <p class="text-xs text-muted" style="margin-top: 0.25rem;">Optional: Enter the payment amount agreed upon.</p>
            </div>
            <button type="submit" class="btn btn--primary" style="width: 100%; margin-top: 1rem;">Submit Request</button>
        </form>
    </div>

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

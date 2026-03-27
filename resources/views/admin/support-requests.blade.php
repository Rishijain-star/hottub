@extends('layouts.admin')
@section('title', 'Support Requests – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Support Requests</h1>
        <p class="panel-page-sub">Messages from paused or frozen customer/manufacturer/dealer accounts</p>
    </div>
</div>

<div class="card" style="padding:0;">
    <table class="table">
        <thead>
            <tr>
                <th>Sender</th>
                <th>Company</th>
                <th>Status</th>
                <th>Message</th>
                <th>Sent At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
            <tr>
                <td>
                    <div class="fw-700 text-dark">{{ $req->sender_name }}</div>
                    <div class="text-xs text-muted">{{ $req->sender_email }}</div>
                    <div class="badge badge--ghost" style="font-size:10px">{{ strtoupper($req->sender_role) }}</div>
                </td>
                <td>{{ $req->company_name ?: '—' }}</td>
                <td>
                    @if($req->sender_status === 'frozen')
                        <span class="badge badge--danger">Frozen</span>
                    @elseif($req->sender_status === 'paused')
                        <span class="badge badge--warning">Paused</span>
                    @else
                        <span class="badge">{{ ucfirst($req->sender_status) }}</span>
                    @endif
                </td>
                <td style="max-width:300px;">
                    <div class="text-sm" style="white-space: pre-wrap; line-height:1.4;">{{ $req->content }}</div>
                </td>
                <td>
                    <div class="text-xs">{{ $req->created_at->format('d M Y') }}</div>
                    <div class="text-xs text-muted">{{ $req->created_at->format('H:i') }}</div>
                </td>
                <td>
                    @if(($hasSupportStatusColumn ?? false) && (($req->support_status ?? 'pending') === 'pending'))
                        <div style="display:flex;gap:0.35rem;align-items:center;flex-wrap:wrap;">
                            <form method="POST" action="{{ route('admin.support-requests.approve', $req->id) }}">
                                @csrf
                                <button type="submit" class="btn btn--primary btn--sm">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.support-requests.reject', $req->id) }}">
                                @csrf
                                <button type="submit" class="btn btn--ghost btn--sm">Reject</button>
                            </form>
                        </div>
                    @elseif(($hasSupportStatusColumn ?? false) && (($req->support_status ?? null) === 'approved'))
                        <span class="badge badge--success">Approved</span>
                    @elseif(($hasSupportStatusColumn ?? false) && (($req->support_status ?? null) === 'rejected'))
                        <span class="badge badge--danger">Rejected</span>
                    @elseif($req->sender_role === 'manufacturer')
                        <a href="{{ route('admin.manufacturers') }}?search={{ $req->sender_email }}" class="btn btn--ghost btn--sm">Manage Account</a>
                    @elseif($req->sender_role === 'dealer')
                        <a href="{{ route('admin.dealers.index') }}?search={{ $req->sender_email }}" class="btn btn--ghost btn--sm">Manage Account</a>
                    @elseif($req->sender_role === 'user')
                        <a href="{{ route('admin.users.index') }}?search={{ $req->sender_email }}" class="btn btn--ghost btn--sm">Manage Account</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-5">No support requests found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:1rem">
        {{ $requests->links('components.pagination') }}
    </div>
</div>
@endsection

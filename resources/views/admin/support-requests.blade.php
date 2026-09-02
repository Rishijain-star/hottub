@extends('layouts.admin')
@section('title', __('panel.admin.nav.support_requests') . ' - ' . __('panel.admin_title'))
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.admin.support_requests.title') }}</h1>
        <p class="panel-page-sub">{{ __('panel.admin.support_requests.sub') }}</p>
    </div>
</div>

<div class="card" style="padding:0;">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('panel.admin.support_requests.sender') }}</th>
                <th>{{ __('panel.admin.common.company') }}</th>
                <th>{{ __('panel.admin.common.status') }}</th>
                <th>{{ __('panel.admin.common.message') }}</th>
                <th>{{ __('panel.admin.support_requests.sent_at') }}</th>
                <th>{{ __('panel.admin.common.actions') }}</th>
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
                <td>{{ $req->company_name ?: __('panel.admin.common.none') }}</td>
                <td>
                    @if($req->sender_status === 'frozen')
                        <span class="badge badge--danger">{{ __('panel.admin.dealers.frozen') }}</span>
                    @elseif($req->sender_status === 'paused')
                        <span class="badge badge--warning">{{ __('panel.admin.dealers.paused') }}</span>
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
                                <button type="submit" class="btn btn--primary btn--sm">{{ __('panel.admin.common.approve') }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.support-requests.reject', $req->id) }}">
                                @csrf
                                <button type="submit" class="btn btn--ghost btn--sm">{{ __('panel.admin.common.reject') }}</button>
                            </form>
                        </div>
                    @elseif(($hasSupportStatusColumn ?? false) && (($req->support_status ?? null) === 'approved'))
                        <span class="badge badge--success">{{ __('panel.admin.dealers.approved') }}</span>
                    @elseif(($hasSupportStatusColumn ?? false) && (($req->support_status ?? null) === 'rejected'))
                        <span class="badge badge--danger">{{ __('panel.status.rejected') }}</span>
                    @elseif($req->sender_role === 'manufacturer')
                        @if(auth()->user()?->isFullAdmin())
                        <a href="{{ route('admin.manufacturers') }}?search={{ $req->sender_email }}" class="btn btn--ghost btn--sm">{{ __('panel.admin.common.manage_account') }}</a>
                        @else
                        <span class="text-xs text-muted">{{ __('panel.admin.support_requests.primary_admin_only') }}</span>
                        @endif
                    @elseif($req->sender_role === 'dealer')
                        @if(auth()->user()?->isFullAdmin())
                        <a href="{{ route('admin.dealers.index') }}?search={{ $req->sender_email }}" class="btn btn--ghost btn--sm">{{ __('panel.admin.common.manage_account') }}</a>
                        @else
                        <span class="text-xs text-muted">{{ __('panel.admin.support_requests.primary_admin_only') }}</span>
                        @endif
                    @elseif($req->sender_role === 'user')
                        <a href="{{ route('admin.users.index') }}?search={{ $req->sender_email }}" class="btn btn--ghost btn--sm">{{ __('panel.admin.common.manage_account') }}</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-5">{{ __('panel.admin.support_requests.no_requests') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:1rem">
        {{ $requests->links('components.pagination') }}
    </div>
</div>
@endsection

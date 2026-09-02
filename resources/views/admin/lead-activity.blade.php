@extends('layouts.admin')
@section('title', __('panel.admin.pages.lead_activity.title') . ' – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.admin.pages.lead_activity.title') }}</h1>
        <p class="panel-page-sub">{{ $lead->name }} · {{ $lead->email }}</p>
    </div>
    <a href="{{ route('admin.leads') }}" class="btn">{{ __('panel.admin.pages.lead_activity.back_to_leads') }}</a>
</div>

<div class="card">
    <div class="fw-800 mb-4" style="font-size:1.125rem;color:var(--gray-900)">Activity Timeline</div>

    <div class="timeline">
        @forelse($activities as $act)
            <div class="timeline-item" style="display:flex;gap:1rem;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid #f3f4f6;">
                <div class="timeline-icon" style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:{{ $act->type === 'status_change' ? '#f5f3ff' : ($act->type === 'note' ? '#ecfdf5' : '#eff6ff') }};">
                    @if($act->type === 'status_change')
                        <svg width="16" height="16" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    @elseif($act->type === 'note')
                        <svg width="16" height="16" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    @else
                        <svg width="16" height="16" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    @endif
                </div>
                <div style="flex:1;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div>
                            <div class="text-sm fw-700 text-dark">{{ ucfirst(str_replace('_', ' ', $act->type)) }}</div>
                            @if($act->dealer)
                                <div class="text-xs text-muted">by {{ $act->dealer->name }}</div>
                            @endif
                        </div>
                        <div class="text-xs text-muted">{{ $act->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    <div class="text-sm" style="margin-top:.25rem;color:var(--gray-700);">
                        {!! nl2br(e($act->content)) !!}
                    </div>
                    @if($act->type === 'task')
                        <div style="display:flex;align-items:center;gap:.75rem;margin-top:.5rem;">
                            @if($act->due_date)
                                <div class="text-xs {{ $act->due_date->isPast() && !$act->is_completed ? 'text-danger' : 'text-muted' }}" style="display:flex;align-items:center;gap:4px;">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    Due {{ $act->due_date->format('M d, Y') }}
                                </div>
                            @endif
                            <div class="text-xs {{ $act->is_completed ? 'text-success' : 'text-muted' }}" style="font-weight: 700;">
                                {{ $act->is_completed ? 'Completed' : 'Pending' }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-sm text-muted text-center" style="padding:2rem 0;">No activity has been logged for this lead yet.</div>
        @endforelse
    </div>
</div>
@endsection

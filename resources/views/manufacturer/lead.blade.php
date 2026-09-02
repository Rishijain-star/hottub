@extends('layouts.manufacturer')
@section('title', __('panel.lead.title').' - '.__('panel.manufacturer_title'))
@section('content')
@php 
    $stages = ['new_lead','contacted','nurturing','site_visit','deposit','delivered','lost'];
    $purchase = \App\Models\LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', auth()->id())->where('buyer_role', 'manufacturer')->first();
    $isLost = ($purchase && $purchase->stage === 'Lost') || ($lead->status === 'converted' && $lead->assigned_dealer_id && $lead->assigned_dealer_id !== auth()->id());
    $canEditCrm = !$isLost;
        $checklistData = is_array($serviceChecklist->checklist_data ?? null) ? $serviceChecklist->checklist_data : [];
        $isChecklistLocked = !empty($serviceChecklist?->completed_at);
    
    if ($isLost) {
        $currentStage = 'lost';
    } elseif ($lead->assigned_dealer_id === auth()->id()) {
        $currentStage = \Illuminate\Support\Str::of($lead->stage ?: 'new_lead')->lower()->replace(' ', '_')->replace('-', '_')->value();
    } else {
        $currentStage = \Illuminate\Support\Str::of(($purchase && $purchase->stage) ? $purchase->stage : 'new_lead')->lower()->replace(' ', '_')->replace('-', '_')->value();
    }
@endphp
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.lead.title') }}</h1>
        <p class="panel-page-sub">{{ $lead->name }} · {{ $lead->email }}</p>
    </div>
    <a href="{{ route('manufacturer.leads') }}" class="btn">{{ __('panel.lead.back') }}</a>
</div>
<div class="crm-grid">
    {{-- Top Message Boxes --}}
    <div style="grid-column: 1 / -1; margin-bottom: 1.5rem;">
        @if($lead->status === 'converted' && $lead->assigned_dealer_id === auth()->id())
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:1.25rem; display:flex; align-items:center; gap:1rem;">
                <div style="width:40px; height:40px; background:#dcfce7; color:#166534; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <h3 style="font-size:1.05rem; font-weight:800; color:#166534; margin:0;">{{ __('panel.lead.congratulations') }}</h3>
                    <p style="color:#15803d; font-size:0.95rem; margin:0.25rem 0 0; font-weight:600;">{{ __('panel.lead.completed_success') }}</p>
                </div>
            </div>
        @endif

        @if($currentStage === 'lost')
            <div style="background:#fffbeb; border:1px solid #fef3c7; border-radius:12px; padding:1.25rem; display:flex; align-items:center; gap:1rem;">
                <div style="width:40px; height:40px; background:#fef3c7; color:#92400e; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 style="font-size:1.05rem; font-weight:800; color:#92400e; margin:0;">{{ __('panel.lead.lead_closed') }}</h3>
                    <p style="color:#b45309; font-size:0.95rem; margin:0.25rem 0 0; font-weight:600;">{{ __('panel.lead.lead_closed_message') }}</p>
                </div>
            </div>
        @endif

        @if(!$customerAccount && !empty($lead->email))
            <div style="margin-top:1rem;background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:1rem 1.25rem;">
                <h3 style="font-size:1rem; font-weight:800; color:#1d4ed8; margin:0;">{{ __('panel.lead.customer_account_reminder') }}</h3>
                <p style="color:#1e40af; font-size:0.92rem; margin:0.35rem 0 0;">{!! __('panel.lead.customer_account_reminder_text', ['email' => '<strong>'.$lead->email.'</strong>']) !!}</p>
            </div>
        @endif
    </div>

    {{-- Left side --}}
    <div class="crm-grid__left">
        <div class="card">
            <div class="form-group" style="margin-bottom:12px; position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <div class="text-sm text-muted">{{ __('panel.lead.progress') }}</div>
                    <button type="button" class="btn--icon-only" onclick="openActivityModal()" title="{{ __('panel.lead.user_activity') }}" style="background:none; border:none; color:var(--gray-400); cursor:pointer; transition:color 0.2s; padding:0; display:flex; align-items:center;" onmouseover="this.style.color='var(--primary-600)'" onmouseout="this.style.color='var(--gray-400)'">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <div style="background:#eef2f7;border-radius:999px;height:8px;overflow:hidden">
                    <div id="ldProgress" style="height:8px;background:#0ea5a3;width:0%"></div>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:12px">
                <div class="text-sm text-muted" style="margin-bottom:6px">{{ __('panel.common.stage') }}</div>
                <div id="ldStageBar" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                    @foreach($stages as $s)
                    <button class="badge js-stage {{ ($currentStage === $s) ? 'badge--success':'' }}" data-stage="{{ $s }}" style="cursor:default; pointer-events:none;">{{ __('panel.stages.'.$s, [], app()->getLocale()) !== 'panel.stages.'.$s ? __('panel.stages.'.$s) : ucfirst(str_replace('_',' ',$s)) }}</button>
                    @endforeach
                </div>
            </div>
            <div id="ldStageMsg" class="text-sm" style="margin-bottom:12px;color:#6b7280"></div>

            <div class="grid grid--2">
                <div class="form-group">
                    <label class="form-label">{{ __('panel.lead.name') }}</label>
                    <div class="text-sm">
                        @if(!$isLost)
                            {{ $lead->name }}
                        @else
                            <span style="color: #94a3b8; font-style: italic;">{{ __('panel.common.name_hidden') }}</span>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('panel.lead.email') }}</label>
                    <div class="text-sm">
                        @if(!$isLost)
                            {{ $lead->email }}
                        @else
                            <span style="color: #94a3b8; font-style: italic;">{{ __('panel.common.email_hidden') }}</span>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('panel.lead.phone') }}</label>
                    <div class="text-sm">
                        @if(!$isLost)
                            {{ $lead->phone }}
                        @else
                            <span style="color: #94a3b8; font-style: italic;">{{ __('panel.lead.phone_hidden') }}</span>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('panel.lead.postcode') }}</label>
                    <div class="text-sm">{{ $lead->postcode }}</div>
                </div>
                @if(!empty($lead->address))
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">{{ __('panel.lead.address') }}</label>
                    <div class="text-sm">{{ $lead->address }}</div>
                </div>
                @endif
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('panel.lead.message') }}</label>
                <div class="text-sm">
                    @if(!$isLost)
                        {{ $lead->message }}
                    @else
                        <span style="color: #94a3b8; font-style: italic;">{{ __('panel.lead.message_hidden') }}</span>
                    @endif
                </div>
            </div>
            <div id="siteVisitBox" style="display:{{ ($currentStage==='site_visit') ? '' : 'none' }};border:1px solid #e5e7eb;border-radius:12px;padding:14px;margin-bottom:12px">
                <div class="fw-700 text-dark" style="margin-bottom:8px">{{ __('panel.lead.site_visit_details') }}</div>
                <div class="form-group">
                    <label class="form-label">{{ __('panel.lead.site_visit_required') }}</label>
                    <div style="display:flex;gap:12px;margin-top:4px">
                        <label style="display:flex;align-items:center;gap:4px;cursor:pointer"><input type="radio" name="sv_required" value="Yes"> {{ __('panel.lead.yes') }}</label>
                        <label style="display:flex;align-items:center;gap:4px;cursor:pointer"><input type="radio" name="sv_required" value="No" checked> {{ __('panel.lead.no') }}</label>
                    </div>
                </div>
                <div id="svNotesBox" style="display:none;margin-top:10px">
                    <label class="form-label">{{ __('panel.lead.site_visit_notes') }}</label>
                    <textarea id="svNotes" class="form-input" rows="2" placeholder="{{ __('panel.lead.site_visit_notes_placeholder') }}"></textarea>
                </div>
            </div>
            <div id="depositBox" style="display:{{ ($currentStage==='deposit') ? '' : 'none' }};border:1px solid #e5e7eb;border-radius:12px;padding:14px;margin-bottom:12px">
                <div class="fw-700 text-dark" style="margin-bottom:8px">{{ __('panel.lead.deposit_confirmation') }}</div>
                @if($lead->deposit_confirmed)
                    <div style="color:#16a34a;font-weight:600;display:flex;align-items:center;gap:6px">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ __('panel.lead.deposit_confirmed') }}
                    </div>
                @else
                    <div style="color:#6b7280;font-style:italic">{{ __('panel.lead.waiting_customer_confirmation') }}</div>
                    <div class="text-xs text-muted" style="margin-top:4px">{{ __('panel.lead.request_sent_on', ['date' => $lead->deposit_requested_at ? $lead->deposit_requested_at->format('M d, Y H:i') : __('panel.overview.n_a')]) }}</div>
                @endif
            </div>
            <div class="modal-actions" style="justify-content:flex-start;margin-bottom:6px;display:{{ ($currentStage==='delivered') ? 'none' : 'flex' }}">
                <button id="btnNextStage" class="btn btn--primary btn--sm">{{ __('panel.lead.next_step') }}</button>
            </div>

            <div id="ldDeliveredSection" style="display:{{ ($currentStage==='delivered') ? '' : 'none' }};margin-top:10px">
                <div class="fw-700 text-dark" style="margin-bottom:6px">{{ __('panel.lead.delivery_details') }}</div>
                <form id="deliverForm">
                    <div class="grid grid--2">
                        @php
                            $purchaseDetails = optional($purchase)->delivery_details;
                            $delivery_details = is_array($lead->delivery_details) && !empty($lead->delivery_details)
                                ? $lead->delivery_details
                                : (is_array($purchaseDetails) ? $purchaseDetails : []);
                        @endphp
                        <div class="form-group">
                            <label class="form-label">{{ __('panel.lead.product_make') }}</label>
                            <input name="make" class="form-input" required value="{{ $delivery_details['make'] ?? '' }}" @if($lead->status === 'converted') readonly style="background-color: #f3f4f6;" @endif>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('panel.lead.product_model') }}</label>
                            <input name="model" class="form-input" required value="{{ $delivery_details['model'] ?? '' }}" @if($lead->status === 'converted') readonly style="background-color: #f3f4f6;" @endif>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('panel.lead.shell_colour') }}</label>
                            <input name="shell_colour" class="form-input" value="{{ $delivery_details['shell_colour'] ?? '' }}" @if($lead->status === 'converted') readonly style="background-color: #f3f4f6;" @endif>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('panel.lead.cabinet_colour') }}</label>
                            <input name="cabinet_colour" class="form-input" value="{{ $delivery_details['cabinet_colour'] ?? '' }}" @if($lead->status === 'converted') readonly style="background-color: #f3f4f6;" @endif>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('panel.lead.accessories') }}</label>
                            <input name="accessories" class="form-input" value="{{ $delivery_details['accessories'] ?? '' }}" @if($lead->status === 'converted') readonly style="background-color: #f3f4f6;" @endif>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('panel.lead.sale_price') }}</label>
                            <input name="sale_price" class="form-input" type="number" step="0.01" min="0" value="{{ $delivery_details['sale_price'] ?? '' }}" @if($lead->status === 'converted') readonly style="background-color: #f3f4f6;" @endif>
                        </div>
                        @if($lead->status !== 'converted')
                            <div class="form-group"><label class="form-label">{{ __('panel.lead.invoice_upload') }}</label><input name="invoice" class="form-input" type="file" accept=".pdf,.jpg,.jpeg,.png"></div>
                            <div class="form-group"><label class="form-label">{{ __('panel.lead.warranty_upload') }}</label><input name="warranty" class="form-input" type="file" accept=".pdf,.jpg,.jpeg,.png"></div>
                        @else
                            @php
                                $invoice_path = $lead->invoice_path ?: (optional($purchase)->invoice_path ?? '');
                                $warranty_path = $lead->warranty_path ?: (optional($purchase)->warranty_path ?? '');
                            @endphp
                            <div class="form-group">
                                <label class="form-label">{{ __('panel.lead.invoice') }}</label>
                                @if(!empty($invoice_path))
                                    <div class="text-sm"><a href="{{ \App\Support\PublicMedia::url($invoice_path) }}" target="_blank" class="text-teal fw-700">{{ __('panel.lead.view_uploaded_invoice') }}</a></div>
                                @else
                                    <div class="text-sm text-muted italic">{{ __('panel.lead.no_invoice_uploaded') }}</div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('panel.lead.warranty') }}</label>
                                @if(!empty($warranty_path))
                                    <div class="text-sm"><a href="{{ \App\Support\PublicMedia::url($warranty_path) }}" target="_blank" class="text-teal fw-700">{{ __('panel.lead.view_uploaded_warranty') }}</a></div>
                                @else
                                    <div class="text-sm text-muted italic">{{ __('panel.lead.no_warranty_uploaded') }}</div>
                                @endif
                            </div>
                        @endif
                    </div>
                    @if($lead->status !== 'converted')
                        <div class="modal-actions"><button type="submit" class="btn btn--primary btn--sm">{{ __('panel.lead.save_delivery_convert') }}</button></div>
                    @endif
                </form>
            </div>

            <div id="serviceChecklistBox" style="display:{{ ($currentStage==='delivered') ? '' : 'none' }};margin-top:20px;padding-top:20px;border-top:1px solid #e5e7eb">
                <div class="fw-700 text-dark" style="margin-bottom:12px">{{ __('panel.lead.delivery_checklist') }}</div>
                <form id="serviceChecklistForm">
                    <div class="grid grid--2">
                        <label style="display:flex;align-items:center;gap:8px;font-size:14px"><input type="checkbox" name="checklist[levelled]" value="1" @checked(!empty($checklistData['levelled'])) @disabled($isChecklistLocked)> {{ __('panel.lead.levelled_properly') }}</label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:14px"><input type="checkbox" name="checklist[electrical]" value="1" @checked(!empty($checklistData['electrical'])) @disabled($isChecklistLocked)> {{ __('panel.lead.electrical_connected') }}</label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:14px"><input type="checkbox" name="checklist[filled]" value="1" @checked(!empty($checklistData['filled'])) @disabled($isChecklistLocked)> {{ __('panel.lead.filled_tested') }}</label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:14px"><input type="checkbox" name="checklist[chemicals]" value="1" @checked(!empty($checklistData['chemicals'])) @disabled($isChecklistLocked)> {{ __('panel.lead.chemical_starter_kit') }}</label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:14px"><input type="checkbox" name="checklist[tutorial]" value="1" @checked(!empty($checklistData['tutorial'])) @disabled($isChecklistLocked)> {{ __('panel.lead.customer_tutorial_done') }}</label>
                    </div>
                    <div class="form-group" style="margin-top:10px">
                        <label class="form-label">{{ __('panel.lead.service_notes') }}</label>
                        <textarea name="notes" class="form-input" rows="2" @disabled($isChecklistLocked)>{{ $serviceChecklist->dealer_notes ?? '' }}</textarea>
                    </div>
                    @if($isChecklistLocked)
                        <div class="text-xs text-muted">{{ __('panel.lead.checklist_saved_locked', ['date' => $serviceChecklist->completed_at?->format('M d, Y H:i')]) }}</div>
                    @else
                        <div class="modal-actions"><button type="submit" class="btn btn--success btn--sm">{{ __('panel.lead.save_checklist') }}</button></div>
                    @endif
                </form>
            </div>
        </div>

        {{-- Notes Section --}}
        <div class="card" style="margin-top:1.5rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <div class="fw-800" style="font-size:1.125rem;color:var(--gray-900)">{{ __('panel.lead.notes') }}</div>
                @if($canEditCrm)
                    <button id="addNoteBtn" class="btn btn--primary btn--sm">{{ __('panel.lead.add_note') }}</button>
                @endif
            </div>
            <form id="addNoteForm" style="display:none;margin-bottom:1.5rem;background:#f9fafb;padding:1rem;border-radius:12px;border:1px solid #e5e7eb;">
                @csrf
                <div class="form-group">
                    <textarea name="content" class="form-input" rows="3" placeholder="{{ __('panel.lead.note_placeholder') }}" required></textarea>
                </div>
                <div class="modal-actions" style="justify-content:flex-end;">
                    <button type="button" id="cancelNoteBtn" class="btn btn--sm">{{ __('panel.common.cancel') }}</button>
                    <button type="submit" class="btn btn--primary btn--sm">{{ __('panel.lead.save_note') }}</button>
                </div>
            </form>
            <div id="notesTimeline">
                @php 
                    $notes = $lead->activities()->where('dealer_id', auth()->id())->where('type', 'note')->get();
                @endphp
                @forelse($notes as $note)
                    <div class="note-item" style="display:flex;gap:1rem;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid #f3f4f6;">
                        <div class="note-author-icon" style="width:32px;height:32px;border-radius:50%;background:#e0e7ff;color:#4338ca;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div style="flex:1;">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                <div class="text-sm fw-700 text-dark">{{ __('panel.lead.you') }}</div>
                                @php
                                    $timezone = auth()->user()->timezone ?? config('app.timezone');
                                @endphp
                                <div class="text-xs text-muted">{{ $note->created_at->setTimezone($timezone)->format('M d, Y, H:i A') }}</div>
                            </div>
                            <div class="text-sm" style="margin-top:.25rem;color:var(--gray-700);">
                                {!! nl2br(e($note->content)) !!}
                            </div>
                            @if($canEditCrm)
                                <div class="text-xs" style="margin-top:0.35rem;display:flex;gap:0.65rem;flex-wrap:wrap;">
                                    <button type="button" class="btn btn--link btn--sm js-edit-crm-activity" style="padding:0;min-height:auto;" data-type="note" data-id="{{ $note->id }}" data-json="{{ htmlspecialchars(json_encode(['content' => $note->content]), ENT_QUOTES, 'UTF-8') }}">{{ __('panel.lead.edit') }}</button>
                                    <button type="button" class="btn btn--link btn--sm js-delete-crm-activity text-danger" style="padding:0;min-height:auto;" data-type="note" data-id="{{ $note->id }}">{{ __('panel.lead.delete') }}</button>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-muted text-center" style="padding:2rem 0;">{{ __('panel.lead.no_notes') }}</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Right side --}}
    <div class="crm-grid__right">
        {{-- Tasks Panel --}}
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <div class="fw-800" style="font-size:1.125rem;color:var(--gray-900)">{{ __('panel.lead.tasks') }}</div>
                @if($canEditCrm)
                    <button id="addTaskBtn" class="btn btn--primary btn--sm">{{ __('panel.lead.add_task') }}</button>
                @endif
            </div>
            <div id="tasksList">
                @php
                    $tasks = $lead->activities()->where('dealer_id', auth()->id())->where('type', 'task')->orderBy('is_completed', 'asc')->orderBy('due_date', 'asc')->get();
                    $pendingTasks = $tasks->where('is_completed', false);
                    $completedTasks = $tasks->where('is_completed', true);
                @endphp
                <div id="pendingTasksContainer">
                    @forelse($pendingTasks as $index => $task)
                        <div class="task-item js-task-item {{ $index >= 5 ? 'd-none' : '' }}" style="display:{{ $index >= 5 ? 'none' : 'flex' }};align-items:flex-start;gap:1rem;padding:.75rem 0;border-bottom:1px solid #f3f4f6;">
                            <input type="checkbox" class="js-toggle-task" data-id="{{ $task->id }}" style="margin-top:4px;" @if(!$canEditCrm) disabled @endif>
                            <div style="flex:1;">
                                <div class="fw-700 text-dark">{{ $task->content }}</div>
                                @if($task->due_date)
                                    <div class="text-xs {{ $task->due_date->isPast() ? 'text-danger' : 'text-muted' }}">
                                        {{ __('panel.lead.due', ['date' => $task->due_date->diffForHumans()]) }}
                                    </div>
                                @endif
                                @if($canEditCrm)
                                    <div class="text-xs" style="margin-top:0.35rem;display:flex;gap:0.65rem;flex-wrap:wrap;">
                                        <button type="button" class="btn btn--link btn--sm js-edit-crm-activity" style="padding:0;min-height:auto;" data-type="task" data-id="{{ $task->id }}" data-json="{{ htmlspecialchars(json_encode(['content' => $task->content, 'due' => $task->due_date ? $task->due_date->format('Y-m-d') : '']), ENT_QUOTES, 'UTF-8') }}">{{ __('panel.lead.edit') }}</button>
                                        <button type="button" class="btn btn--link btn--sm js-delete-crm-activity text-danger" style="padding:0;min-height:auto;" data-type="task" data-id="{{ $task->id }}">{{ __('panel.lead.delete') }}</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-muted" style="padding:.5rem 0;">{{ __('panel.lead.no_pending_tasks') }}</div>
                    @endforelse
                </div>
                @if($pendingTasks->count() > 5)
                    <button id="btnSeeAllTasks" class="btn btn--link btn--sm" style="margin-top:0.5rem;padding-left:0;color:var(--teal);">{{ __('panel.lead.see_all_tasks') }}</button>
                @endif
            </div>
            <div id="completedTasksSection" style="margin-top:1.5rem;{{ $completedTasks->isEmpty() ? 'display:none;' : '' }}">
                <button id="toggleCompletedBtn" class="text-sm fw-700" style="color:var(--gray-600);background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;">
                    <span>{{ __('panel.lead.completed') }}</span>
                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
                </button>
                <div id="completedTasksList" style="display:none;margin-top:.5rem;">
                    @foreach($completedTasks as $task)
                        <div class="task-item--completed" style="display:flex;align-items:center;gap:1rem;padding:.5rem 0;text-decoration:line-through;color:var(--gray-500);">
                            <input type="checkbox" checked disabled style="cursor:default;">
                            <span>{{ $task->content }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Activity Timeline --}}
        <div class="card" style="margin-top:1.5rem;">
            <div class="fw-800 mb-2" style="font-size:1.125rem;color:var(--gray-900)">{{ __('panel.lead.activity_timeline') }}</div>
            <div id="activityTimeline" class="timeline">
                @php 
                    $activities = $lead->activities()->where(function($q) { 
                        $q->where('dealer_id', auth()->id())->orWhereNull('dealer_id');
                    })->orderBy('created_at', 'desc')->get();
                @endphp
                <div id="timelineContainer">
                    @forelse($activities as $index => $act)
                        <div class="timeline-item js-timeline-item {{ $act->type === 'task_completion' ? 'timeline-item--completed' : '' }} {{ $index >= 4 ? 'd-none' : '' }}" data-type="{{ $act->type }}" style="display:{{ $index >= 4 ? 'none' : 'flex' }};gap:1rem;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid #f3f4f6;">
                            <div class="timeline-icon" style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:{{ $act->type === 'status_change' ? '#f5f3ff' : ($act->type === 'note' ? '#ecfdf5' : '#eff6ff') }};">
                                @if($act->type === 'status_change')
                                    <svg width="16" height="16" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                @elseif($act->type === 'note')
                                    <svg width="16" height="16" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                @elseif($act->type === 'task_completion')
                                    <svg width="16" height="16" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                @else
                                    <svg width="16" height="16" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                @endif
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                    <div class="text-sm fw-700 text-dark">{{ $act->dealer_id ? __('panel.lead.you') : __('panel.lead.system') }}</div>
                                    @php
                                        $timezone = auth()->user()->timezone ?? config('app.timezone');
                                    @endphp
                                    <div class="text-xs text-muted">{{ $act->created_at->setTimezone($timezone)->format('M d, Y, H:i A') }}</div>
                                </div>
                                <div class="text-sm" style="margin-top:.25rem;color:var(--gray-700);">
                                    {!! nl2br(e($act->content)) !!}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-muted text-center" style="padding:2rem 0;">{{ __('panel.lead.no_activity') }}</div>
                    @endforelse
                </div>
                @if($activities->count() > 4)
                    <button id="btnSeeAllTimeline" class="btn btn--link btn--sm" style="margin-top:0.5rem;padding-left:0;color:var(--teal);">{{ __('panel.lead.see_all_activity') }}</button>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Customer Activity Modal --}}
<div id="activityModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:99999; align-items:center; justify-content:center; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
    <div class="card" style="width:90%; max-width:650px; max-height:85vh; overflow-y:auto; padding:35px; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); background:#fff; position: relative; margin: 0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
            <div style="display:flex; align-items:center; gap:12px">
                <div style="width:40px; height:40px; background:var(--primary-100); color:var(--primary-600); border-radius:12px; display:flex; align-items:center; justify-content:center;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <h2 style="margin:0; font-weight:800; font-size: 1.5rem; color: var(--gray-900); letter-spacing: -0.02em;">{{ __('panel.lead.user_activity_title') }}</h2>
            </div>
            <button onclick="closeActivityModal()" style="background:var(--gray-100); border:none; font-size:24px; cursor:pointer; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; transition: all 0.2s ease;" onmouseover="this.style.background='var(--gray-200)'" onmouseout="this.style.background='var(--gray-100)'">&times;</button>
        </div>
        
        @php $activities = $lead->customerActivities()->limit(30)->get(); @endphp
        @if($activities->count() > 0)
            <div class="activity-timeline">
                @foreach($activities as $act)
                    <div style="display:flex; gap:18px; margin-bottom:20px; padding-bottom:20px; border-bottom:1px solid #f8fafc">
                        <div style="width:42px; height:42px; background:#f8fafc; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; border: 1px solid #f1f5f9;">
                            @if($act->product_id)
                                <svg width="20" height="20" fill="var(--primary-500)" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                            @else
                                <svg width="20" height="20" fill="none" stroke="var(--gray-400)" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            @endif
                        </div>
                        <div style="flex-grow:1">
                            <div class="fw-800 text-sm" style="color:var(--gray-900); margin-bottom: 4px; font-size: 1.05rem;">{{ $act->page_name }}</div>
                            <div class="text-xs text-muted" style="display:flex; align-items:center; gap:6px; font-weight: 600;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ $act->created_at->diffForHumans() }}
                            </div>
                            <div class="text-xs mt-2" style="color:var(--primary-600); word-break: break-all; opacity: 0.8; font-family: monospace;">{{ $act->url }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align:center; padding:60px 20px;">
                <div style="font-size: 50px; margin-bottom: 20px; opacity: 0.2;">🔍</div>
                <div class="fw-800" style="color:var(--gray-900); font-size: 1.25rem;">{{ __('panel.lead.no_user_activity') }}</div>
                <p class="text-sm text-muted mt-2">{{ __('panel.lead.no_user_activity_sub') }}</p>
            </div>
        @endif
        
        <div class="mt-5" style="text-align: center;">
            <button class="btn btn--primary btn--pill" style="padding: 0.75rem 2.5rem;" onclick="closeActivityModal()">{{ __('panel.lead.close_activity_logs') }}</button>
        </div>
    </div>
</div>

<div id="addTaskModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">{{ __('panel.lead.add_task_title') }}</h2>
            <button id="closeTaskModal" class="modal-close">&times;</button>
        </div>
        <form id="addTaskForm">
            @csrf
            <div class="form-group">
                <label for="taskTitle" class="form-label">{{ __('panel.lead.task_title') }}</label>
                <input type="text" id="taskTitle" name="content" class="form-input" required placeholder="{{ __('panel.lead.task_title_placeholder') }}">
            </div>
            <div class="form-group">
                <label for="taskDueDate" class="form-label">{{ __('panel.lead.due_date') }}</label>
                <input type="date" id="taskDueDate" name="due_date" class="form-input" onclick="this.showPicker()">
            </div>
            <div class="modal-actions">
                <button type="button" id="cancelTaskModal" class="btn">{{ __('panel.common.cancel') }}</button>
                <button type="submit" class="btn btn--primary">{{ __('panel.lead.add_task') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Real-time check for deposit confirmation ---
    const leadId = {{ $lead->id }};
    const currentStage = '{{ $currentStage }}';
    const isAlreadyConfirmed = {{ $lead->deposit_confirmed ? 'true' : 'false' }};
    
    if (currentStage === 'deposit' && !isAlreadyConfirmed) {
        const interval = setInterval(async () => {
            try {
                const res = await fetch(`/manufacturer/leads/${leadId}/status`);
                const data = await res.json();
                if (data.deposit_confirmed) {
                    clearInterval(interval);
                    alert(@json(__('panel.lead.deposit_reload_alert')));
                    window.location.reload();
                }
            } catch (e) {
                // Stop on error
                clearInterval(interval);
            }
        }, 10000); // Check every 10 seconds
    }
    const STAGES = ['new_lead','contacted','nurturing','site_visit','deposit','delivered','lost'];
    const STAGE_API = {
        new_lead: 'New Lead',
        contacted: 'Contacted',
        nurturing: 'Nurturing',
        site_visit: 'Site Visit',
        deposit: 'Deposit',
        delivered: 'Delivered',
        lost: 'Lost',
    };
    const STAGE_MSG = {
        'new_lead': @json(__('panel.lead.stage_new_lead')),
        'contacted': @json(__('panel.lead.stage_contacted')),
        'nurturing': @json(__('panel.lead.stage_nurturing')),
        'site_visit': @json(__('panel.lead.stage_site_visit')),
        'deposit': @json(__('panel.lead.stage_deposit')),
        'delivered': @json(__('panel.lead.stage_delivered')),
        'lost': @json(__('panel.lead.stage_lost')),
    };
    function getCurrentStage() {
        const active = document.querySelector('.js-stage.badge--success');
        return active ? active.getAttribute('data-stage') : 'new_lead';
    }
    function setProgress(stage) {
        const idx = STAGES.indexOf(stage);
        const pct = (idx / (STAGES.length - 2)) * 100; // Delivered is 100%, Lost is also final
        const progressEl = document.getElementById('ldProgress');
        if (progressEl) {
            progressEl.style.width = (stage === 'lost' ? 100 : pct) + '%';
            progressEl.style.backgroundColor = (stage === 'lost' ? '#ef4444' : '#0ea5a3');
        }
        const stageMsgEl = document.getElementById('ldStageMsg');
        if (stageMsgEl) stageMsgEl.textContent = STAGE_MSG[stage] || '';
        const nextStageBtn = document.getElementById('btnNextStage');
        if (nextStageBtn) nextStageBtn.style.display = (stage === 'delivered' || stage === 'lost') ? 'none' : '';
        
        // Disable next button if at Deposit and not confirmed
        @if($currentStage === 'deposit' && !$lead->deposit_confirmed)
            if (nextStageBtn) nextStageBtn.disabled = true;
        @endif
    }
    setProgress(getCurrentStage());

    // --- Site Visit Required Radio Toggle ---
    document.querySelectorAll('input[name="sv_required"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const notesBox = document.getElementById('svNotesBox');
            if (notesBox) notesBox.style.display = (this.value === 'Yes') ? 'block' : 'none';
        });
    });

    // --- Next Step Button ---
    document.getElementById('btnNextStage')?.addEventListener('click', async function(){
        @if($currentStage === 'lost')
            alert(@json(__('panel.lead.closed_alert')));
            return;
        @endif
        const cur = getCurrentStage();
        const i = STAGES.indexOf(cur);
        const next = STAGES[Math.min(i+1, STAGES.length-1)];
        if (next === cur) return;

        let body = { stage: STAGE_API[next] || next };

        if (cur === 'site_visit') {
            const svRequired = document.querySelector('input[name="sv_required"]:checked')?.value;
            const svNotes = document.getElementById('svNotes')?.value;
            body.site_visit_required = svRequired;
            body.site_visit_notes = svNotes;
        }

        try{
            const res = await fetch('{{ route("manufacturer.leads.stage", $lead) }}', {
                method: 'POST',
                headers: { 'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json' },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            if (res.ok && data.ok){
                if (data.msg) {
                    alert(data.msg);
                }
                window.location.reload();
            } else {
                alert(data.msg || @json(__('panel.lead.unable_update_stage')));
            }
        }catch(err){ alert(@json(__('panel.common.network_error'))); }
    });

    // --- Customer Guidance Checkbox ---
    document.getElementById('cgConfirm')?.addEventListener('change', function() {
        const cur = getCurrentStage();
        const btn = document.getElementById('btnNextStage');
        if (btn) btn.disabled = (cur === 'sale_pending' && !this.checked);
    });

    // --- Notes ---
    document.getElementById('addNoteBtn')?.addEventListener('click', () => {
        document.getElementById('addNoteForm').style.display = 'block';
        document.getElementById('addNoteBtn').style.display = 'none';
    });
    document.getElementById('cancelNoteBtn')?.addEventListener('click', () => {
        document.getElementById('addNoteForm').style.display = 'none';
        document.getElementById('addNoteBtn').style.display = 'block';
    });
    document.getElementById('addNoteForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        const fd = new FormData(this);
        fd.set('type', 'note');
        try {
            const res = await fetch('{{ route("manufacturer.leads.activity", $lead) }}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: fd
            });
            const data = await res.json();
            if (res.ok && data.ok) {
                window.location.reload();
            } else {
                alert(data.msg || @json(__('panel.lead.unable_save_note')));
                btn.disabled = false;
            }
        } catch (err) { alert(@json(__('panel.common.network_error'))); btn.disabled = false; }
    });

    // --- Tasks ---
    document.getElementById('toggleCompletedBtn')?.addEventListener('click', function(){
        const list = document.getElementById('completedTasksList');
        const isOpen = list.style.display === 'block';
        list.style.display = isOpen ? 'none' : 'block';
        this.querySelector('svg').style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
    });
    document.addEventListener('change', async function(e) {
        if (e.target.classList.contains('js-toggle-task')) {
            const id = e.target.getAttribute('data-id');
            try {
                const res = await fetch('{{ route("manufacturer.activities.toggle", ":id") }}'.replace(':id', id), {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                if (res.ok && data.ok) {
                    window.location.reload(); // Simple reload to refresh state
                }
            } catch (err) { alert(@json(__('panel.common.network_error'))); }
        }
    });

    document.getElementById('btnSeeAllTasks')?.addEventListener('click', function() {
        const isShowingAll = this.textContent === 'See Less Tasks';
        if (isShowingAll) {
            document.querySelectorAll('.js-task-item').forEach((el, index) => {
                if (index >= 5) {
                    el.classList.add('d-none');
                    el.style.display = 'none';
                }
            });
            this.textContent = @json(__('panel.lead.see_all_tasks'));
        } else {
            document.querySelectorAll('.js-task-item.d-none').forEach(el => {
                el.classList.remove('d-none');
                el.style.display = 'flex';
            });
            this.textContent = @json(__('panel.lead.see_less_tasks'));
        }
    });

    document.getElementById('btnSeeAllTimeline')?.addEventListener('click', function() {
        const isShowingAll = this.textContent === 'See Less Activity';
        if (isShowingAll) {
            document.querySelectorAll('.js-timeline-item').forEach((el, index) => {
                if (index >= 4) {
                    el.classList.add('d-none');
                    el.style.display = 'none';
                }
            });
            this.textContent = @json(__('panel.lead.see_all_activity'));
        } else {
            document.querySelectorAll('.js-timeline-item.d-none').forEach(el => {
                el.classList.remove('d-none');
                el.style.display = 'flex';
            });
            this.textContent = @json(__('panel.lead.see_less_activity'));
        }
    });

    // --- Add Task Modal ---
    const addTaskModal = document.getElementById('addTaskModal');
    const addTaskBtn = document.getElementById('addTaskBtn');
    const closeTaskModal = document.getElementById('closeTaskModal');
    const cancelTaskModal = document.getElementById('cancelTaskModal');
    const addTaskForm = document.getElementById('addTaskForm');

    addTaskBtn?.addEventListener('click', () => {
        addTaskModal.style.display = 'block';
    });

    const hideTaskModal = () => {
        addTaskModal.style.display = 'none';
        addTaskForm.reset();
    };

    closeTaskModal?.addEventListener('click', hideTaskModal);
    cancelTaskModal?.addEventListener('click', hideTaskModal);
    window.addEventListener('click', (e) => {
        if (e.target === addTaskModal) hideTaskModal();
    });

    addTaskForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        const fd = new FormData(this);
        fd.set('type', 'task');
        try {
            const res = await fetch('{{ route("manufacturer.leads.activity", $lead) }}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: fd
            });
            const data = await res.json();
            if (res.ok && data.ok) {
                window.location.reload();
            } else {
                alert(data.msg || @json(__('panel.lead.unable_save_task')));
                btn.disabled = false;
            }
        } catch (err) { alert(@json(__('panel.common.network_error'))); btn.disabled = false; }
    });

    // --- Deliver Form ---
    document.getElementById('deliverForm')?.addEventListener('submit', async function(e){
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = @json(__('panel.lead.saving'));
        }
        const fd = new FormData(form);
        try{
            const res = await fetch('{{ route("manufacturer.leads.deliver", $lead) }}', {
                method: 'POST',
                headers: { 'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':'{{ csrf_token() }}' },
                body: fd
            });
            const data = await res.json();
            if (res.ok && data.ok){
                alert(@json(__('panel.lead.delivery_saved')));
                window.location.reload();
            } else {
                alert(data.msg || @json(__('panel.lead.unable_save_delivery')));
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = @json(__('panel.lead.save_delivery_convert'));
                }
            }
        }catch(err){
            alert(@json(__('panel.common.network_error')));
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = @json(__('panel.lead.save_delivery_convert'));
            }
        }
    });

    const checklistForm = document.getElementById('serviceChecklistForm');
    checklistForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        try {
            const res = await fetch('{{ route("manufacturer.leads.service-checklist", $lead) }}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: fd
            });
            const data = await res.json();
            if (res.ok && data.ok) {
                alert(@json(__('panel.lead.checklist_saved')));
                window.location.reload();
            } else {
                alert(data.msg || @json(__('panel.lead.unable_save_checklist')));
            }
        } catch (err) { alert(@json(__('panel.common.network_error'))); }
    });

    // --- Activity Modal Functions ---
    window.openActivityModal = function() {
        document.getElementById('activityModal').style.display = 'flex';
    };
    window.closeActivityModal = function() {
        document.getElementById('activityModal').style.display = 'none';
    };
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('activityModal');
        if (e.target === modal) {
            closeActivityModal();
        }
    });

    const crmActivityUrl = (id) => '{{ url('/manufacturer/leads/'.$lead->id.'/activities') }}/' + id;
    document.querySelectorAll('.js-edit-crm-activity').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const id = this.getAttribute('data-id');
            const type = this.getAttribute('data-type');
            let payload = {};
            try { payload = JSON.parse(this.getAttribute('data-json') || '{}'); } catch (e) {}
            if (type === 'task') {
                const content = window.prompt(@json(__('panel.lead.task')), payload.content || '');
                if (content === null) return;
                const dueInput = window.prompt(@json(__('panel.lead.due_date_prompt')), payload.due || '');
                if (dueInput === null) return;
                const res = await fetch(crmActivityUrl(id), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ content: content, due_date: dueInput.trim() || null })
                });
                const data = await res.json().catch(function () { return {}; });
                if (res.ok && data.ok) window.location.reload(); else alert(data.msg || @json(__('panel.lead.could_not_update')));
            } else {
                const content = window.prompt(@json(__('panel.lead.note')), payload.content || '');
                if (content === null) return;
                const res = await fetch(crmActivityUrl(id), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ content: content })
                });
                const data = await res.json().catch(function () { return {}; });
                if (res.ok && data.ok) window.location.reload(); else alert(data.msg || @json(__('panel.lead.could_not_update')));
            }
        });
    });
    document.querySelectorAll('.js-delete-crm-activity').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            if (!window.confirm(@json(__('panel.lead.delete_item_confirm')))) return;
            const id = this.getAttribute('data-id');
            const res = await fetch(crmActivityUrl(id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await res.json().catch(function () { return {}; });
            if (res.ok && data.ok) window.location.reload(); else alert(data.msg || @json(__('panel.lead.could_not_delete')));
        });
    });
});
</script>
@endsection

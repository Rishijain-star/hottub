@extends('layouts.manufacturer')
@section('title', 'Lead – Manufacturer Panel')
@section('content')
@php 
    $stages = ['New Lead','Contacted','Nurturing','Site Visit','Deposit','Delivered','Lost']; 
    $purchase = \App\Models\LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', auth()->id())->where('buyer_role', 'manufacturer')->first();
    $isLost = ($purchase && $purchase->stage === 'Lost') || ($lead->status === 'converted' && $lead->assigned_dealer_id && $lead->assigned_dealer_id !== auth()->id());
    $canEditCrm = !$isLost;
        $checklistData = is_array($serviceChecklist->checklist_data ?? null) ? $serviceChecklist->checklist_data : [];
        $isChecklistLocked = !empty($serviceChecklist?->completed_at);
    
    if ($isLost) {
        $currentStage = 'Lost';
    } elseif ($lead->assigned_dealer_id === auth()->id()) {
        $currentStage = $lead->stage ?: 'New Lead';
    } else {
        $currentStage = ($purchase && $purchase->stage) ? $purchase->stage : 'New Lead';
    }
@endphp
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Lead</h1>
        <p class="panel-page-sub">{{ $lead->name }} · {{ $lead->email }}</p>
    </div>
    <a href="{{ route('manufacturer.leads') }}" class="btn">Back</a>
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
                    <h3 style="font-size:1.05rem; font-weight:800; color:#166534; margin:0;">Congratulations!</h3>
                    <p style="color:#15803d; font-size:0.95rem; margin:0.25rem 0 0; font-weight:600;">You have successfully completed this lead and secured the customer.</p>
                </div>
            </div>
        @endif

        @if($currentStage === 'Lost')
            <div style="background:#fffbeb; border:1px solid #fef3c7; border-radius:12px; padding:1.25rem; display:flex; align-items:center; gap:1rem;">
                <div style="width:40px; height:40px; background:#fef3c7; color:#92400e; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 style="font-size:1.05rem; font-weight:800; color:#92400e; margin:0;">Lead Closed</h3>
                    <p style="color:#b45309; font-size:0.95rem; margin:0.25rem 0 0; font-weight:600;">This lead has been completed by another dealer. Don’t worry — new opportunities are on the way. Keep going and stay focused! 💪✨</p>
                </div>
            </div>
        @endif

        @if(!$customerAccount && !empty($lead->email))
            <div style="margin-top:1rem;background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:1rem 1.25rem;">
                <h3 style="font-size:1rem; font-weight:800; color:#1d4ed8; margin:0;">Customer account reminder</h3>
                <p style="color:#1e40af; font-size:0.92rem; margin:0.35rem 0 0;">Ask the customer to create an account using <strong>{{ $lead->email }}</strong> so chat, reminders, and aftercare updates stay synced.</p>
            </div>
        @endif
    </div>

    {{-- Left side --}}
    <div class="crm-grid__left">
        <div class="card">
            <div class="form-group" style="margin-bottom:12px; position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <div class="text-sm text-muted">Progress</div>
                    <button type="button" class="btn--icon-only" onclick="openActivityModal()" title="User Activity" style="background:none; border:none; color:var(--gray-400); cursor:pointer; transition:color 0.2s; padding:0; display:flex; align-items:center;" onmouseover="this.style.color='var(--primary-600)'" onmouseout="this.style.color='var(--gray-400)'">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <div style="background:#eef2f7;border-radius:999px;height:8px;overflow:hidden">
                    <div id="ldProgress" style="height:8px;background:#0ea5a3;width:0%"></div>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:12px">
                <div class="text-sm text-muted" style="margin-bottom:6px">Stage</div>
                <div id="ldStageBar" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                    @foreach($stages as $s)
                    <button class="badge js-stage {{ ($currentStage === $s) ? 'badge--success':'' }}" data-stage="{{ $s }}" style="cursor:default; pointer-events:none;">{{ $s }}</button>
                    @endforeach
                </div>
            </div>
            <div id="ldStageMsg" class="text-sm" style="margin-bottom:12px;color:#6b7280"></div>

            <div class="grid grid--2">
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <div class="text-sm">
                        @if(!$isLost)
                            {{ $lead->name }}
                        @else
                            <span style="color: #94a3b8; font-style: italic;">Name Hidden</span>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="text-sm">
                        @if(!$isLost)
                            {{ $lead->email }}
                        @else
                            <span style="color: #94a3b8; font-style: italic;">Email Hidden</span>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <div class="text-sm">
                        @if(!$isLost)
                            {{ $lead->phone }}
                        @else
                            <span style="color: #94a3b8; font-style: italic;">Phone Hidden</span>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Postcode</label>
                    <div class="text-sm">{{ $lead->postcode }}</div>
                </div>
                @if(!empty($lead->address))
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Address</label>
                    <div class="text-sm">{{ $lead->address }}</div>
                </div>
                @endif
            </div>
            <div class="form-group">
                <label class="form-label">Message</label>
                <div class="text-sm">
                    @if(!$isLost)
                        {{ $lead->message }}
                    @else
                        <span style="color: #94a3b8; font-style: italic;">Message Hidden</span>
                    @endif
                </div>
            </div>
            <div id="siteVisitBox" style="display:{{ ($currentStage==='Site Visit') ? '' : 'none' }};border:1px solid #e5e7eb;border-radius:12px;padding:14px;margin-bottom:12px">
                <div class="fw-700 text-dark" style="margin-bottom:8px">Site Visit Details</div>
                <div class="form-group">
                    <label class="form-label">Site Visit Required?</label>
                    <div style="display:flex;gap:12px;margin-top:4px">
                        <label style="display:flex;align-items:center;gap:4px;cursor:pointer"><input type="radio" name="sv_required" value="Yes"> Yes</label>
                        <label style="display:flex;align-items:center;gap:4px;cursor:pointer"><input type="radio" name="sv_required" value="No" checked> No</label>
                    </div>
                </div>
                <div id="svNotesBox" style="display:none;margin-top:10px">
                    <label class="form-label">Site Visit Notes</label>
                    <textarea id="svNotes" class="form-input" rows="2" placeholder="Enter details about the site visit..."></textarea>
                </div>
            </div>
            <div id="depositBox" style="display:{{ ($currentStage==='Deposit') ? '' : 'none' }};border:1px solid #e5e7eb;border-radius:12px;padding:14px;margin-bottom:12px">
                <div class="fw-700 text-dark" style="margin-bottom:8px">Deposit Confirmation</div>
                @if($lead->deposit_confirmed)
                    <div style="color:#16a34a;font-weight:600;display:flex;align-items:center;gap:6px">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Customer has confirmed the deposit.
                    </div>
                @else
                    <div style="color:#6b7280;font-style:italic">Waiting for customer confirmation...</div>
                    <div class="text-xs text-muted" style="margin-top:4px">A request was sent on {{ $lead->deposit_requested_at ? $lead->deposit_requested_at->format('M d, Y H:i') : 'N/A' }}</div>
                @endif
            </div>
            <div class="modal-actions" style="justify-content:flex-start;margin-bottom:6px;display:{{ ($currentStage==='Delivered') ? 'none' : 'flex' }}">
                <button id="btnNextStage" class="btn btn--primary btn--sm">Next Step</button>
            </div>

            <div id="ldDeliveredSection" style="display:{{ ($currentStage==='Delivered') ? '' : 'none' }};margin-top:10px">
                <div class="fw-700 text-dark" style="margin-bottom:6px">Delivery Details</div>
                <form id="deliverForm">
                    <div class="grid grid--2">
                        @php
                            $purchaseDetails = optional($purchase)->delivery_details;
                            $delivery_details = is_array($lead->delivery_details) && !empty($lead->delivery_details)
                                ? $lead->delivery_details
                                : (is_array($purchaseDetails) ? $purchaseDetails : []);
                        @endphp
                        <div class="form-group">
                            <label class="form-label">Product Make *</label>
                            <input name="make" class="form-input" required value="{{ $delivery_details['make'] ?? '' }}" @if($lead->status === 'converted') readonly style="background-color: #f3f4f6;" @endif>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Product Model *</label>
                            <input name="model" class="form-input" required value="{{ $delivery_details['model'] ?? '' }}" @if($lead->status === 'converted') readonly style="background-color: #f3f4f6;" @endif>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Shell Colour</label>
                            <input name="shell_colour" class="form-input" value="{{ $delivery_details['shell_colour'] ?? '' }}" @if($lead->status === 'converted') readonly style="background-color: #f3f4f6;" @endif>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cabinet Colour</label>
                            <input name="cabinet_colour" class="form-input" value="{{ $delivery_details['cabinet_colour'] ?? '' }}" @if($lead->status === 'converted') readonly style="background-color: #f3f4f6;" @endif>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Accessories</label>
                            <input name="accessories" class="form-input" value="{{ $delivery_details['accessories'] ?? '' }}" @if($lead->status === 'converted') readonly style="background-color: #f3f4f6;" @endif>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sale Price</label>
                            <input name="sale_price" class="form-input" type="number" step="0.01" min="0" value="{{ $delivery_details['sale_price'] ?? '' }}" @if($lead->status === 'converted') readonly style="background-color: #f3f4f6;" @endif>
                        </div>
                        @if($lead->status !== 'converted')
                            <div class="form-group"><label class="form-label">Invoice Upload</label><input name="invoice" class="form-input" type="file" accept=".pdf,.jpg,.jpeg,.png"></div>
                            <div class="form-group"><label class="form-label">Warranty Upload</label><input name="warranty" class="form-input" type="file" accept=".pdf,.jpg,.jpeg,.png"></div>
                        @else
                            @php
                                $invoice_path = $lead->invoice_path ?: (optional($purchase)->invoice_path ?? '');
                                $warranty_path = $lead->warranty_path ?: (optional($purchase)->warranty_path ?? '');
                            @endphp
                            <div class="form-group">
                                <label class="form-label">Invoice</label>
                                @if(!empty($invoice_path))
                                    <div class="text-sm"><a href="{{ \App\Support\PublicMedia::url($invoice_path) }}" target="_blank" class="text-teal fw-700">View Uploaded Invoice</a></div>
                                @else
                                    <div class="text-sm text-muted italic">No invoice uploaded</div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label class="form-label">Warranty</label>
                                @if(!empty($warranty_path))
                                    <div class="text-sm"><a href="{{ \App\Support\PublicMedia::url($warranty_path) }}" target="_blank" class="text-teal fw-700">View Uploaded Warranty</a></div>
                                @else
                                    <div class="text-sm text-muted italic">No warranty uploaded</div>
                                @endif
                            </div>
                        @endif
                    </div>
                    @if($lead->status !== 'converted')
                        <div class="modal-actions"><button type="submit" class="btn btn--primary btn--sm">Save Delivery & Convert</button></div>
                    @endif
                </form>
            </div>

            <div id="serviceChecklistBox" style="display:{{ ($currentStage==='Delivered') ? '' : 'none' }};margin-top:20px;padding-top:20px;border-top:1px solid #e5e7eb">
                <div class="fw-700 text-dark" style="margin-bottom:12px">Delivery check list</div>
                <form id="serviceChecklistForm">
                    <div class="grid grid--2">
                        <label style="display:flex;align-items:center;gap:8px;font-size:14px"><input type="checkbox" name="checklist[levelled]" value="1" @checked(!empty($checklistData['levelled'])) @disabled($isChecklistLocked)> Levelled properly</label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:14px"><input type="checkbox" name="checklist[electrical]" value="1" @checked(!empty($checklistData['electrical'])) @disabled($isChecklistLocked)> Electrical connected</label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:14px"><input type="checkbox" name="checklist[filled]" value="1" @checked(!empty($checklistData['filled'])) @disabled($isChecklistLocked)> Filled & Tested</label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:14px"><input type="checkbox" name="checklist[chemicals]" value="1" @checked(!empty($checklistData['chemicals'])) @disabled($isChecklistLocked)> Chemical starter kit</label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:14px"><input type="checkbox" name="checklist[tutorial]" value="1" @checked(!empty($checklistData['tutorial'])) @disabled($isChecklistLocked)> Customer tutorial done</label>
                    </div>
                    <div class="form-group" style="margin-top:10px">
                        <label class="form-label">Service Notes</label>
                        <textarea name="notes" class="form-input" rows="2" @disabled($isChecklistLocked)>{{ $serviceChecklist->dealer_notes ?? '' }}</textarea>
                    </div>
                    @if($isChecklistLocked)
                        <div class="text-xs text-muted">Checklist saved on {{ $serviceChecklist->completed_at?->format('M d, Y H:i') }} and is now locked.</div>
                    @else
                        <div class="modal-actions"><button type="submit" class="btn btn--success btn--sm">Save Checklist</button></div>
                    @endif
                </form>
            </div>
        </div>

        {{-- Notes Section --}}
        <div class="card" style="margin-top:1.5rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <div class="fw-800" style="font-size:1.125rem;color:var(--gray-900)">Notes</div>
                @if($canEditCrm)
                    <button id="addNoteBtn" class="btn btn--primary btn--sm">+ Add Note</button>
                @endif
            </div>
            <form id="addNoteForm" style="display:none;margin-bottom:1.5rem;background:#f9fafb;padding:1rem;border-radius:12px;border:1px solid #e5e7eb;">
                @csrf
                <div class="form-group">
                    <textarea name="content" class="form-input" rows="3" placeholder="Write your note here..." required></textarea>
                </div>
                <div class="modal-actions" style="justify-content:flex-end;">
                    <button type="button" id="cancelNoteBtn" class="btn btn--sm">Cancel</button>
                    <button type="submit" class="btn btn--primary btn--sm">Save Note</button>
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
                                <div class="text-sm fw-700 text-dark">You</div>
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
                                    <button type="button" class="btn btn--link btn--sm js-edit-crm-activity" style="padding:0;min-height:auto;" data-type="note" data-id="{{ $note->id }}" data-json="{{ htmlspecialchars(json_encode(['content' => $note->content]), ENT_QUOTES, 'UTF-8') }}">Edit</button>
                                    <button type="button" class="btn btn--link btn--sm js-delete-crm-activity text-danger" style="padding:0;min-height:auto;" data-type="note" data-id="{{ $note->id }}">Delete</button>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-muted text-center" style="padding:2rem 0;">No notes added yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Right side --}}
    <div class="crm-grid__right">
        {{-- Tasks Panel --}}
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <div class="fw-800" style="font-size:1.125rem;color:var(--gray-900)">Tasks</div>
                @if($canEditCrm)
                    <button id="addTaskBtn" class="btn btn--primary btn--sm">+ Add Task</button>
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
                                        Due {{ $task->due_date->diffForHumans() }}
                                    </div>
                                @endif
                                @if($canEditCrm)
                                    <div class="text-xs" style="margin-top:0.35rem;display:flex;gap:0.65rem;flex-wrap:wrap;">
                                        <button type="button" class="btn btn--link btn--sm js-edit-crm-activity" style="padding:0;min-height:auto;" data-type="task" data-id="{{ $task->id }}" data-json="{{ htmlspecialchars(json_encode(['content' => $task->content, 'due' => $task->due_date ? $task->due_date->format('Y-m-d') : '']), ENT_QUOTES, 'UTF-8') }}">Edit</button>
                                        <button type="button" class="btn btn--link btn--sm js-delete-crm-activity text-danger" style="padding:0;min-height:auto;" data-type="task" data-id="{{ $task->id }}">Delete</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-muted" style="padding:.5rem 0;">No pending tasks.</div>
                    @endforelse
                </div>
                @if($pendingTasks->count() > 5)
                    <button id="btnSeeAllTasks" class="btn btn--link btn--sm" style="margin-top:0.5rem;padding-left:0;color:var(--teal);">See All Tasks</button>
                @endif
            </div>
            <div id="completedTasksSection" style="margin-top:1.5rem;{{ $completedTasks->isEmpty() ? 'display:none;' : '' }}">
                <button id="toggleCompletedBtn" class="text-sm fw-700" style="color:var(--gray-600);background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;">
                    <span>Completed</span>
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
            <div class="fw-800 mb-2" style="font-size:1.125rem;color:var(--gray-900)">Activity Timeline</div>
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
                                    <div class="text-sm fw-700 text-dark">{{ $act->dealer_id ? 'You' : 'System' }}</div>
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
                        <div class="text-sm text-muted text-center" style="padding:2rem 0;">No activity logged yet.</div>
                    @endforelse
                </div>
                @if($activities->count() > 4)
                    <button id="btnSeeAllTimeline" class="btn btn--link btn--sm" style="margin-top:0.5rem;padding-left:0;color:var(--teal);">See All Activity</button>
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
                <h2 style="margin:0; font-weight:800; font-size: 1.5rem; color: var(--gray-900); letter-spacing: -0.02em;">User Activity</h2>
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
                <div class="fw-800" style="color:var(--gray-900); font-size: 1.25rem;">No activity found</div>
                <p class="text-sm text-muted mt-2">The customer hasn't browsed any tracked pages yet.</p>
            </div>
        @endif
        
        <div class="mt-5" style="text-align: center;">
            <button class="btn btn--primary btn--pill" style="padding: 0.75rem 2.5rem;" onclick="closeActivityModal()">Close Activity Logs</button>
        </div>
    </div>
</div>

<div id="addTaskModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Add Task</h2>
            <button id="closeTaskModal" class="modal-close">&times;</button>
        </div>
        <form id="addTaskForm">
            @csrf
            <div class="form-group">
                <label for="taskTitle" class="form-label">Task Title</label>
                <input type="text" id="taskTitle" name="content" class="form-input" required placeholder="e.g. Call Customer">
            </div>
            <div class="form-group">
                <label for="taskDueDate" class="form-label">Due Date</label>
                <input type="date" id="taskDueDate" name="due_date" class="form-input" onclick="this.showPicker()">
            </div>
            <div class="modal-actions">
                <button type="button" id="cancelTaskModal" class="btn">Cancel</button>
                <button type="submit" class="btn btn--primary">Save Task</button>
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
    
    if (currentStage === 'Deposit' && !isAlreadyConfirmed) {
        const interval = setInterval(async () => {
            try {
                const res = await fetch(`/manufacturer/leads/${leadId}/status`);
                const data = await res.json();
                if (data.deposit_confirmed) {
                    clearInterval(interval);
                    alert('Customer has confirmed the deposit. The page will now reload to reflect the changes.');
                    window.location.reload();
                }
            } catch (e) {
                // Stop on error
                clearInterval(interval);
            }
        }, 10000); // Check every 10 seconds
    }
    const STAGES = ['New Lead','Contacted','Nurturing','Site Visit','Deposit','Delivered','Lost'];
    const STAGE_MSG = {
        'New Lead': 'A new lead is assigned. Reach out as soon as possible.',
        'Contacted': 'You have contacted the lead. Best of luck!',
        'Nurturing': 'Continue nurturing with helpful info and follow-ups.',
        'Site Visit': 'Site visit scheduled — confirm time and address.',
        'Deposit': 'Waiting for customer deposit confirmation.',
        'Delivered': 'Delivery complete. Warranty registered and customer onboarded.',
        'Lost': 'Lead marked as lost. Record remains for history.',
    };
    function getCurrentStage() {
        const active = document.querySelector('.js-stage.badge--success');
        return active ? active.getAttribute('data-stage') : 'New Lead';
    }
    function setProgress(stage) {
        const idx = STAGES.indexOf(stage);
        const pct = (idx / (STAGES.length - 2)) * 100; // Delivered is 100%, Lost is also final
        const progressEl = document.getElementById('ldProgress');
        if (progressEl) {
            progressEl.style.width = (stage === 'Lost' ? 100 : pct) + '%';
            progressEl.style.backgroundColor = (stage === 'Lost' ? '#ef4444' : '#0ea5a3');
        }
        const stageMsgEl = document.getElementById('ldStageMsg');
        if (stageMsgEl) stageMsgEl.textContent = STAGE_MSG[stage] || '';
        const nextStageBtn = document.getElementById('btnNextStage');
        if (nextStageBtn) nextStageBtn.style.display = (stage === 'Delivered' || stage === 'Lost') ? 'none' : '';
        
        // Disable next button if at Deposit and not confirmed
        @if($currentStage === 'Deposit' && !$lead->deposit_confirmed)
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
        @if($currentStage === 'Lost')
            alert('This lead has already been closed.');
            return;
        @endif
        const cur = getCurrentStage();
        const i = STAGES.indexOf(cur);
        const next = STAGES[Math.min(i+1, STAGES.length-1)];
        if (next === cur) return;

        let body = { stage: next };

        if (cur === 'Site Visit') {
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
                alert(data.msg || 'Unable to update stage');
            }
        }catch(err){ alert('Network error'); }
    });

    // --- Customer Guidance Checkbox ---
    document.getElementById('cgConfirm')?.addEventListener('change', function() {
        const cur = getCurrentStage();
        const btn = document.getElementById('btnNextStage');
        if (btn) btn.disabled = (cur === 'Sale Pending' && !this.checked);
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
                alert(data.msg || 'Unable to save note');
                btn.disabled = false;
            }
        } catch (err) { alert('Network error'); btn.disabled = false; }
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
            } catch (err) { alert('Network error'); }
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
            this.textContent = 'See All Tasks';
        } else {
            document.querySelectorAll('.js-task-item.d-none').forEach(el => {
                el.classList.remove('d-none');
                el.style.display = 'flex';
            });
            this.textContent = 'See Less Tasks';
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
            this.textContent = 'See All Activity';
        } else {
            document.querySelectorAll('.js-timeline-item.d-none').forEach(el => {
                el.classList.remove('d-none');
                el.style.display = 'flex';
            });
            this.textContent = 'See Less Activity';
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
                alert(data.msg || 'Unable to save task');
                btn.disabled = false;
            }
        } catch (err) { alert('Network error'); btn.disabled = false; }
    });

    // --- Deliver Form ---
    document.getElementById('deliverForm')?.addEventListener('submit', async function(e){
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
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
                alert('Delivery saved. Lead converted.');
                window.location.reload();
            } else {
                alert(data.msg || 'Unable to save delivery');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Save Delivery & Convert';
                }
            }
        }catch(err){
            alert('Network error');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save Delivery & Convert';
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
                alert('Service checklist saved successfully.');
                window.location.reload();
            } else {
                alert(data.msg || 'Unable to save checklist.');
            }
        } catch (err) { alert('Network error'); }
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
                const content = window.prompt('Task', payload.content || '');
                if (content === null) return;
                const dueInput = window.prompt('Due date (YYYY-MM-DD, optional)', payload.due || '');
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
                if (res.ok && data.ok) window.location.reload(); else alert(data.msg || 'Could not update');
            } else {
                const content = window.prompt('Note', payload.content || '');
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
                if (res.ok && data.ok) window.location.reload(); else alert(data.msg || 'Could not update');
            }
        });
    });
    document.querySelectorAll('.js-delete-crm-activity').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            if (!window.confirm('Delete this item?')) return;
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
            if (res.ok && data.ok) window.location.reload(); else alert(data.msg || 'Could not delete');
        });
    });
});
</script>
@endsection

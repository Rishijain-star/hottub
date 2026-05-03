@extends('layouts.admin')
@section('title', 'Dealer Management – Admin Panel')

@section('styles') @endsection

@section('content')

{{-- Flash Messages — .alert .alert--success / .alert--danger from global.css --}}
@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert--danger">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert--danger">{{ $errors->first() }}</div>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const el = document.getElementById('createDealerModal');
            if (el) el.classList.add('active');
        });
    </script>
@endif

{{-- Page Header — .panel-page-header / .panel-page-title / .panel-page-sub from panel.css --}}
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Dealer Management</h1>
        <p class="panel-page-sub">Approve dealers, manage credits, and edit all profile information</p>
    </div>
    <button type="button" id="btnOpenCreateDealer" class="btn btn--primary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
        </svg>
        Create Dealer
    </button>
</div>

{{-- ─── FILTERS ─────────────────────────────────────────────────── --}}
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.dealers.index') }}" class="panel-filter-form panel-filter-form--3">
        <div class="form-group mb-0">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Name, Email, or Company..." value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Status</label>
            <select name="status" class="form-input">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Revoked</option>
                <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                <option value="frozen" {{ request('status') === 'frozen' ? 'selected' : '' }}>Frozen</option>
            </select>
        </div>
        <div class="form-group mb-0 panel-filter-actions-col">
            <label class="form-label panel-filter-actions__label-spacer" aria-hidden="true">&nbsp;</label>
            <div class="panel-filter-actions">
            <button type="submit" class="btn btn--primary">Filter</button>
            <a href="{{ route('admin.dealers.index') }}" class="btn btn--ghost">Clear</a>
            </div>
        </div>
    </form>
</div>

{{-- Table wrapped in .card from global.css --}}
<div class="card" style="padding: 0; overflow: hidden;">
    <table class="table">
        <thead>
            <tr>
                <th></th>
                <th>Dealer Info</th>
                <th>Company</th>
                <th>Contact</th>
                <th>Type</th>
                <th>Service Offerings</th>
                <th>Credits</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dealers as $dealer)
            <tr>
                <td style="width: 70px; text-align: center; vertical-align: middle;">
                    <div style="width: 50px; height: 50px; margin: 0 auto; border-radius: 50%; overflow: hidden; border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center;">
                        @if($dealer->profile_picture)
                            <img src="{{ \App\Support\PublicMedia::url($dealer->profile_picture) }}" 
                                 alt="Profile Picture" 
                                 loading="lazy"
                                 style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        @else
                            <div class="letter-avatar" style="width: 100%; height: 100%; font-size: 1.5rem;">
                                {{ substr($dealer->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                </td>
                {{-- Dealer Info --}}
                <td>
                    <div class="fw-700 text-dark text-sm">{{ $dealer->name }}</div>
                    <div class="icon-row icon-row--sm text-xs text-muted" style="margin-top:3px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                        {{ $dealer->email }}
                    </div>
                </td>

                {{-- Company --}}
                <td>
                    @if($dealer->company_name)
                        <div class="fw-700 text-dark text-sm">{{ $dealer->company_name }}</div>
                        @if($dealer->company_number)
                            <div class="text-xs text-muted mt-1">Co: {{ $dealer->company_number }}</div>
                        @endif
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>

                {{-- Contact --}}
                <td>
                    @if($dealer->phone)
                        <div class="icon-row icon-row--md text-sm" style="margin-bottom:3px;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                            </svg>
                            {{ $dealer->phone }}
                        </div>
                    @endif
                    @if($dealer->postcode)
                        <div class="icon-row icon-row--md text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                            {{ $dealer->postcode }}
                        </div>
                    @endif
                    @if(!$dealer->phone && !$dealer->postcode)
                        <span class="text-muted">—</span>
                    @endif
                </td>

                {{-- Type --}}
                <td>
                    @if($dealer->type)
                        <span class="text-sm">{{ $dealer->type }}</span>
                    @else
                        <span class="text-muted text-sm">Not set</span>
                    @endif
                </td>

                {{-- Service Offerings --}}
                <td>
                    @if($dealer->service_offerings && count($dealer->service_offerings))
                        <span class="text-sm">{{ implode(', ', $dealer->service_offerings) }}</span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>

                {{-- Credits --}}
                <td>
                    <div class="credits">
                        {{ $dealer->credits ?? 0 }}
                        <button type="button"
                                class="btn-icon js-open-credits"
                                title="Add credits"
                                data-id="{{ $dealer->id }}"
                                data-name="{{ $dealer->name }}"
                                data-credits="{{ $dealer->credits ?? 0 }}"
                                data-action="{{ route('admin.dealers.credits.add', $dealer) }}">+</button>
                    </div>
                </td>

                {{-- Status — .badge from global.css --}}
                <td>
                    @if($dealer->status === 'approved')
                        <span class="badge badge--success">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Approved
                        </span>
                    @elseif($dealer->status === 'pending')
                        <span class="badge badge--pending">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Pending
                        </span>
                    @elseif($dealer->status === 'paused')
                        <span class="badge" style="background:#fff7ed;color:#9a3412;border:1px solid #fdba74">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5" />
                            </svg>
                            Paused
                        </span>
                    @elseif($dealer->status === 'frozen')
                        <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            Frozen
                        </span>
                    @else
                        <span class="badge badge--dark">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            Revoked
                        </span>
                    @endif
                </td>

                {{-- Actions --}}
                <td>
                    <div class="actions-col">
                        <div class="actions-row">
                            <button type="button"
                               class="icon-btn js-open-edit"
                               title="Edit dealer"
                               data-action="{{ route('admin.dealers.update', $dealer) }}"
                               data-name="{{ $dealer->name }}"
                               data-email="{{ $dealer->email }}"
                               data-company_name="{{ $dealer->company_name }}"
                               data-company_number="{{ $dealer->company_number }}"
                               data-vat_number="{{ $dealer->vat_number }}"
                               data-phone="{{ $dealer->phone }}"
                               data-postcode="{{ $dealer->postcode }}"
                               data-address="{{ $dealer->address }}"
                               data-website="{{ $dealer->website }}"
                               data-status="{{ $dealer->status }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                </svg>
                            </button>
                            <button type="button"
                               class="icon-btn js-open-password"
                               title="Set new password"
                               data-name="{{ $dealer->name }}"
                               data-email="{{ $dealer->email }}"
                               data-action="{{ route('admin.dealers.reset-password', $dealer) }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z" />
                                </svg>
                            </button>
                            <button type="button"
                               class="icon-btn js-open-delete"
                               title="Delete"
                               data-action="{{ route('admin.dealers.destroy', $dealer) }}"
                               data-entity="dealer">✕</button>
                        </div>

                        @if($dealer->status === 'approved')
                            <form method="POST" action="{{ route('admin.dealers.revoke', $dealer) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="btn btn--sm btn--danger"
                                        onclick="return confirm('Revoke access for {{ $dealer->name }}?')">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:13px;height:13px">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    Revoke
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.dealers.approve', $dealer) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn--sm btn--primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:13px;height:13px">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    Approve
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8">
                    {{-- .panel-coming-soon from panel.css --}}
                    <div class="panel-coming-soon" style="border: none; box-shadow: none; padding: 3rem 2rem;">
                        <div class="panel-coming-soon__icon">👥</div>
                        <h2>No Dealers Found</h2>
                        <p>No dealer accounts exist yet. Create the first one using the button above.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination — .mt-6 spacing utility from global.css --}}
@if($dealers->hasPages())
    <div class="mt-6">
        {{ $dealers->onEachSide(1)->links('components.pagination') }}
    </div>
@endif

<div class="modal-backdrop" id="createDealerModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Create New Dealer</div>
            <button type="button" class="modal-close" data-close="#createDealerModal">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.dealers.store') }}">
            @csrf
            <div class="grid grid--2">
                <div class="form-group">
                    <label class="form-label">Contact Name *</label>
                    <input name="name" class="form-input" placeholder="John Smith" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-input" placeholder="contact@company.com" value="{{ old('email') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Company Name *</label>
                    <input name="company_name" class="form-input" placeholder="Company Name Ltd" value="{{ old('company_name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Company Number *</label>
                    <input name="company_number" class="form-input" placeholder="e.g., 12345678" value="{{ old('company_number') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">VAT Number *</label>
                    <input name="vat_number" class="form-input" placeholder="e.g., GB123456789" value="{{ old('vat_number') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone *</label>
                    <input name="phone" class="form-input" placeholder="020 1234 5678" value="{{ old('phone') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Postcode *</label>
                    <input name="postcode" class="form-input" placeholder="SW1A 1AA" value="{{ old('postcode') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input name="address" class="form-input" placeholder="Full business address" value="{{ old('address') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Website *</label>
                    <input name="website" class="form-input" placeholder="https://www.company.com" value="{{ old('website') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Temporary Password *</label>
                    <input name="password" class="form-input" placeholder="TempPass123!" required>
                </div>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn--primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                    </svg>
                    Create Dealer
                </button>
                <button type="button" class="btn" data-close="#createDealerModal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="editDealerModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Edit Dealer - Full Profile Access</div>
            <button type="button" class="modal-close" data-close="#editDealerModal">✕</button>
        </div>
        <form id="editDealerForm" method="POST" action="#">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="grid grid--2">
                    <div class="form-group"><label class="form-label">Contact Name *</label><input name="name" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">Company Name *</label><input name="company_name" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">Company Number *</label><input name="company_number" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">VAT Number *</label><input name="vat_number" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">Phone *</label><input name="phone" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">Postcode *</label><input name="postcode" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">Address</label><input name="address" class="form-input"></div>
                    <div class="form-group"><label class="form-label">Website *</label><input name="website" class="form-input" required></div>
                    <div class="form-group">
                        <label class="form-label">Account Status</label>
                        <select name="status" class="form-input" id="editDealerStatus">
                            <option value="pending">Pending Approval</option>
                            <option value="approved">Approved / Active</option>
                            <option value="paused">Pause Account</option>
                            <option value="frozen">Freeze Account</option>
                            <option value="revoked">Revoked / Disabled</option>
                        </select>
                    </div>
                    <div class="form-group" id="resumeOptionContainer" style="display:none; grid-column: span 2;">
                        <div class="alert alert--warning" style="margin-bottom:0; display:flex; align-items:center; justify-content:space-between;">
                            <span>This account is currently <strong>paused or frozen</strong>.</span>
                            <button type="button" class="btn btn--sm btn--primary" onclick="resumeAccount()">Resume Account</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn--primary">Save Changes</button>
                <button type="button" class="btn" data-close="#editDealerModal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="creditsModal">
    <div class="modal-container modal-container--sm">
        <div class="modal-header">
            <div class="modal-title">Add Credits</div>
            <button type="button" class="modal-close" data-close="#creditsModal">✕</button>
        </div>
        <div class="text-sm text-muted" id="creditsCurrent">Current balance: 0 credits</div>
        <form id="creditsForm" method="POST" action="#">
            @csrf
            <div class="form-group mt-3">
                <label class="form-label">Number of Credits to Add</label>
                <input name="amount" type="number" min="1" class="form-input" placeholder="Enter amount" required>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn--primary">+ Add Credits</button>
                <button type="button" class="btn" data-close="#creditsModal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="passwordModal">
    <div class="modal-container modal-container--sm">
        <div class="modal-header">
            <div class="modal-title">Set New Password</div>
            <button type="button" class="modal-close" data-close="#passwordModal">✕</button>
        </div>
        <div class="text-sm text-muted" id="passwordTargetText">Update account password.</div>
        <form id="passwordForm" method="POST" action="#">
            @csrf
            <div class="form-group mt-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-input" minlength="8" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-input" minlength="8" required>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn--primary">Update Password</button>
                <button type="button" class="btn" data-close="#passwordModal">Cancel</button>
            </div>
        </form>
    </div>
</div>

@include('components.delete-confirm-modal')

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ─── MODAL HELPERS ───────────────────────────────────────────────────────
    function openModal(id) {
        const modal = document.querySelector(id);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(id) {
        const modal = document.querySelector(id);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    window.resumeAccount = function() {
        const statusSel = document.getElementById('editDealerStatus');
        const resumeCont = document.getElementById('resumeOptionContainer');
        if (statusSel) {
            statusSel.value = 'approved';
            if (resumeCont) resumeCont.style.display = 'none';
        }
    };

    // ─── EVENT LISTENERS ─────────────────────────────────────────────────────
    
    // Close buttons
    document.querySelectorAll('[data-close]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            closeModal(btn.getAttribute('data-close'));
        });
    });

    // Backdrop clicks
    document.querySelectorAll('.modal-backdrop').forEach(function(bg) {
        bg.addEventListener('click', function(e) {
            if (e.target === bg) {
                bg.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.active').forEach(function(modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
    });

    // Create Dealer Button
    const createBtn = document.getElementById('btnOpenCreateDealer');
    if (createBtn) {
        createBtn.addEventListener('click', function() {
            openModal('#createDealerModal');
        });
    }

    // Credits Modal
    document.querySelectorAll('.js-open-credits').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const current = btn.getAttribute('data-credits') || '0';
            const currentEl = document.getElementById('creditsCurrent');
            if (currentEl) currentEl.textContent = 'Current balance: ' + current + ' credits';
            
            const form = document.getElementById('creditsForm');
            if (form) {
                form.action = btn.getAttribute('data-action');
                const amountInput = form.querySelector('[name="amount"]');
                if (amountInput) amountInput.value = '';
            }
            openModal('#creditsModal');
        });
    });

    document.querySelectorAll('.js-open-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const form = document.getElementById('passwordForm');
            const targetText = document.getElementById('passwordTargetText');
            if (form) {
                form.action = btn.getAttribute('data-action');
                form.reset();
            }
            if (targetText) {
                targetText.textContent = 'Set a new password for ' + (btn.getAttribute('data-name') || 'this account') + ' (' + (btn.getAttribute('data-email') || '') + ').';
            }
            openModal('#passwordModal');
        });
    });

    // Edit Dealer Modal
    const editForm = document.getElementById('editDealerForm');
    const statusSel = document.getElementById('editDealerStatus');
    const resumeCont = document.getElementById('resumeOptionContainer');

    document.querySelectorAll('.js-open-edit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (editForm) {
                editForm.action = btn.getAttribute('data-action');
                const fields = ['name', 'email', 'company_name', 'company_number', 'vat_number', 'phone', 'postcode', 'address', 'website', 'status'];
                
                fields.forEach(function(key) {
                    const input = editForm.querySelector('[name="' + key + '"]');
                    if (input) {
                        const val = btn.getAttribute('data-' + key) || '';
                        input.value = val;
                        
                        if (key === 'status') {
                            if (val === 'paused' || val === 'frozen') {
                                if (resumeCont) resumeCont.style.display = 'block';
                            } else {
                                if (resumeCont) resumeCont.style.display = 'none';
                            }
                        }
                    }
                });
            }
            openModal('#editDealerModal');
        });
    });

    // Status Select Change
    if (statusSel) {
        statusSel.addEventListener('change', function() {
            if (this.value === 'paused' || this.value === 'frozen') {
                if (resumeCont) resumeCont.style.display = 'block';
            } else {
                if (resumeCont) resumeCont.style.display = 'none';
            }
        });
    }
});
</script>
@endsection

@endsection

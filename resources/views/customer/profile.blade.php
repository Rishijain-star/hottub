@extends('layouts.customer')
@section('title', 'Profile Settings – Customer Panel')
@section('content')
@php $user = auth()->user(); @endphp
<div class="panel-page-header"><div><h1 class="panel-page-title">Profile Settings</h1><p class="panel-page-sub">Manage your account details</p></div></div>

@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif

<div class="grid grid--2" style="display:grid; grid-template-columns: 0.6fr 1.4fr; gap: 2rem; align-items: start;">
    <div class="card" style="text-align: center;">
        <div class="fw-800 mb-4" style="font-size:1.05rem;color:var(--gray-900)">Profile Picture</div>
        <div style="margin-bottom: 1.5rem;">
            @if($user->profile_picture)
                <img src="{{ \App\Support\PublicMedia::url($user->profile_picture) }}" alt="Profile" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid #f1f5f9;">
            @else
                <div style="width: 150px; height: 150px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin: 0 auto; color: #64748b; font-size: 3rem; font-weight: 800; border: 4px solid #f1f5f9;">
                    {{ substr($user->name, 0, 1) }}
                </div>
            @endif
        </div>
        <form action="{{ route('customer.profile.update-image') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <input type="file" name="image" class="form-input" style="font-size: 0.8rem;" required>
            </div>
            <button type="submit" class="btn btn--primary btn--sm w-100">Update Photo</button>
        </form>
    </div>

    <div class="card">
        <div class="fw-800 mb-4" style="font-size:1.05rem;color:var(--gray-900)">Account Information</div>
        <form action="{{ route('customer.profile.update') }}" method="POST">
            @csrf @method('PUT')
            <div class="grid grid--2" style="gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-input" value="{{ $user->name }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input" value="{{ $user->email }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-input" value="{{ $user->phone }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Postcode</label>
                    <input type="text" name="postcode" class="form-input" value="{{ $user->postcode }}">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-input" rows="2" placeholder="Street, city, county">{{ $user->address }}</textarea>
                </div>
            </div>
            <div class="mt-4" style="text-align: right;">
                <button type="submit" class="btn btn--primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div class="card" style="margin-top: 2rem;">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Delete Account</div>
    <p class="text-sm text-muted">Permanently delete your account and all associated data. This action is irreversible.</p>
    <div style="text-align: right; margin-top: 1rem;">
        <form method="POST" action="{{ route('customer.profile.delete') }}" onsubmit="event.preventDefault(); showConfirmationModal(this, 'Delete Account?', 'You are about to request permanent account deletion. This will be finalized in 30 days. Are you sure?', 'Yes, Request Deletion');">
            @csrf
            @method('DELETE')
            <button class="btn btn--danger">Delete My Account</button>
        </form>
    </div>
</div>
@endsection

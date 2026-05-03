@extends('layouts.manufacturer')
@section('title', 'My Profile – Manufacturer Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">My Profile</h1>
        <p class="panel-page-sub">View and edit your manufacturer account details</p>
    </div>
</div>

<div class="card" style="width: 100%;">
    @if(session('success'))
        <div class="alert alert--success" style="margin-bottom: 2rem; padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px;">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert--danger" style="margin-bottom: 2rem; padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px;">
            <ul style="margin: 0; padding-left: 1.5rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('manufacturer.profile.update') }}" enctype="multipart/form-data">
        @csrf
        <div style="display: flex; align-items: center; gap: 2.5rem; margin-bottom: 4rem;">
            <div style="position: relative; width: 150px; height: 150px; flex-shrink: 0; border-radius: 50%; overflow: hidden; border: 4px solid #f3f4f6; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: center;">
                @if($manufacturer->profile_picture)
                    <img id="profile_picture_preview" src="{{ \App\Support\PublicMedia::url($manufacturer->profile_picture) }}" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                @else
                    <div id="profile_picture_preview_container" class="letter-avatar" style="width: 100%; height: 100%; font-size: 4rem;">
                        {{ substr($manufacturer->name, 0, 1) }}
                    </div>
                    <img id="profile_picture_preview" src="" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                @endif
                <label for="profile_picture" style="position: absolute; bottom: 8px; right: 8px; background: var(--primary); color: #fff; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.2); border: 2px solid #fff; transition: all 0.2s; z-index: 10;" onmouseover="this.style.transform='scale(1.1)'; this.style.background='#00b395';" onmouseout="this.style.transform='scale(1)'; this.style.background='var(--primary)';" title="Change Picture">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    <input type="file" id="profile_picture" name="profile_picture" style="display: none;" accept="image/*">
                </label>
            </div>
            <div style="flex-grow: 1;">
                <div class="fw-800 text-dark" style="font-size: 2.25rem; line-height: 1.1; letter-spacing: -0.02em;">{{ $manufacturer->company_name ?: $manufacturer->name }}</div>
                <div class="text-sm text-muted" style="margin-top: 0.6rem; font-size: 1.1rem; font-weight: 500;">Manufacturer Account · ID: #{{ str_pad($manufacturer->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div class="text-sm text-muted" style="margin-top: 0.4rem; font-size: 1rem; font-weight: 500;">{{ $manufacturer->postcode ?: '—' }}</div>
                <button type="submit" class="btn btn--primary btn--sm" style="margin-top: 1.25rem; padding: 0.6rem 1.25rem; border-radius: 8px; font-weight: 600;">Update Profile Picture</button>
            </div>
        </div>

        <div class="grid grid--2" style="gap: 2.5rem 3rem;">
            <div class="form-group">
                <label class="form-label" style="font-weight: 600; color: #4b5563; margin-bottom: 0.75rem;">Contact Person</label>
                <input type="text" name="name" class="form-input" value="{{ $manufacturer->name }}" readonly style="background-color: #f9fafb; cursor: not-allowed; border-color: #e5e7eb; color: #6b7280; padding: 0.75rem 1rem;">
            </div>
            <div class="form-group">
                <label class="form-label" style="font-weight: 600; color: #4b5563; margin-bottom: 0.75rem;">Email Address</label>
                <input type="email" name="email" class="form-input" value="{{ $manufacturer->email }}" readonly style="background-color: #f9fafb; cursor: not-allowed; border-color: #e5e7eb; color: #6b7280; padding: 0.75rem 1rem;">
            </div>
            <div class="form-group">
                <label class="form-label" style="font-weight: 600; color: #4b5563; margin-bottom: 0.75rem;">Mobile Number</label>
                <input type="text" name="phone" class="form-input" value="{{ $manufacturer->phone ?: '—' }}" readonly style="background-color: #f9fafb; cursor: not-allowed; border-color: #e5e7eb; color: #6b7280; padding: 0.75rem 1rem;">
            </div>
            <div class="form-group">
                <label class="form-label" style="font-weight: 600; color: #4b5563; margin-bottom: 0.75rem;">Postcode</label>
                <input type="text" class="form-input" value="{{ $manufacturer->postcode ?: '—' }}" readonly style="background-color: #f9fafb; cursor: not-allowed; border-color: #e5e7eb; color: #6b7280; padding: 0.75rem 1rem;">
            </div>
            <div class="form-group">
                <label class="form-label" style="font-weight: 600; color: #4b5563; margin-bottom: 0.75rem;">Website</label>
                <input type="text" name="website" class="form-input" value="{{ $manufacturer->website }}" readonly style="background-color: #f9fafb; cursor: not-allowed; border-color: #e5e7eb; color: #6b7280; padding: 0.75rem 1rem;">
            </div>
            <div class="form-group">
                <label class="form-label" style="font-weight: 600; color: #4b5563; margin-bottom: 0.75rem;">Company Registration Number</label>
                <input type="text" name="company_number" class="form-input" value="{{ $manufacturer->company_number ?: '—' }}" readonly style="background-color: #f9fafb; cursor: not-allowed; border-color: #e5e7eb; color: #6b7280; padding: 0.75rem 1rem;">
            </div>
            <div class="form-group">
                <label class="form-label" style="font-weight: 600; color: #4b5563; margin-bottom: 0.75rem;">VAT Number</label>
                <input type="text" name="vat_number" class="form-input" value="{{ $manufacturer->vat_number ?: '—' }}" readonly style="background-color: #f9fafb; cursor: not-allowed; border-color: #e5e7eb; color: #6b7280; padding: 0.75rem 1rem;">
            </div>
        </div>

        <div class="form-group" style="margin-top: 2.5rem;">
            <label class="form-label" style="font-weight: 600; color: #4b5563; margin-bottom: 0.75rem;">Business Address & Postcode</label>
            <textarea name="address" class="form-input" rows="3" readonly style="background-color: #f9fafb; cursor: not-allowed; border-color: #e5e7eb; color: #6b7280; padding: 0.75rem 1rem;">{{ $manufacturer->address }}</textarea>
        </div>
    </form>
</div>

<div class="card" style="margin-top: 2rem;">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Delete Account</div>
    <p class="text-sm text-muted">Permanently delete your account and all associated data. This action is irreversible.</p>
    <div style="text-align: right; margin-top: 1rem;">
        <form method="POST" action="{{ route('manufacturer.profile.delete') }}" onsubmit="event.preventDefault(); showConfirmationModal(this, 'Delete Account?', 'You are about to request permanent account deletion. This will be finalized in 30 days. Are you sure?', 'Yes, Request Deletion');">
            @csrf
            @method('DELETE')
            <button class="btn btn--danger">Delete My Account</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('profile_picture').addEventListener('change', function(event) {
        const [file] = event.target.files;
        if (file) {
            const preview = document.getElementById('profile_picture_preview');
            const container = document.getElementById('profile_picture_preview_container');
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
            if (container) {
                container.style.display = 'none';
            }
        }
    });
</script>
@endsection


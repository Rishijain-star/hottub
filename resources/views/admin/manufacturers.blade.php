@extends('layouts.admin')
@section('title', 'Manufacturers – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Manufacturer Management</h1><p class="panel-page-sub">Approve manufacturers, manage credits, and edit profile information</p></div>
    <button class="btn btn--primary btn--pill" id="toggleCreateManu">Create Manufacturer</button>
    </div>
@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert--danger">{{ $errors->first() }}</div> @endif
<div class="card" id="createManuCard" style="display:none">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">Create New Manufacturer</div>
    <form method="POST" action="{{ route('admin.manufacturers.store') }}">
        @csrf
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">Contact Name *</label><input name="name" class="form-input" required></div>
            <div class="form-group"><label class="form-label">Email *</label><input name="email" class="form-input" type="email" required></div>
            <div class="form-group"><label class="form-label">Company Name *</label><input name="company_name" class="form-input" required></div>
            <div class="form-group"><label class="form-label">VAT Number</label><input name="vat_number" class="form-input"></div>
            <div class="form-group"><label class="form-label">Company Number</label><input name="company_number" class="form-input"></div>
            <div class="form-group"><label class="form-label">Phone</label><input name="phone" class="form-input"></div>
            <div class="form-group"><label class="form-label">Postcode</label><input name="postcode" class="form-input"></div>
            <div class="form-group"><label class="form-label">Address</label><input name="address" class="form-input"></div>
            <div class="form-group"><label class="form-label">Website</label><input name="website" class="form-input"></div>
            <div class="form-group"><label class="form-label">Temporary Password *</label><input name="password" class="form-input" type="password" required></div>
        </div>
        @include('components.upload-progress')
        <div class="modal-actions" style="justify-content:flex-start">
            <button class="btn btn--primary" type="submit">Create</button>
        </div>
    </form>
    <script>
        document.getElementById('toggleCreateManu')?.addEventListener('click', function(){
            const el = document.getElementById('createManuCard');
            el.style.display = el.style.display === 'none' ? '' : 'none';
        });
    </script>
</div>
<div class="card" style="padding:0;margin-top:1rem;">
    <table class="table">
        <thead><tr><th></th><th>Manufacturer Info</th><th>Company</th><th>Contact</th><th>Credits</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($manufacturers as $m)
            <tr>
                <td style="width: 70px; text-align: center; vertical-align: middle; padding-left: 1.5rem;">
                    <div style="width: 50px; height: 50px; margin: 0 auto; border-radius: 50%; overflow: hidden; border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center;">
                        @if($m->profile_picture)
                            <img src="{{ asset('storage/' . $m->profile_picture) }}" 
                                 alt="Profile Picture" 
                                 loading="lazy"
                                 style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        @else
                            <div class="letter-avatar" style="width: 100%; height: 100%; font-size: 1.5rem;">
                                {{ substr($m->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                </td>
                <td>
                    <div class="fw-700 text-dark">{{ $m->name }}</div>
                    <div class="text-sm text-muted">{{ $m->email }}</div>
                </td>
                <td>
                    <div>{{ $m->company_name ?? '—' }}</div>
                    <div class="text-sm text-muted">Co: {{ $m->company_number ?? '—' }}</div>
                </td>
                <td>
                    <div>📞 {{ $m->phone ?? '—' }}</div>
                    <div class="text-sm text-muted">{{ $m->postcode ?? '—' }}</div>
                </td>
                <td>{{ $m->credits ?? 0 }} <a href="{{ route('admin.manufacturers.credits', $m) }}" class="btn btn--ghost btn--sm">+</a></td>
                <td>
                    @if($m->status==='approved')
                        <span class="badge badge--success">Approved</span>
                    @elseif($m->status==='pending')
                        <span class="badge">Pending</span>
                    @elseif($m->status==='paused')
                        <span class="badge" style="background:#fff7ed;color:#9a3412;border:1px solid #fdba74">Paused</span>
                    @elseif($m->status==='frozen')
                        <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca">Frozen</span>
                    @else
                        <span class="badge badge--dark">Revoked</span>
                    @endif
                </td>
                <td>
                    <div class="actions-row">
                        @if($m->status!=='approved')
                            <form method="POST" action="{{ route('admin.manufacturers.approve', $m) }}">@csrf @method('PATCH') <button class="btn btn--ghost btn--sm">Approve</button></form>
                        @else
                            <form method="POST" action="{{ route('admin.manufacturers.revoke', $m) }}">@csrf @method('PATCH') <button class="btn btn--danger btn--sm">Revoke</button></form>
                        @endif
                        <a href="{{ route('admin.manufacturers.edit', $m) }}" class="icon-btn" title="Edit">✎</a>
                        <form method="POST" action="{{ route('admin.manufacturers.destroy', $m) }}" onsubmit="return confirm('Delete this manufacturer?')">
                            @csrf @method('DELETE')
                            <button class="icon-btn" title="Delete">✕</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-muted">No manufacturers found.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $manufacturers->links('components.pagination') }}</div>
</div>
@endsection

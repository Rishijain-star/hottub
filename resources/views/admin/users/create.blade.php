@extends('layouts.admin')
@section('title', 'Add sub-admin – Admin Panel')

@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Add sub-admin</h1>
        <p class="panel-page-sub">Creates a staff account with admin panel access (restricted vs the main admin). You set the password; they use the same login page.</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn--ghost">Back to list</a>
</div>

@if($errors->any())
    <div class="alert alert--danger">{{ $errors->first() }}</div>
@endif

<div class="card" style="max-width:640px">
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="grid grid--2" style="gap:15px">
            <div class="form-group">
                <label class="form-label">Full name *</label>
                <input type="text" name="name" class="form-input" value="{{ old('name') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-input" value="{{ old('email') }}" required autocomplete="off">
            </div>
            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-input" required minlength="8" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label">Confirm password *</label>
                <input type="password" name="password_confirmation" class="form-input" required minlength="8" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-input" value="{{ old('phone') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Postcode</label>
                <input type="text" name="postcode" class="form-input" value="{{ old('postcode') }}">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-input" rows="2">{{ old('address') }}</textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Status *</label>
            <select name="status" class="form-input" required>
                <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                <option value="paused" @selected(old('status') === 'paused')>Paused</option>
                <option value="frozen" @selected(old('status') === 'frozen')>Frozen</option>
            </select>
        </div>
        <div class="modal-actions" style="justify-content:flex-start;margin-top:1rem">
            <button type="submit" class="btn btn--primary">Create sub-admin</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn--ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection

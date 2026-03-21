@extends('layouts.admin')
@section('title', 'Edit Manufacturer – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Edit Manufacturer</h1><p class="panel-page-sub">{{ $manufacturer->name }}</p></div>
    <a href="{{ route('admin.manufacturers') }}" class="btn">Back</a>
@endsection
@@
@section('content')
@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert--danger">{{ $errors->first() }}</div> @endif
<div class="card">
    <form method="POST" action="{{ route('admin.manufacturers.update', $manufacturer) }}">
        @csrf @method('PUT')
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">Contact Name *</label><input name="name" class="form-input" value="{{ old('name',$manufacturer->name) }}" required></div>
            <div class="form-group"><label class="form-label">Email *</label><input name="email" class="form-input" type="email" value="{{ old('email',$manufacturer->email) }}" required></div>
            <div class="form-group"><label class="form-label">Company Name *</label><input name="company_name" class="form-input" value="{{ old('company_name',$manufacturer->company_name) }}" required></div>
            <div class="form-group"><label class="form-label">VAT Number</label><input name="vat_number" class="form-input" value="{{ old('vat_number',$manufacturer->vat_number) }}"></div>
            <div class="form-group"><label class="form-label">Company Number</label><input name="company_number" class="form-input" value="{{ old('company_number',$manufacturer->company_number) }}"></div>
            <div class="form-group"><label class="form-label">Phone</label><input name="phone" class="form-input" value="{{ old('phone',$manufacturer->phone) }}"></div>
            <div class="form-group"><label class="form-label">Postcode</label><input name="postcode" class="form-input" value="{{ old('postcode',$manufacturer->postcode) }}"></div>
            <div class="form-group"><label class="form-label">Address</label><input name="address" class="form-input" value="{{ old('address',$manufacturer->address) }}"></div>
            <div class="form-group"><label class="form-label">Website</label><input name="website" class="form-input" value="{{ old('website',$manufacturer->website) }}"></div>
            <div class="form-group"><label class="form-label">New Password</label><input name="password" type="password" class="form-input"></div>
            <div class="form-group">
                <label class="form-label">Account Status</label>
                <select name="status" class="form-input" id="statusSel">
                    <option value="pending" @selected(old('status',$manufacturer->status)=='pending')>Pending Approval</option>
                    <option value="approved" @selected(old('status',$manufacturer->status)=='approved')>Approved / Active</option>
                    <option value="paused" @selected(old('status',$manufacturer->status)=='paused')>Pause Account</option>
                    <option value="frozen" @selected(old('status',$manufacturer->status)=='frozen')>Freeze Account</option>
                    <option value="revoked" @selected(old('status',$manufacturer->status)=='revoked')>Revoked / Disabled</option>
                </select>
            </div>
            <div class="form-group" id="resumeCont" style="display:{{ in_array($manufacturer->status, ['paused', 'frozen']) ? 'block' : 'none' }}; grid-column: span 2;">
                <div class="alert alert--warning" style="display:flex; align-items:center; justify-content:space-between;">
                    <span>This account is currently <strong>paused or frozen</strong>.</span>
                    <button type="button" class="btn btn--sm btn--primary" onclick="resume()">Resume Account</button>
                </div>
            </div>
        </div>
        <div class="modal-actions" style="justify-content:flex-start">
            <button class="btn btn--primary" type="submit">Save Changes</button>
        </div>
    </form>
</div>

<script>
function resume() {
    const sel = document.getElementById('statusSel');
    if (sel) {
        sel.value = 'approved';
        document.getElementById('resumeCont').style.display = 'none';
    }
}
document.getElementById('statusSel')?.addEventListener('change', function(){
    const cont = document.getElementById('resumeCont');
    if (this.value === 'paused' || this.value === 'frozen') {
        cont.style.display = 'block';
    } else {
        cont.style.display = 'none';
    }
});
</script>
@endsection

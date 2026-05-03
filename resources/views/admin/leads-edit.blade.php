@extends('layouts.admin')
@section('title', 'Edit Lead – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">Edit Lead</h1><p class="panel-page-sub">{{ $item->name }}</p></div>
    <a href="{{ route('admin.leads') }}" class="btn">Back</a>
</div>
@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert--danger">{{ $errors->first() }}</div> @endif
<div class="card">
    <form method="POST" action="{{ route('admin.leads.update', $item) }}">
        @csrf @method('PUT')
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">Name *</label><input name="name" class="form-input" value="{{ old('name',$item->name) }}" required></div>
            <div class="form-group"><label class="form-label">Email *</label><input name="email" class="form-input" type="email" value="{{ old('email',$item->email) }}" required></div>
            <div class="form-group"><label class="form-label">Phone *</label><input name="phone" class="form-input" value="{{ old('phone',$item->phone) }}"></div>
            <div class="form-group"><label class="form-label">Postcode *</label><input name="postcode" class="form-input" value="{{ old('postcode',$item->postcode) }}" required></div>
            <div class="form-group" style="display:flex;align-items:center;gap:8px;margin-top:25px">
                <input type="checkbox" name="is_national" id="is_national" value="1" @checked(old('is_national',$item->is_national))>
                <label for="is_national" class="form-label" style="margin-bottom:0">National Lead (No postcode restriction for dealers)</label>
            </div>
        </div>
        <div class="form-group"><label class="form-label">What are they looking for? *</label>
            <div class="grid grid--3">
                @php $ints = is_array($item->interests)?$item->interests:[]; @endphp
                @foreach(['hot_tub'=>'Hot Tub','swim_spa'=>'Swim Spa','pool'=>'Pool','sauna'=>'Sauna','outdoor_kitchen'=>'Outdoor Kitchen','other'=>'Other'] as $key=>$label)
                <label class="form-check" style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="interests[]" value="{{ $key }}" @checked(in_array($key, old('interests',$ints)))> {{ $label }}</label>
                @endforeach
            </div>
        </div>
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">Preferred Timeframe</label>
                <select name="timeframe" class="form-input">
                    @foreach(['Not specified','Immediate','1–3 months','3–6 months','6+ months'] as $tf)
                        <option @selected(old('timeframe',$item->timeframe)===$tf)>{{ $tf }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label class="form-label">Status</label>
                <select name="status" class="form-input">
                    @foreach(['new'=>'New','contacted'=>'Contacted','converted'=>'Converted','closed'=>'Closed'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('status',$item->status)===$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group"><label class="form-label">Message</label><textarea name="message" class="form-input" rows="4">{{ old('message',$item->message) }}</textarea></div>
        <div class="modal-actions" style="justify-content:flex-start"><button class="btn btn--primary">Save Changes</button></div>
    </form>
</div>
<div class="card" style="padding:0;margin-top:1rem;">
    <table class="table">
        <thead><tr><th>Customer</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($items as $l)
            <tr>
                <td><div class="fw-700 text-dark">{{ $l->name }}</div><div class="text-sm text-muted">{{ $l->email }}</div></td>
                <td>@if($l->status==='converted')<span class="badge badge--success">Converted</span>@elseif($l->status==='contacted')<span class="badge">Contacted</span>@elseif($l->status==='closed')<span class="badge badge--dark">Closed</span>@else<span class="badge">New</span>@endif</td>
                <td>
                    <div class="actions-row">
                        <a href="{{ route('admin.leads.activity', $l) }}" class="icon-btn" title="Activity">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </a>
                        <a href="{{ route('admin.leads.edit', $l) }}" class="icon-btn" title="Edit">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                            </svg>
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4" style="padding:1rem">{{ $items->links('components.pagination') }}</div>
</div>
@endsection

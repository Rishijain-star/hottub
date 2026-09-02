@extends('layouts.admin')
@section('title', __('panel.admin.lead_edit.title') . ' - ' . __('panel.admin_title'))
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">{{ __('panel.admin.lead_edit.title') }}</h1><p class="panel-page-sub">{{ $item->name }}</p></div>
    <a href="{{ route('admin.leads') }}" class="btn">{{ __('panel.admin.common.back') }}</a>
</div>
@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert--danger">{{ $errors->first() }}</div> @endif
<div class="card">
    <form method="POST" action="{{ route('admin.leads.update', $item) }}">
        @csrf @method('PUT')
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">{{ __('panel.admin.common.name') }} *</label><input name="name" class="form-input" value="{{ old('name',$item->name) }}" required></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.common.email') }} *</label><input name="email" class="form-input" type="email" value="{{ old('email',$item->email) }}" required></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.common.phone') }} *</label><input name="phone" class="form-input" value="{{ old('phone',$item->phone) }}"></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.common.postcode') }} *</label><input name="postcode" class="form-input" value="{{ old('postcode',$item->postcode) }}" required></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.common.price') }} (GBP) *</label><input name="price" class="form-input" type="number" step="0.01" min="0" value="{{ old('price', $item->price) }}" required></div>
            <div class="form-group" style="display:flex;align-items:center;gap:8px;margin-top:25px">
                <input type="checkbox" name="is_national" id="is_national" value="1" @checked(old('is_national',$item->is_national))>
                <label for="is_national" class="form-label" style="margin-bottom:0">{{ __('panel.admin.leads.national_lead') }}</label>
            </div>
        </div>
        <div class="form-group"><label class="form-label">{{ __('panel.admin.leads.looking_for') }} *</label>
            <div class="grid grid--3">
                @php
                    $ints = is_array($item->interests) ? $item->interests : [];
                    $ints = array_map(fn ($v) => $v === 'outdoor_kitchen' ? 'outdoor_product' : $v, $ints);
                @endphp
                @foreach(['hot_tub'=>'Hot Tub','swim_spa'=>'Swim Spa','pool'=>'Pool','sauna'=>'Sauna','outdoor_product'=>'Outdoor Product','other'=>'Other'] as $key=>$label)
                <label class="form-check" style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="interests[]" value="{{ $key }}" @checked(in_array($key, old('interests',$ints)))> {{ $label }}</label>
                @endforeach
            </div>
        </div>
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">{{ __('panel.admin.leads.preferred_timeframe') }}</label>
                <select name="timeframe" class="form-input">
                    @foreach([__('panel.admin.leads.not_specified'),__('panel.admin.leads.immediate'),__('panel.admin.leads.months_1_3'),__('panel.admin.leads.months_3_6'),__('panel.admin.leads.months_6_plus')] as $tf)
                        <option @selected(old('timeframe',$item->timeframe)===$tf)>{{ $tf }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.common.status') }}</label>
                <select name="status" class="form-input">
                    @foreach(['new'=>__('panel.admin.leads.new'),'contacted'=>__('panel.admin.leads.contacted'),'converted'=>__('panel.admin.leads.converted'),'closed'=>__('panel.admin.leads.closed')] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('status',$item->status)===$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group"><label class="form-label">{{ __('panel.admin.common.message') }}</label><textarea name="message" class="form-input" rows="4">{{ old('message',$item->message) }}</textarea></div>
        <div class="modal-actions" style="justify-content:flex-start"><button class="btn btn--primary">{{ __('panel.admin.lead_edit.save_changes') }}</button></div>
    </form>
</div>
<div class="card" style="padding:0;margin-top:1rem;">
    <table class="table">
        <thead><tr><th>{{ __('panel.admin.leads.customer') }}</th><th>{{ __('panel.admin.common.status') }}</th><th>{{ __('panel.admin.common.actions') }}</th></tr></thead>
        <tbody>
        @foreach($items as $l)
            <tr>
                <td><div class="fw-700 text-dark">{{ $l->name }}</div><div class="text-sm text-muted">{{ $l->email }}</div></td>
                <td>@if($l->status==='converted')<span class="badge badge--success">{{ __('panel.admin.leads.converted') }}</span>@elseif($l->status==='contacted')<span class="badge">{{ __('panel.admin.leads.contacted') }}</span>@elseif($l->status==='closed')<span class="badge badge--dark">{{ __('panel.admin.leads.closed') }}</span>@else<span class="badge">{{ __('panel.admin.leads.new') }}</span>@endif</td>
                <td>
                    <div class="actions-row">
                        <a href="{{ route('admin.leads.activity', $l) }}" class="icon-btn" title="{{ __('panel.admin.leads.activity') }}">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </a>
                        <a href="{{ route('admin.leads.edit', $l) }}" class="icon-btn" title="{{ __('panel.admin.common.edit') }}">
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

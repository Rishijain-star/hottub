@extends('layouts.admin')
@section('title', __('panel.admin.pages.setting.title') . ' – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">{{ __('panel.admin.pages.setting.title') }}</h1><p class="panel-page-sub">{{ __('panel.admin.pages.setting.sub') }}</p></div>
</div>
<div class="panel-coming-soon"><div class="panel-coming-soon__icon">💳</div><h2>{{ __('panel.admin.pages.setting.coming_soon_title') }}</h2><p>{{ __('panel.admin.pages.setting.coming_soon_sub') }}</p></div>
@endsection
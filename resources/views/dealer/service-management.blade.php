@extends('layouts.dealer')
@section('title', __('panel.service_management.title').' - '.__('panel.dealer_title'))
@section('content')
@include('partials.service-management-index', ['panelSub' => __('panel.service_management.sub')])
@endsection

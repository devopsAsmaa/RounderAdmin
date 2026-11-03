@extends('layouts.admin')
@section('title', __('Edit region').' · Rounder')
@section('body')
<main class="container py-4">
@adminCan('admin.regions.index')
<a class="btn btn-outline-secondary mb-3" href="{{ route('admin.regions.index') }}">
    <i class="fa-solid fa-arrow-right me-1"></i> {{ __('Regions') }}
</a>
@endadminCan
@include('admin.partials.feedback')
<div class="panel p-3 p-lg-4 mb-4">
    <h1 class="h5 fw-bold mb-3">{{ __('Edit region') }}</h1>
    @adminCan('admin.regions.update', 'PUT')
    @include('admin.regions.partials.form', ['action' => route('admin.regions.update', $region->id), 'method' => 'PUT', 'region' => $region])
    @endadminCan
</div>
</main>
@endsection


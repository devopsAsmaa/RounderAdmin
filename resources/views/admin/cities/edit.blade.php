@extends('layouts.admin')
@section('title', __('Edit city').' · Rounder')
@section('body')
<main class="container py-4">
@adminCan('admin.cities.index')
<a class="btn btn-outline-secondary mb-3" href="{{ route('admin.cities.index') }}">
    <i class="fa-solid fa-arrow-right me-1"></i> {{ __('Cities') }}
</a>
@endadminCan
@include('admin.partials.feedback')
<div class="panel p-3 p-lg-4 mb-4">
    <h1 class="h5 fw-bold mb-3">{{ __('Edit city') }}</h1>
    @adminCan('admin.cities.update', 'PUT')
    @include('admin.cities.partials.form', ['action' => route('admin.cities.update', $city->id), 'method' => 'PUT', 'city' => $city, 'regions' => $regions, 'names' => $names])
    @endadminCan
</div>
</main>
@endsection


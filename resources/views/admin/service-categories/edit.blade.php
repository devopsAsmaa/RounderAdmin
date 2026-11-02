@extends('layouts.admin')
@section('title', __('Edit service category').' · Rounder')
@section('body')
<main class="container py-4">
@adminCan('admin.service-categories.index')<a class="btn btn-outline-secondary mb-3" href="{{ route('admin.service-categories.index') }}"><i class="fa-solid fa-arrow-right me-1"></i> {{ __('Service categories') }}</a>@endadminCan
@include('admin.partials.feedback')
<div class="panel p-3 p-lg-4 mb-4"><h1 class="h5 fw-bold mb-3">{{ __('Edit service category') }}</h1>
@adminCan('admin.service-categories.update','PUT')
@include('admin.service-categories.partials.form', ['action' => route('admin.service-categories.update', $category->id), 'method' => 'PUT', 'category' => $category])
@endadminCan
</div>
</main>
@endsection

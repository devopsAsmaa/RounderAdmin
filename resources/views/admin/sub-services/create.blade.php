@extends('layouts.admin')
@section('title', __('Create sub service').' · Rounder')
@section('body')
<main class="container py-4">
@adminCan('admin.sub-services.index')<a class="btn btn-outline-secondary mb-3" href="{{ route('admin.sub-services.index') }}">{{ __('Sub services') }}</a>@endadminCan
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="panel p-3 p-lg-4 mb-4"><h2 class="h5 fw-bold">{{ __('Create sub service') }}</h2>
@adminCan('admin.sub-services.store','POST')
@include('admin.sub-services.partials.form', ['action' => route('admin.sub-services.store'), 'method' => 'POST', 'subService' => null])
@endadminCan
</div>
</main>
@endsection

@extends('layouts.admin')
@section('title', __('Package comparison features').' · Rounder')
@section('body')
<main class="container py-4"><div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <h1 class="h3 fw-bold mb-0">{{ __('Package comparison features') }}</h1>
    @adminCan('admin.package-features.create')
    <a class="btn btn-primary" href="{{ route('admin.package-features.create') }}">
        <i class="fa-solid fa-plus me-1"></i> {{ __('Add comparison feature') }}
    </a>
    @endadminCan
</div>
@include('admin.partials.feedback')
@foreach($featureDefinitions as $feature)
<details class="border rounded p-3 mb-2"><summary>{{ $feature->{'name_'.app()->getLocale()} }} <span class="text-secondary">#{{ $feature->id }} · {{ $feature->is_active ? __('Active') : __('Inactive') }}</span></summary>
@adminCan('admin.package-features.update','PUT')<form class="mt-3" method="post" action="{{ route('admin.package-features.update',$feature->id) }}">@csrf @method('PUT')
@include('admin.packages.partials.feature-definition-fields',['feature'=>$feature])
</form>@endadminCan
@adminCan('admin.package-features.destroy','DELETE')@include('admin.partials.delete-button',['action'=>route('admin.package-features.destroy',$feature->id)])@endadminCan</details>
@endforeach


</main>@endsection

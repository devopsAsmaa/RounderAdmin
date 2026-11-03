@extends('layouts.admin')
@section('title', __('Sub services').' · Rounder')
@section('body')
<main class="container py-4">
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="h3 mb-0">{{ __('Sub services') }}</h1>
    @adminCan('admin.sub-services.create')<a class="btn btn-primary" href="{{ route('admin.sub-services.create') }}"><i class="fa-solid fa-plus me-1"></i> {{ __('Create sub service') }}</a>@endadminCan
</div>
@include('admin.partials.feedback')

<ul class="nav nav-pills mb-4 gap-2">
    @adminCan('admin.service-categories.index')
    <li class="nav-item">
        <a class="nav-link btn btn-outline-secondary" href="{{ route('admin.service-categories.index') }}">
            <i class="fa-solid fa-layer-group me-1"></i> {{ __('Service categories') }}
        </a>
    </li>
    @endadminCan
    @adminCan('admin.services.index')
    <li class="nav-item">
        <a class="nav-link btn btn-outline-secondary" href="{{ route('admin.services.index') }}">
            <i class="fa-solid fa-screwdriver-wrench me-1"></i> {{ __('Available services') }}
        </a>
    </li>
    @endadminCan
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('admin.sub-services.index') }}">
            <i class="fa-solid fa-list-check me-1"></i> {{ __('Sub services') }}
        </a>
    </li>
    @adminCan('admin.prices.index')
    <li class="nav-item">
        <a class="nav-link btn btn-outline-secondary" href="{{ route('admin.prices.index') }}">
            <i class="fa-solid fa-tags me-1"></i> {{ __('Prices') }}
        </a>
    </li>
    @endadminCan
</ul>
<div class="panel p-3 p-lg-4 mb-4"><h2 class="h5 fw-bold">{{ __('Sub services') }}</h2>
<form method="get" class="row g-2 mb-3">
<div class="col-md-4"><select name="service_id" class="form-select"><option value="">{{ __('All services') }}</option>@foreach($services as $service)<option value="{{ $service->id }}" @selected(($filters['service_id'] ?? null)==$service->id)>{{ $service->translations->firstWhere('locale',$locale)?->name ?? $service->code }}</option>@endforeach</select></div>
<div class="col-md-4"><input name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="{{ __('Search') }}"></div>
<div class="col-md-2"><button class="btn btn-outline-primary w-100">{{ __('Filter') }}</button></div>
</form>
<div class="table-responsive"><table class="table align-middle"><thead><th>#</th><th>{{ __('Sub service') }}</th><th>{{ __('Service') }}</th>
<th>{{ __('Sort order') }}</th><th>{{ __('Status') }}</th></thead><tbody>
@forelse($subServices as $sub)<tr><td>{{ $sub->id }}</td>
<td>@if($sub->image_path)<img class="rounded me-2" width="48" height="48" style="object-fit:cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($sub->image_path) }}" alt="{{ __('Product image') }}">@endif{{ $sub->translations->firstWhere('locale',$locale)?->name ?? $sub->code }}<div class="small text-secondary">{{ $sub->code }}</div></td>
<td>{{ $sub->service?->translations->firstWhere('locale',$locale)?->name ?? $sub->service?->code }}</td>
<td>{{ $sub->sort_order }}</td>
<td>{{ $sub->is_active ? __('Active') : __('Inactive') }} @adminCan('admin.sub-services.edit')<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.sub-services.edit', $sub->id) }}">{{ __('Edit') }}</a>@endadminCan
@adminCan('admin.sub-services.destroy','DELETE')@include('admin.partials.delete-button',['action'=>route('admin.sub-services.destroy',$sub->id)])@endadminCan</td></tr>@empty<tr><td colspan="5">{{ __('No sub services found') }}</td></tr>@endforelse
</tbody></table></div>
{{ $subServices->links() }}
</div>
</main>@endsection

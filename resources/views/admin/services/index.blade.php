@extends('layouts.admin')
@section('title', __('Available services').' · Rounder')
@section('body')
<main class="container py-4">
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="h3 mb-0">{{ __('Available services') }}</h1>
    @adminCan('admin.services.create')<a class="btn btn-primary" href="{{ route('admin.services.create') }}"><i class="fa-solid fa-plus me-1"></i> {{ __('Create service') }}</a>@endadminCan
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
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('admin.services.index') }}">
            <i class="fa-solid fa-screwdriver-wrench me-1"></i> {{ __('Available services') }}
        </a>
    </li>
    @adminCan('admin.sub-services.index')
    <li class="nav-item">
        <a class="nav-link btn btn-outline-secondary" href="{{ route('admin.sub-services.index') }}">
            <i class="fa-solid fa-list-check me-1"></i> {{ __('Sub services') }}
        </a>
    </li>
    @endadminCan
    @adminCan('admin.prices.index')
    <li class="nav-item">
        <a class="nav-link btn btn-outline-secondary" href="{{ route('admin.prices.index') }}">
            <i class="fa-solid fa-tags me-1"></i> {{ __('Prices') }}
        </a>
    </li>
    @endadminCan
</ul>
<div class="panel p-3 p-lg-4 mb-4">
    <h2 class="h5 fw-bold mb-3">{{ __('Available services') }}</h2>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>{{ __('Service') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Base price') }}</th>
                    <th style="width: 120px;">{{ __('Status') }}</th>
                    <th style="width: 160px;" class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                <tr>
                    <td class="text-secondary fw-semibold">{{ $service->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($service->image_path)
                                <img class="rounded border" width="44" height="44" style="object-fit:cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($service->image_path) }}" alt="{{ __('Product image') }}">
                            @else
                                <div class="rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(2, 51, 62, 0.05); color: var(--petrol);">
                                    <i class="fa-solid fa-screwdriver-wrench"></i>
                                </div>
                            @endif
                            <div>
                                <div class="fw-bold text-dark">{{ $service->name ?? $service->code }}</div>
                                <div class="small text-secondary font-monospace">{{ $service->code }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge" style="background: rgba(2, 51, 62, 0.06); color: var(--petrol); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 8px;">
                            {{ $service->category_name ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">
                            @if($service->price_before_discount > $service->base_price)
                                <del class="small text-secondary me-1">{{ number_format($service->price_before_discount, 2) }}</del>
                            @endif
                            <span>{{ number_format($service->base_price, 2) }}</span>
                            <span class="small text-secondary">{{ __('SAR') }}</span>
                        </div>
                    </td>
                    <td>
                        @if($service->is_active)
                            <span class="badge rounded-pill" style="background:#eef8f4; color:#0e6c51; border:1px solid #bde4d5; font-size:0.78rem; font-weight:600; padding:0.35rem 0.75rem;">
                                <i class="fa-solid fa-circle me-1" style="font-size:0.45rem; vertical-align:middle;"></i>{{ __('Active') }}
                            </span>
                        @else
                            <span class="badge rounded-pill" style="background:#fdf2f2; color:#b32a2a; border:1px solid #f4c7c7; font-size:0.78rem; font-weight:600; padding:0.35rem 0.75rem;">
                                <i class="fa-solid fa-circle me-1" style="font-size:0.45rem; vertical-align:middle;"></i>{{ __('Inactive') }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1.5 align-items-center">
                            @adminCan('admin.packages.services.edit')
                            <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" href="{{ route('admin.packages.services.edit', $service->id) }}" style="border-radius:8px; font-weight:600; padding:0.32rem 0.65rem; font-size:0.82rem;">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>{{ __('Edit') }}</span>
                            </a>
                            @endadminCan
                            @adminCan('admin.packages.services.destroy','DELETE')
                            @include('admin.partials.delete-button',['action'=>route('admin.packages.services.destroy',$service->id)])
                            @endadminCan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">{{ __('No services found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


</main>@endsection

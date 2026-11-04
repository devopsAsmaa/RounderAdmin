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
        <a class="nav-link {{ request()->routeIs('admin.service-categories.*') ? 'active' : '' }}" href="{{ route('admin.service-categories.index') }}">
            <i class="fa-solid fa-layer-group me-1"></i> {{ __('Service categories') }}
        </a>
    </li>
    @endadminCan
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}">
            <i class="fa-solid fa-screwdriver-wrench me-1"></i> {{ __('Available services') }}
        </a>
    </li>
    @adminCan('admin.sub-services.index')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.sub-services.*') ? 'active' : '' }}" href="{{ route('admin.sub-services.index') }}">
            <i class="fa-solid fa-list-check me-1"></i> {{ __('Sub services') }}
        </a>
    </li>
    @endadminCan
    @adminCan('admin.prices.index')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.prices.*') ? 'active' : '' }}" href="{{ route('admin.prices.index') }}">
            <i class="fa-solid fa-tags me-1"></i> {{ __('Prices') }}
        </a>
    </li>
    @endadminCan
</ul>
<div class="panel p-3 p-lg-4 mb-4">
    <h2 class="h5 fw-bold mb-3">{{ __('Available services') }}</h2>
    <div class="table-responsive">
        <table class="table align-middle" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">#</th>
                    <th style="width: 34%;">{{ __('Service') }}</th>
                    <th style="width: 18%;">{{ __('Category') }}</th>
                    <th style="width: 18%;">{{ __('Base price') }}</th>
                    <th style="width: 14%;">{{ __('Status') }}</th>
                    <th style="width: 16%;" class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                <tr>
                    <td class="text-center text-secondary small fw-bold">{{ $service->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            @if($service->image_path)
                                <img class="rounded-3 border flex-shrink-0" width="46" height="46" style="object-fit:cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($service->image_path) }}" alt="{{ __('Product image') }}">
                            @else
                                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: rgba(2, 51, 62, 0.05); color: var(--petrol); font-size: 1.15rem;">
                                    <i class="fa-solid fa-screwdriver-wrench"></i>
                                </div>
                            @endif
                            <div>
                                <div class="fw-bold text-dark fs-6">{{ $service->name ?? $service->code }}</div>
                                <div class="small text-secondary font-monospace" style="font-size: 0.78rem;">{{ $service->code }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge" style="background: rgba(2, 51, 62, 0.06); color: var(--petrol); font-weight: 600; padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.82rem;">
                            {{ $service->category_name ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">
                            @if($service->price_before_discount > $service->base_price)
                                <del class="small text-secondary me-1">{{ number_format($service->price_before_discount, 2) }}</del>
                            @endif
                            <span class="fs-6">{{ number_format($service->base_price, 2) }}</span>
                            <span class="small text-secondary fw-normal">{{ __('SAR') }}</span>
                        </div>
                    </td>
                    <td>
                        @if($service->is_active)
                            <span class="badge rounded-pill d-inline-flex align-items-center gap-2" style="background:#eef8f4; color:#0e6c51; border:1px solid #bde4d5; font-size:0.8rem; font-weight:600; padding:0.38rem 0.8rem;">
                                <span class="topbar-status-dot" style="width:6px;height:6px;"></span>
                                {{ __('Active') }}
                            </span>
                        @else
                            <span class="badge rounded-pill d-inline-flex align-items-center gap-2" style="background:#fdf2f2; color:#b32a2a; border:1px solid #f4c7c7; font-size:0.8rem; font-weight:600; padding:0.38rem 0.8rem;">
                                <span style="width:6px;height:6px;border-radius:50%;background:#dc3545;"></span>
                                {{ __('Inactive') }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex align-items-center" style="gap: 10px;">
                            @adminCan('admin.packages.services.edit')
                            <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" href="{{ route('admin.packages.services.edit', $service->id) }}" style="border-radius:8px; font-weight:600; padding:0.35rem 0.75rem; font-size:0.82rem;">
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

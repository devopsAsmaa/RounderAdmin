@extends('layouts.admin')
@section('title', __('Service categories').' · Rounder')
@section('body')
@php $locale = $locale ?? app()->getLocale(); @endphp
<main class="container py-4">
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="h3 mb-0">{{ __('Service categories') }}</h1>
    @adminCan('admin.service-categories.create')
    <a class="btn btn-primary" href="{{ route('admin.service-categories.create') }}">
        <i class="fa-solid fa-plus me-1"></i> {{ __('Create service category') }}
    </a>
    @endadminCan
</div>

@include('admin.partials.feedback')

<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('admin.service-categories.index') }}">
            <i class="fa-solid fa-layer-group me-1"></i> {{ __('Service categories') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link btn btn-outline-secondary" href="{{ route('admin.services.index') }}">
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
    <form method="get" class="row g-2 mb-3 align-items-center">
        <div class="col-md-5">
            <input name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="{{ __('Search by name or code') }}...">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>{{ __('All statuses') }}</option>
                <option value="active" @selected(($filters['status'] ?? null) === 'active')>{{ __('Active') }}</option>
                <option value="inactive" @selected(($filters['status'] ?? null) === 'inactive')>{{ __('Inactive') }}</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-primary w-100">
                <i class="fa-solid fa-filter me-1"></i> {{ __('Filter') }}
            </button>
        </div>
        @if(!empty($filters['search']) || !empty($filters['status']) && $filters['status'] !== 'all')
        <div class="col-md-2">
            <a href="{{ route('admin.service-categories.index') }}" class="btn btn-outline-secondary w-100">
                {{ __('Clear') }}
            </a>
        </div>
        @endif
    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th style="width: 80px;">{{ __('Image') }}</th>
                    <th>{{ __('Category name') }}</th>
                    <th>{{ __('Code') }}</th>
                    <th>{{ __('Services count') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td>
                        @if($category->image_path)
                            <img class="rounded border" width="48" height="48" style="object-fit:cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($category->image_path) }}" alt="{{ __('Category image') }}">
                        @else
                            <div class="rounded bg-light d-flex align-items-center justify-content-center border" style="width: 48px; height: 48px; color: var(--petrol);">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-bold">{{ $category->translations->firstWhere('locale', $locale)?->name ?? $category->code }}</div>
                        <div class="small text-secondary">
                            {{ $category->translations->pluck('name')->filter()->join(' · ') }}
                        </div>
                    </td>
                    <td><code>{{ $category->code }}</code></td>
                    <td>
                        <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-2 py-1">
                            {{ $category->services_count }} {{ __('Services') }}
                        </span>
                    </td>
                    <td>
                        @if($category->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">{{ __('Active') }}</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">{{ __('Inactive') }}</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex align-items-center" style="gap: 10px;">
                            @adminCan('admin.service-categories.edit')
                            <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" href="{{ route('admin.service-categories.edit', $category->id) }}" style="border-radius:8px; font-weight:600; padding:0.35rem 0.75rem; font-size:0.82rem;">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>{{ __('Edit') }}</span>
                            </a>
                            @endadminCan

                            @adminCan('admin.service-categories.destroy', 'DELETE')
                            @include('admin.partials.delete-button', ['action' => route('admin.service-categories.destroy', $category->id)])
                            @endadminCan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-secondary">
                        <i class="fa-solid fa-folder-open d-block mb-2 fs-3"></i>
                        {{ __('No service categories found') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
    <div class="mt-3">
        {{ $categories->links() }}
    </div>
    @endif
</div>
</main>
@endsection

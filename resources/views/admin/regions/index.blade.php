@extends('layouts.admin')
@section('title', __('Regions').' · Rounder')
@section('body')
<main class="container py-4">
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="h3 mb-0">{{ __('Regions') }}</h1>
    @adminCan('admin.regions.create')
    <a class="btn btn-primary" href="{{ route('admin.regions.create') }}">
        <i class="fa-solid fa-plus me-1"></i> {{ __('Create region') }}
    </a>
    @endadminCan
</div>

@include('admin.partials.feedback')

<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.regions.*') ? 'active' : '' }}" href="{{ route('admin.regions.index') }}">
            <i class="fa-solid fa-map-location-dot me-1"></i> {{ __('Regions') }}
        </a>
    </li>
    @adminCan('admin.cities.index')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.cities.*') ? 'active' : '' }}" href="{{ route('admin.cities.index') }}">
            <i class="fa-solid fa-tree-city me-1"></i> {{ __('Cities') }}
        </a>
    </li>
    @endadminCan
</ul>

<div class="panel p-3 p-lg-4 mb-4">
    <form method="get" class="row g-2 mb-3 align-items-center">
        <div class="col-md-5">
            <input name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="{{ __('Search by name') }}...">
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
        @if(!empty($filters['search']) || (!empty($filters['status']) && $filters['status'] !== 'all'))
        <div class="col-md-2">
            <a href="{{ route('admin.regions.index') }}" class="btn btn-outline-secondary w-100">
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
                    <th>{{ __('Region name') }}</th>
                    <th>{{ __('Linked cities count') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end" style="width: 140px;">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($regions as $region)
                    @php
                        $name = $region->translations->firstWhere('locale', $locale)?->name
                            ?? $region->translations->firstWhere('locale', 'ar')?->name
                            ?? $region->translations->first()?->name
                            ?? $region->code;
                        $enName = $region->translations->firstWhere('locale', 'en')?->name;
                    @endphp
                    <tr>
                        <td class="text-muted">{{ $region->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $name }}</div>
                            @if($enName && $enName !== $name)
                                <small class="text-muted">{{ $enName }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border">
                                <i class="fa-solid fa-city me-1"></i> {{ number_format($region->cities_count) }} {{ __('Cities') }}
                            </span>
                        </td>
                        <td>
                            @if($region->is_active)
                                <span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                @adminCan('admin.regions.edit')
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.regions.edit', $region->id) }}">
                                    {{ __('Edit') }}
                                </a>
                                @endadminCan
                                @adminCan('admin.regions.destroy', 'DELETE')
                                <form method="post" action="{{ route('admin.regions.destroy', $region->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this record?') }}');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" @disabled($region->cities_count > 0)>
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                                @endadminCan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            {{ __('No regions found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($regions->hasPages())
        <div class="mt-3">
            {{ $regions->links() }}
        </div>
    @endif
</div>
</main>
@endsection


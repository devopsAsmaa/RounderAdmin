@extends('layouts.admin')
@section('title', __('Cities').' · Rounder')
@section('body')
<main class="container py-4">
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="h3 mb-0">{{ __('Cities') }}</h1>
    @adminCan('admin.cities.create')
    <a class="btn btn-primary" href="{{ route('admin.cities.create') }}">
        <i class="fa-solid fa-plus me-1"></i> {{ __('Create city') }}
    </a>
    @endadminCan
</div>

@include('admin.partials.feedback')

<ul class="nav nav-pills mb-4 gap-2">
    @adminCan('admin.regions.index')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.regions.*') ? 'active' : '' }}" href="{{ route('admin.regions.index') }}">
            <i class="fa-solid fa-map-location-dot me-1"></i> {{ __('Regions') }}
        </a>
    </li>
    @endadminCan
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.cities.*') ? 'active' : '' }}" href="{{ route('admin.cities.index') }}">
            <i class="fa-solid fa-tree-city me-1"></i> {{ __('Cities') }}
        </a>
    </li>
</ul>

<div class="panel p-3 p-lg-4 mb-4">
    <form method="get" class="row g-2 mb-3 align-items-center">
        <div class="col-md-4">
            <input name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="{{ __('Search by name or code') }}...">
        </div>
        <div class="col-md-3">
            <select name="region_id" class="form-select">
                <option value="">{{ __('All regions') }}</option>
                @foreach($regions as $r)
                    @php
                        $rName = $r->translations->firstWhere('locale', $locale)?->name
                            ?? $r->translations->firstWhere('locale', 'ar')?->name
                            ?? $r->code;
                    @endphp
                    <option value="{{ $r->id }}" @selected(($filters['region_id'] ?? null) == $r->id)>
                        {{ $rName }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
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
        @if(!empty($filters['search']) || !empty($filters['region_id']) || (!empty($filters['status']) && $filters['status'] !== 'all'))
        <div class="col-md-1">
            <a href="{{ route('admin.cities.index') }}" class="btn btn-outline-secondary w-100">
                {{ __('Clear') }}
            </a>
        </div>
        @endif
    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th style="width: 70px;">#</th>
                    <th>{{ __('City name') }}</th>
                    <th>{{ __('Code') }}</th>
                    <th>{{ __('Region') }}</th>
                    <th>{{ __('Linked homes count') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end" style="width: 140px;">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cities as $city)
                    @php
                        $name = $city->translations->firstWhere('locale', $locale)?->name
                            ?? $city->translations->firstWhere('locale', 'ar')?->name
                            ?? $city->translations->first()?->name
                            ?? $city->code;
                        $enName = $city->translations->firstWhere('locale', 'en')?->name;
                        $regionName = $city->region?->translations->firstWhere('locale', $locale)?->name
                            ?? $city->region?->translations->firstWhere('locale', 'ar')?->name
                            ?? $city->region?->code;
                    @endphp
                    <tr>
                        <td class="text-muted">{{ $city->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $name }}</div>
                            @if($enName && $enName !== $name)
                                <small class="text-muted">{{ $enName }}</small>
                            @endif
                        </td>
                        <td><code>{{ $city->code }}</code></td>
                        <td>
                            @if($city->region)
                                <span class="badge bg-primary-subtle text-primary border">
                                    <i class="fa-solid fa-map-location-dot me-1"></i> {{ $regionName }}
                                </span>
                            @else
                                <span class="badge bg-light text-muted border">—</span>
                            @endif
                        </td>
                        <td>
                            @if($city->homes_count > 0)
                                <span class="badge bg-secondary-subtle text-secondary border">
                                    <i class="fa-solid fa-city me-1"></i> {{ number_format($city->homes_count) }} {{ __('Homes') }}
                                </span>
                            @else
                                <span class="text-muted small">0</span>
                            @endif
                        </td>
                        <td>
                            @if($city->is_active)
                                <span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                @adminCan('admin.cities.edit')
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.cities.edit', $city->id) }}">
                                    {{ __('Edit') }}
                                </a>
                                @endadminCan
                                @adminCan('admin.cities.destroy', 'DELETE')
                                <form method="post" action="{{ route('admin.cities.destroy', $city->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this record?') }}');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" @disabled($city->homes_count > 0)>
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                                @endadminCan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            {{ __('No cities found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($cities->hasPages())
        <div class="mt-3">
            {{ $cities->links() }}
        </div>
    @endif
</div>
</main>
@endsection


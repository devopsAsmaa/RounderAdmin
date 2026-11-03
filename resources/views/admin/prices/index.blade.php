@extends('layouts.admin')
@section('title', __('Prices').' · Rounder')
@section('body')
<main class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h3 mb-0">{{ __('Prices') }}</h1>
        @adminCan('admin.prices.create')
        <a class="btn btn-primary" href="{{ route('admin.prices.create') }}">
            <i class="fa-solid fa-plus me-1"></i> {{ __('Add price') }}
        </a>
        @endadminCan
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
        @adminCan('admin.sub-services.index')
        <li class="nav-item">
            <a class="nav-link btn btn-outline-secondary" href="{{ route('admin.sub-services.index') }}">
                <i class="fa-solid fa-list-check me-1"></i> {{ __('Sub services') }}
            </a>
        </li>
        @endadminCan
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.prices.index') }}">
                <i class="fa-solid fa-tags me-1"></i> {{ __('Prices') }}
            </a>
        </li>
    </ul>

    <div class="panel p-3 p-lg-4 mb-4">
        <form method="get" class="row g-2 mb-3 align-items-center">
            <div class="col-md-3">
                <select name="provider_profile_id" class="form-select">
                    <option value="">{{ __('All providers') }}</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}" @selected(($filters['provider_profile_id'] ?? null) == $provider->id)>
                            {{ $provider->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="service_id" class="form-select">
                    <option value="">{{ __('All services') }}</option>
                    @foreach($services as $service)
                        @php
                            $sName = $service->translations->firstWhere('locale', $locale)?->name 
                                ?? $service->translations->firstWhere('locale', 'ar')?->name 
                                ?? $service->code;
                        @endphp
                        <option value="{{ $service->id }}" @selected(($filters['service_id'] ?? null) == $service->id)>
                            {{ $sName }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="region_id" class="form-select">
                    <option value="">{{ __('All regions') }}</option>
                    @foreach($regions as $region)
                        @php
                            $rName = $region->translations->firstWhere('locale', $locale)?->name 
                                ?? $region->translations->firstWhere('locale', 'ar')?->name 
                                ?? $region->code;
                        @endphp
                        <option value="{{ $region->id }}" @selected(($filters['region_id'] ?? null) == $region->id)>
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
                <input name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="{{ __('Search') }}...">
            </div>
            <div class="col-md-1">
                <button class="btn btn-outline-primary w-100" title="{{ __('Filter') }}">
                    <i class="fa-solid fa-filter"></i>
                </button>
            </div>
            @if(!empty($filters['provider_profile_id']) || !empty($filters['service_id']) || !empty($filters['region_id']) || !empty($filters['search']) || (!empty($filters['status']) && $filters['status'] !== 'all'))
            <div class="col-12 mt-2">
                <a href="{{ route('admin.prices.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-xmark me-1"></i> {{ __('Clear') }}
                </a>
            </div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>{{ __('Provider') }}</th>
                        <th>{{ __('Service') }}</th>
                        <th>{{ __('Sub service') }}</th>
                        <th>{{ __('Region and cities') }}</th>
                        <th>{{ __('Price') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end" style="width: 140px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prices as $priceItem)
                        @php
                            $subName = $priceItem->subService?->translations->firstWhere('locale', $locale)?->name
                                ?? $priceItem->subService?->translations->firstWhere('locale', 'ar')?->name
                                ?? $priceItem->subService?->code;
                            $serviceName = $priceItem->subService?->service?->translations->firstWhere('locale', $locale)?->name
                                ?? $priceItem->subService?->service?->translations->firstWhere('locale', 'ar')?->name
                                ?? $priceItem->subService?->service?->code;
                            $regionName = $priceItem->region?->translations->firstWhere('locale', $locale)?->name
                                ?? $priceItem->region?->translations->firstWhere('locale', 'ar')?->name;
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $priceItem->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $priceItem->provider?->company_name ?? '—' }}</div>
                                @if($priceItem->provider?->user?->name)
                                    <small class="text-secondary">{{ $priceItem->provider->user->name }}</small>
                                @endif
                            </td>
                            <td>{{ $serviceName ?? '—' }}</td>
                            <td>
                                <span class="fw-semibold text-primary">{{ $subName ?? '—' }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $regionName ?? __('All regions') }}</div>
                                <button type="button" class="btn btn-sm btn-outline-secondary view-cities-btn py-0 px-2 mt-1" 
                                    data-price-id="{{ $priceItem->id }}"
                                    title="{{ __('View cities') }}">
                                    <i class="fa-solid fa-city me-1 text-primary"></i>
                                    <span class="fw-semibold">{{ number_format($priceItem->cities_count) }}</span> {{ __('Cities') }}
                                    <i class="fa-solid fa-eye ms-1 text-muted"></i>
                                </button>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success fs-6 border">
                                    {{ number_format($priceItem->price, 2) }} {{ __('SAR') }}
                                </span>
                            </td>
                            <td>
                                @if($priceItem->is_active)
                                    <span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    @adminCan('admin.prices.edit')
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.prices.edit', $priceItem->id) }}">
                                        {{ __('Edit') }}
                                    </a>
                                    @endadminCan
                                    @adminCan('admin.prices.destroy', 'DELETE')
                                    @include('admin.partials.delete-button', ['action' => route('admin.prices.destroy', $priceItem->id)])
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-secondary">
                                {{ __('No prices found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $prices->links() }}
        </div>
    </div>
</main>

{{-- نافذة منبثقة لعرض المدن المشمولة بالتسعير --}}
<div class="modal fade" id="citiesModal" tabindex="-1" aria-labelledby="citiesModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="citiesModalTitle">
                        <i class="fa-solid fa-city text-primary me-2"></i> {{ __('Covered cities') }}
                    </h5>
                    <div class="small text-muted" id="citiesModalSubtitle"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" id="modalCitySearch" class="form-control" placeholder="{{ __('Search city') }}...">
                    </div>
                </div>

                <div id="modalLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="text-muted small mt-2">{{ __('Loading...') }}</div>
                </div>

                <div id="modalCitiesList" class="d-flex flex-wrap gap-2 d-none" style="max-height: 400px; overflow-y: auto;">
                    {{-- يتم رندر المدن هنا كبطاقات --}}
                </div>

                <div id="modalEmptySearch" class="text-center py-4 text-muted small d-none">
                    <i class="fa-solid fa-circle-exclamation me-1"></i> {{ __('No matching cities found') }}
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center">
                <span class="text-muted small" id="modalCitiesSummary"></span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('citiesModal');
    const citiesModal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const modalSubtitle = document.getElementById('citiesModalSubtitle');
    const modalLoading = document.getElementById('modalLoading');
    const modalCitiesList = document.getElementById('modalCitiesList');
    const modalCitySearch = document.getElementById('modalCitySearch');
    const modalCitiesSummary = document.getElementById('modalCitiesSummary');
    const modalEmptySearch = document.getElementById('modalEmptySearch');

    document.querySelectorAll('.view-cities-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const priceId = this.dataset.priceId;
            openCitiesModal(priceId);
        });
    });

    function openCitiesModal(priceId) {
        modalCitySearch.value = '';
        modalSubtitle.textContent = '';
        modalCitiesSummary.textContent = '';
        modalCitiesList.innerHTML = '';
        modalCitiesList.classList.add('d-none');
        modalEmptySearch.classList.add('d-none');
        modalLoading.classList.remove('d-none');

        citiesModal.show();

        fetch(`{{ url('admin/prices') }}/${priceId}/cities`)
            .then(res => res.json())
            .then(data => {
                modalLoading.classList.add('d-none');
                modalSubtitle.textContent = `${data.provider_name} · ${data.sub_service_name} (${data.region_name})`;
                modalCitiesSummary.textContent = `{{ __('Total covered cities') }}: ${data.cities_count}`;

                if (!data.cities.length) {
                    modalCitiesList.innerHTML = '<span class="text-muted small">{{ __('No cities found') }}</span>';
                    modalCitiesList.classList.remove('d-none');
                    return;
                }

                data.cities.forEach(city => {
                    const chip = document.createElement('span');
                    chip.className = 'badge bg-light text-dark border p-2 modal-city-chip d-inline-flex align-items-center gap-1';
                    chip.dataset.name = city.name.toLowerCase();
                    chip.innerHTML = `<i class="fa-solid fa-location-dot text-primary me-1" style="font-size:0.8rem;"></i>${city.name}`;
                    modalCitiesList.appendChild(chip);
                });

                modalCitiesList.classList.remove('d-none');
            })
            .catch(() => {
                modalLoading.classList.add('d-none');
                modalCitiesList.innerHTML = '<div class="alert alert-danger w-100">{{ __('An error occurred while loading data.') }}</div>';
                modalCitiesList.classList.remove('d-none');
            });
    }

    modalCitySearch.addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();
        let visibleCount = 0;

        modalCitiesList.querySelectorAll('.modal-city-chip').forEach(chip => {
            const name = chip.dataset.name;
            if (!term || name.includes(term)) {
                chip.classList.remove('d-none');
                visibleCount++;
            } else {
                chip.classList.add('d-none');
            }
        });

        if (visibleCount === 0 && term) {
            modalEmptySearch.classList.remove('d-none');
        } else {
            modalEmptySearch.classList.add('d-none');
        }
    });
});
</script>
@endsection

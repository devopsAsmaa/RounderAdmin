@extends('layouts.provider')
@section('title', $price ? __('Edit and resubmit price') : __('Add price'))
@section('body')
@php
    $locale = app()->getLocale();
    $regionsData = $regions->mapWithKeys(function ($region) use ($locale) {
        return [
            $region->id => [
                'id' => $region->id,
                'name' => $region->translations->firstWhere('locale', $locale)?->name 
                    ?? $region->translations->firstWhere('locale', 'ar')?->name 
                    ?? $region->code,
                'cities' => $region->cities->map(function ($city) use ($locale) {
                    return [
                        'id' => $city->id,
                        'name' => $city->translations->firstWhere('locale', $locale)?->name 
                            ?? $city->translations->firstWhere('locale', 'ar')?->name 
                            ?? $city->code,
                    ];
                })->values()->all(),
            ],
        ];
    })->all();
    $oldCityIds = collect(old('city_ids', $price?->cities->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
@endphp

<div class="mb-4">
    <h1 class="h3 fw-bold">{{ $price ? __('Edit and resubmit price') : __('Submit a service price') }}</h1>
    <p class="text-secondary mb-0">{{ __('Prices are sent to the administration for approval before they become available to customers.') }}</p>
</div>

<section class="panel p-3 p-lg-4">
    <form id="providerPriceForm" method="post" action="{{ $price ? route('provider.prices.update', $price->id) : route('provider.prices.store') }}">
        @csrf
        @if($price) @method('put') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="service_id">
                    <i class="fa-solid fa-briefcase text-primary me-1"></i> {{ __('Service') }} <span class="text-danger">*</span>
                </label>
                <select class="form-select @error('service_id') is-invalid @enderror" id="service_id" name="service_id" required>
                    <option value="">{{ __('Choose a service') }}</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected(old('service_id', $price?->subService?->service_id) == $service->id)>
                            {{ $service->translations->firstWhere('locale', $locale)?->name ?? $service->translations->firstWhere('locale', 'ar')?->name ?? $service->code }}
                        </option>
                    @endforeach
                </select>
                @error('service_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold" for="sub_service_id">
                    <i class="fa-solid fa-layer-group text-primary me-1"></i> {{ __('Sub service') }} <span class="text-danger">*</span>
                </label>
                <select class="form-select @error('sub_service_id') is-invalid @enderror" id="sub_service_id" name="sub_service_id" required>
                    <option value="">{{ __('Choose a sub service') }}</option>
                    @foreach($subServices as $subService)
                        <option value="{{ $subService->id }}" data-service-id="{{ $subService->service_id }}" @selected(old('sub_service_id', $price?->sub_service_id) == $subService->id)>
                            {{ $subService->translations->firstWhere('locale', $locale)?->name ?? $subService->translations->firstWhere('locale', 'ar')?->name ?? $subService->code }}
                        </option>
                    @endforeach
                </select>
                @error('sub_service_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold" for="region_id">
                    <i class="fa-solid fa-map text-primary me-1"></i> {{ __('Region') }} <span class="text-danger">*</span>
                </label>
                <select class="form-select @error('region_id') is-invalid @enderror" id="region_id" name="region_id" required>
                    <option value="">{{ __('Choose a region') }}</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}" @selected(old('region_id', $price?->region_id) == $region->id)>
                            {{ $region->translations->firstWhere('locale', $locale)?->name ?? $region->translations->firstWhere('locale', 'ar')?->name ?? $region->code }}
                        </option>
                    @endforeach
                </select>
                @error('region_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold" for="price">
                    <i class="fa-solid fa-coins text-warning me-1"></i> {{ __('Price') }} ({{ __('SAR') }}) <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input class="form-control @error('price') is-invalid @enderror" id="price" name="price" type="number" min="0" max="999999.99" step="0.01" value="{{ old('price', $price?->price) }}" placeholder="0.00" required>
                    <span class="input-group-text">{{ __('SAR') }}</span>
                </div>
                @error('price')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- المدن المشمولة بالتسعير بتصميم بطاقة الأدمن المتطابق --}}
            <div class="col-12" id="citiesCardWrapper">
                <div class="card border">
                    <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold">
                                <i class="fa-solid fa-city text-primary me-1"></i> {{ __('Cities') }} <span class="text-danger">*</span>
                            </span>
                            <span class="badge bg-primary rounded-pill" id="selectedCitiesCount">0</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success" id="btnSelectAll" disabled>
                                <i class="fa-solid fa-check-double me-1"></i> {{ __('Select all') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeselectAll" disabled>
                                <i class="fa-solid fa-ban me-1"></i> {{ __('Deselect all') }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <input type="text" id="citySearchInput" class="form-control form-control-sm" placeholder="{{ __('Search city') }}" disabled>
                            </div>
                            <div class="col-md-6 text-muted small d-flex align-items-center">
                                <i class="fa-solid fa-circle-info me-1 text-info"></i>
                                {{ __('All cities are selected by default. Click on (x) or click any city to exclude it.') }}
                            </div>
                        </div>

                        <div id="citiesPlaceholder" class="text-center py-4 text-muted small">
                            <i class="fa-solid fa-arrow-up me-1"></i> {{ __('Please select a region to load its cities.') }}
                        </div>

                        <div id="citiesContainer" class="d-flex flex-wrap gap-2 pt-2" style="max-height: 280px; overflow-y: auto;">
                            {{-- يتم تعبئة بطاقات المدن هنا ديناميكياً بواسطة JavaScript --}}
                        </div>
                        <input type="hidden" name="city_ids_json" id="cityIdsJson">

                        @error('city_ids')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button class="btn btn-orange" type="submit">{{ $price ? __('Resubmit for approval') : __('Submit for approval') }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('provider.prices.index') }}">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const regionsData = @json($regionsData);
    const oldCityIds = @json($oldCityIds);

    const serviceSelect = document.getElementById('service_id');
    const subServiceSelect = document.getElementById('sub_service_id');
    const regionSelect = document.getElementById('region_id');

    const citiesContainer = document.getElementById('citiesContainer');
    const citiesPlaceholder = document.getElementById('citiesPlaceholder');
    const selectedCitiesCount = document.getElementById('selectedCitiesCount');
    const btnSelectAll = document.getElementById('btnSelectAll');
    const btnDeselectAll = document.getElementById('btnDeselectAll');
    const citySearchInput = document.getElementById('citySearchInput');
    const providerPriceForm = document.getElementById('providerPriceForm');
    const cityIdsJson = document.getElementById('cityIdsJson');

    let initialLoadDone = false;

    // فلترة الخدمات الفرعية حسب الخدمة المختارة
    const filterSubServices = () => {
        let selected = false;
        Array.from(subServiceSelect.options).forEach(option => {
            if (!option.value) return;
            const visible = option.dataset.serviceId === serviceSelect.value;
            option.hidden = !visible;
            if (!visible && option.selected) option.selected = false;
            if (visible && option.selected) selected = true;
        });
        if (!selected && subServiceSelect.value) {
            subServiceSelect.value = '';
        }
    };

    // تحديث عدد المدن المحددة
    function updateCitiesCount() {
        const checked = citiesContainer.querySelectorAll('.city-checkbox:checked').length;
        selectedCitiesCount.textContent = checked;
    }

    // تبديل حالة المدينة (محددة / غير محددة)
    function toggleCity(chip) {
        const checkbox = chip.querySelector('.city-checkbox');
        const icon = chip.querySelector('.remove-icon');
        checkbox.checked = !checkbox.checked;

        if (checkbox.checked) {
            chip.classList.remove('bg-light', 'text-secondary', 'border-secondary');
            chip.classList.add('bg-primary', 'text-white', 'border-primary');
            icon.classList.remove('fa-plus');
            icon.classList.add('fa-xmark');
        } else {
            chip.classList.remove('bg-primary', 'text-white', 'border-primary');
            chip.classList.add('bg-light', 'text-secondary', 'border-secondary');
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-plus');
        }
        updateCitiesCount();
    }

    // رندر بطاقات المدن
    function renderCities(cities, useOldSelection = false) {
        citiesContainer.innerHTML = '';
        if (!cities || !cities.length) {
            citiesContainer.innerHTML = '<span class="text-muted small p-2">{{ __('No cities in this region') }}</span>';
            updateCitiesCount();
            return;
        }

        const fragment = document.createDocumentFragment();
        cities.forEach(city => {
            let isSelected = true;
            if (useOldSelection && oldCityIds.length > 0) {
                isSelected = oldCityIds.includes(Number(city.id)) || oldCityIds.includes(String(city.id));
            }

            const chip = document.createElement('div');
            chip.className = `city-chip badge rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 border ${isSelected ? 'bg-primary text-white border-primary' : 'bg-light text-secondary border-secondary'}`;
            chip.style.cursor = 'pointer';
            chip.dataset.id = city.id;
            chip.dataset.name = (city.name || '').toLowerCase();

            chip.innerHTML = `
                <span class="city-name">${city.name}</span>
                <i class="fa-solid ${isSelected ? 'fa-xmark' : 'fa-plus'} remove-icon ms-1" style="font-size: 0.85rem;"></i>
                <input type="checkbox" name="city_ids[]" value="${city.id}" ${isSelected ? 'checked' : ''} class="d-none city-checkbox">
            `;

            chip.addEventListener('click', () => toggleCity(chip));
            fragment.appendChild(chip);
        });

        citiesContainer.appendChild(fragment);
        updateCitiesCount();
    }

    // عند تغيير المنطقة
    function handleRegionChange(isInitial = false) {
        const regionId = regionSelect.value;
        citiesContainer.innerHTML = '';
        updateCitiesCount();

        if (!regionId || !regionsData[regionId]) {
            citiesPlaceholder.classList.remove('d-none');
            btnSelectAll.disabled = true;
            btnDeselectAll.disabled = true;
            citySearchInput.disabled = true;
            citySearchInput.value = '';
            return;
        }

        citiesPlaceholder.classList.add('d-none');
        btnSelectAll.disabled = false;
        btnDeselectAll.disabled = false;
        citySearchInput.disabled = false;
        citySearchInput.value = '';

        const cities = regionsData[regionId].cities || [];
        renderCities(cities, isInitial);
    }

    // زر تحديد الكل
    btnSelectAll.addEventListener('click', () => {
        citiesContainer.querySelectorAll('.city-chip').forEach(chip => {
            const checkbox = chip.querySelector('.city-checkbox');
            const icon = chip.querySelector('.remove-icon');
            checkbox.checked = true;
            chip.classList.remove('bg-light', 'text-secondary', 'border-secondary');
            chip.classList.add('bg-primary', 'text-white', 'border-primary');
            icon.classList.remove('fa-plus');
            icon.classList.add('fa-xmark');
        });
        updateCitiesCount();
    });

    // زر إلغاء تحديد الكل
    btnDeselectAll.addEventListener('click', () => {
        citiesContainer.querySelectorAll('.city-chip').forEach(chip => {
            const checkbox = chip.querySelector('.city-checkbox');
            const icon = chip.querySelector('.remove-icon');
            checkbox.checked = false;
            chip.classList.remove('bg-primary', 'text-white', 'border-primary');
            chip.classList.add('bg-light', 'text-secondary', 'border-secondary');
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-plus');
        });
        updateCitiesCount();
    });

    // البحث اللحظي بالمدن
    citySearchInput.addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();
        citiesContainer.querySelectorAll('.city-chip').forEach(chip => {
            const name = chip.dataset.name || '';
            if (!term || name.includes(term)) {
                chip.classList.remove('d-none');
            } else {
                chip.classList.add('d-none');
            }
        });
    });

    // التحقق من النموذج عند الحفظ
    providerPriceForm.addEventListener('submit', function (e) {
        if (!regionSelect.value) {
            e.preventDefault();
            alert('{{ __('Please select a region.') }}');
            return false;
        }

        const checkedBoxes = citiesContainer.querySelectorAll('.city-checkbox:checked');
        const ids = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
        if (ids.length === 0) {
            e.preventDefault();
            alert('{{ __('Please select at least one city.') }}');
            return false;
        }
        cityIdsJson.value = JSON.stringify(ids);
    });

    // أحداث التغيير
    serviceSelect.addEventListener('change', filterSubServices);
    regionSelect.addEventListener('change', () => handleRegionChange(false));

    // تهيئة الصفحة
    filterSubServices();
    if (regionSelect.value) {
        handleRegionChange(true);
    }
});
</script>
@endsection

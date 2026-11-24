@extends('layouts.provider')
@section('title', __('Prices'))
@section('body')
<div class="mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h1 class="h3 fw-bold">{{ __('Prices') }}</h1>
            <p class="text-secondary mb-0">{{ __('Submit service prices by region for admin review.') }}</p>
        </div>
        @if(app(\App\Modules\Providers\Services\ProviderPortalService::class)->allows(auth('provider')->user(), app(\App\Modules\Providers\Services\ProviderPortalService::class)->profileFor(auth('provider')->user()), 'prices.manage'))
            <a class="btn btn-orange" href="{{ route('provider.prices.create') }}">
                <i class="fa-solid fa-plus me-1"></i>{{ __('Add price') }}
            </a>
        @endif
    </div>
</div>
<section class="panel p-3 p-lg-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>{{ __('Service') }}</th>
                    <th>{{ __('Sub service') }}</th>
                    <th>{{ __('Region') }}</th>
                    <th>{{ __('Cities') }}</th>
                    <th>{{ __('Price') }}</th>
                    <th>{{ __('Approval') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($prices as $price)
                @php
                    $locale = app()->getLocale();
                    $subService = $price->subService;
                    $region = $price->region;
                    $citiesData = $price->cities->map(function ($c) use ($locale) {
                        return [
                            'id' => $c->id,
                            'name' => $c->translations->firstWhere('locale', $locale)?->name
                                ?? $c->translations->firstWhere('locale', 'ar')?->name
                                ?? $c->code,
                        ];
                    })->sortBy('name')->values();
                    $serviceName = $subService?->service?->translations?->firstWhere('locale', $locale)?->name ?? $subService?->service?->code ?? '—';
                    $subName = $subService?->translations?->firstWhere('locale', $locale)?->name ?? $subService?->code ?? '—';
                    $regionName = $region?->translations?->firstWhere('locale', $locale)?->name ?? __('All regions');
                @endphp
                <tr>
                    <td>{{ $serviceName }}</td>
                    <td><span class="fw-semibold text-primary">{{ $subName }}</span></td>
                    <td><div class="fw-semibold">{{ $regionName }}</div></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-secondary view-cities-btn py-1 px-2" 
                            data-cities='@json($citiesData)'
                            data-service="{{ $subName }}"
                            data-region="{{ $regionName }}"
                            data-count="{{ $citiesData->count() }}"
                            title="{{ __('View cities') }}">
                            <i class="fa-solid fa-city me-1 text-primary"></i>
                            <span class="fw-semibold">{{ number_format($citiesData->count()) }}</span> {{ __('Cities') }}
                            <i class="fa-solid fa-eye ms-1 text-muted" style="font-size:0.75rem;"></i>
                        </button>
                    </td>
                    <td>
                        <span class="badge bg-success-subtle text-success fs-6 border">
                            {{ number_format((float) $price->price, 2) }} {{ __('SAR') }}
                        </span>
                    </td>
                    <td>
                        @php($approvalLabels = ['pending' => __('Pending approval'), 'approved' => __('Approved'), 'rejected' => __('Rejected')])
                        <span class="badge {{ $price->approval_status === 'approved' ? 'bg-success-subtle text-success' : ($price->approval_status === 'rejected' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning-emphasis') }}">
                            {{ $approvalLabels[$price->approval_status] ?? $price->approval_status }}
                        </span>
                        @if($price->rejection_reason)
                            <div class="small text-danger mt-1">{{ $price->rejection_reason }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $price->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                            {{ $price->is_active ? __('Active') : __('Inactive') }}
                        </span>
                    </td>
                    <td class="text-end">
                        @if($price->approval_status === 'rejected')
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('provider.prices.edit', $price->id) }}">{{ __('Edit and resubmit') }}</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-secondary py-4">{{ __('No prices have been submitted for your account.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $prices->links() }}</div>
</section>

{{-- نافذة منبثقة لعرض المدن المشمولة بالتسعير --}}
<div class="modal fade" id="citiesModal" tabindex="-1" aria-labelledby="citiesModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="citiesModalTitle">
                        <i class="fa-solid fa-city text-primary me-2"></i> {{ __('Covered cities') }}
                    </h5>
                    <div class="small text-muted mt-1" id="citiesModalSubtitle"></div>
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

                <div id="modalCitiesList" class="d-flex flex-wrap gap-2" style="max-height: 400px; overflow-y: auto;">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('citiesModal');
    if (!modalElement) return;
    const citiesModal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const modalSubtitle = document.getElementById('citiesModalSubtitle');
    const modalCitiesList = document.getElementById('modalCitiesList');
    const modalCitySearch = document.getElementById('modalCitySearch');
    const modalCitiesSummary = document.getElementById('modalCitiesSummary');
    const modalEmptySearch = document.getElementById('modalEmptySearch');

    document.querySelectorAll('.view-cities-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            let cities = [];
            try {
                cities = JSON.parse(this.dataset.cities || '[]');
            } catch (e) {
                cities = [];
            }
            const service = this.dataset.service || '';
            const region = this.dataset.region || '';
            const count = this.dataset.count || cities.length;

            modalCitySearch.value = '';
            modalSubtitle.textContent = `${service} (${region})`;
            modalCitiesSummary.textContent = `{{ __('Total covered cities') }}: ${count}`;
            modalCitiesList.innerHTML = '';
            modalEmptySearch.classList.add('d-none');

            if (!cities.length) {
                modalCitiesList.innerHTML = '<span class="text-muted small">{{ __('No cities found') }}</span>';
                citiesModal.show();
                return;
            }

            const fragment = document.createDocumentFragment();
            cities.forEach(city => {
                const chip = document.createElement('span');
                chip.className = 'badge bg-light text-dark border p-2 modal-city-chip d-inline-flex align-items-center gap-1';
                chip.dataset.name = (city.name || '').toLowerCase();
                chip.innerHTML = `<i class="fa-solid fa-location-dot text-primary me-1" style="font-size:0.8rem;"></i>${city.name}`;
                fragment.appendChild(chip);
            });
            modalCitiesList.appendChild(fragment);

            citiesModal.show();
        });
    });

    if (modalCitySearch) {
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
    }
});
</script>
@endpush
@endsection

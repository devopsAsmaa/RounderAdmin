@extends('layouts.admin')
@section('title', __('Provider accounts & balances').' · Rounder')

@section('body')
<style>
    .nav-pills-orange .nav-link.active {
        background: var(--orange) !important;
        color: #fff !important;
        box-shadow: 0 4px 14px rgba(251, 130, 47, 0.35) !important;
        border-color: var(--orange) !important;
    }
    .nav-pills-orange .nav-link {
        color: var(--petrol);
        background: #fff;
        border: 1px solid #dce6e3;
        transition: all .15s ease;
    }
    .nav-pills-orange .nav-link:hover:not(.active) {
        background: #fff8f3;
        color: var(--orange);
        border-color: #ffd4b8;
    }
    .btn-orange {
        background: var(--orange) !important;
        border-color: var(--orange) !important;
        color: #fff !important;
        box-shadow: 0 3px 10px rgba(251, 130, 47, 0.28) !important;
    }
    .btn-orange:hover {
        background: var(--orange-hover) !important;
        border-color: var(--orange-hover) !important;
        color: #fff !important;
    }
    .btn-outline-orange {
        color: var(--orange) !important;
        border-color: rgba(251, 130, 47, 0.45) !important;
        background: #fff !important;
    }
    .btn-outline-orange:hover {
        background: var(--orange) !important;
        border-color: var(--orange) !important;
        color: #fff !important;
    }
    .badge-orange {
        background: #fff3eb !important;
        color: #d95d08 !important;
        border: 1px solid #ffd4b8 !important;
    }
    .card-kpi-orange {
        border-top: 3.5px solid var(--orange) !important;
    }
    .filter-dues-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.45rem 1rem;
        border-radius: 50rem;
        background: #f8fafb;
        border: 1.5px solid #dce6e3;
        transition: all 0.2s ease;
        user-select: none;
    }
    .filter-dues-pill:hover {
        border-color: #ffd4b8;
        background: #fff8f3;
    }
    .filter-dues-pill.active {
        background: #fff3eb !important;
        border-color: var(--orange) !important;
        box-shadow: 0 2px 8px rgba(251, 130, 47, 0.18) !important;
    }
    .filter-dues-pill .form-check-input {
        cursor: pointer;
        width: 2.1rem;
        height: 1.15rem;
    }
    .filter-dues-pill .form-check-input:checked {
        background-color: var(--orange) !important;
        border-color: var(--orange) !important;
    }
</style>

<main class="container py-4">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-invoice-dollar text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Provider accounts & balances') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Track provider balances, total accrued commissions, settled and remaining dues.') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.providers.commissions.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fa-solid fa-list-check me-1"></i> {{ __('Detailed commissions ledger') }}
            </a>
            <a href="{{ route('admin.providers.index') }}" class="btn btn-light shadow-sm">
                <i class="fa-solid fa-handshake me-1"></i> {{ __('Providers list') }}
            </a>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="panel p-3 h-100 shadow-sm border-0 d-flex align-items-center gap-3" style="border-radius: 14px; background: #fff;">
                <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 52px; height: 52px; background: #e8f4f2; color: var(--petrol);">
                    <i class="fa-solid fa-cash-register fa-xl"></i>
                </div>
                <div>
                    <div class="text-secondary small fw-semibold">{{ __('Total orders value') }}</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($kpis['total_gross'], 2) }} <span class="fs-6 fw-normal text-muted">{{ __('SAR') }}</span></div>
                    <div class="text-muted" style="font-size: 0.78rem;">{{ __('Across :count completed orders', ['count' => $kpis['total_orders']]) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="panel p-3 h-100 shadow-sm border-0 d-flex align-items-center gap-3 card-kpi-orange" style="border-radius: 14px; background: #fff;">
                <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 52px; height: 52px; background: #fff3eb; color: var(--orange);">
                    <i class="fa-solid fa-percent fa-xl"></i>
                </div>
                <div>
                    <div class="text-secondary small fw-semibold">{{ __('Total platform commissions') }}</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($kpis['total_commission'], 2) }} <span class="fs-6 fw-normal text-muted">{{ __('SAR') }}</span></div>
                    <div class="text-muted" style="font-size: 0.78rem;">{{ __('Total contractual commissions') }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="panel p-3 h-100 shadow-sm border-0 d-flex align-items-center gap-3" style="border-radius: 14px; background: #fff;">
                <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 52px; height: 52px; background: #ecfdf5; color: #059669;">
                    <i class="fa-solid fa-circle-check fa-xl"></i>
                </div>
                <div>
                    <div class="text-secondary small fw-semibold">{{ __('Settled commissions') }}</div>
                    <div class="fs-4 fw-bold text-success">{{ number_format($kpis['total_settled'], 2) }} <span class="fs-6 fw-normal text-muted">{{ __('SAR') }}</span></div>
                    <div class="text-muted" style="font-size: 0.78rem;">{{ __('Received and settled') }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="panel p-3 h-100 shadow-sm border-0 d-flex align-items-center gap-3 card-kpi-orange" style="border-radius: 14px; background: #fff8f3;">
                <div class="d-flex align-items-center justify-content-center rounded-3 shadow-sm" style="width: 52px; height: 52px; background: var(--orange); color: #fff;">
                    <i class="fa-solid fa-clock-rotate-left fa-xl"></i>
                </div>
                <div>
                    <div class="text-secondary small fw-semibold">{{ __('Outstanding dues on providers') }}</div>
                    <div class="fs-4 fw-bold" style="color: #c45707;">{{ number_format($kpis['total_pending'], 2) }} <span class="fs-6 fw-normal text-muted">{{ __('SAR') }}</span></div>
                    <div class="text-muted" style="font-size: 0.78rem;">{{ __('Pending dues to collect') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills nav-pills-orange mb-3 gap-2">
        <li class="nav-item">
            <a class="nav-link active fw-bold px-4 py-2 rounded-3" href="{{ route('admin.providers.finance.index') }}">
                <i class="fa-solid fa-scale-balanced me-1"></i> {{ __('Provider accounts & balances') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link fw-semibold px-4 py-2 rounded-3" href="{{ route('admin.providers.commissions.index') }}">
                <i class="fa-solid fa-table-list me-1"></i> {{ __('Commissions ledger') }}
            </a>
        </li>
    </ul>

    {{-- Main Panel and Table --}}
    <div class="panel p-3 p-lg-4 shadow-sm" style="border-radius: 16px; background: #fff;">
        {{-- Search and Filters Bar --}}
        <form method="get" action="{{ route('admin.providers.finance.index') }}" class="mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                {{-- Search Box --}}
                <div class="flex-grow-1" style="max-width: 480px;">
                    <div class="input-group shadow-sm" style="border-radius: 12px; overflow: hidden; border: 1px solid #dce6e3;">
                        <span class="input-group-text bg-white border-0 text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-0 py-2"
                               value="{{ request('q') }}"
                               placeholder="{{ __('Search company, contact, phone...') }}">
                        @if(request('q'))
                            <a href="{{ route('admin.providers.finance.index', request('has_dues') ? ['has_dues' => 1] : []) }}" 
                               class="btn btn-white border-0 text-muted d-flex align-items-center px-2" style="background:#fff;" title="{{ __('Reset') }}">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-orange px-3 fw-semibold border-0">
                            {{ __('Search') }}
                        </button>
                    </div>
                </div>

                {{-- Filter Pill Toggle --}}
                <div class="d-flex align-items-center gap-2">
                    <label class="filter-dues-pill shadow-sm cursor-pointer m-0 {{ request('has_dues') ? 'active' : '' }}" for="hasDuesSwitch">
                        <div class="form-check form-switch m-0 p-0 d-flex align-items-center">
                            <input class="form-check-input m-0 cursor-pointer" type="checkbox" name="has_dues" value="1" 
                                   id="hasDuesSwitch" {{ request('has_dues') ? 'checked' : '' }} onchange="this.form.submit()">
                        </div>
                        <span class="fw-semibold small {{ request('has_dues') ? 'text-dark' : 'text-secondary' }}">
                            <i class="fa-solid fa-coins me-1 {{ request('has_dues') ? 'text-rounder-orange' : 'text-muted' }}"></i>
                            {{ __('Providers with pending dues only') }}
                        </span>
                    </label>

                    @if(request('q') || request('has_dues'))
                        <a href="{{ route('admin.providers.finance.index') }}" class="btn btn-light btn-sm rounded-pill text-secondary border px-3 py-1.5 shadow-sm" title="{{ __('Reset') }}">
                            <i class="fa-solid fa-rotate-left me-1"></i> {{ __('Reset') }}
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Provider Balances Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 25%;">{{ __('Provider / Company') }}</th>
                        <th style="width: 17%;">{{ __('Contact & details') }}</th>
                        <th class="text-center" style="width: 11%;">{{ __('Contract rates') }}</th>
                        <th class="text-end" style="width: 10%;">{{ __('Total orders') }}</th>
                        <th class="text-end" style="width: 11%;">{{ __('Total commissions') }}</th>
                        <th class="text-end" style="width: 10%;">{{ __('Settled') }}</th>
                        <th class="text-end pe-3" style="width: 12%;">{{ __('Pending dues') }}</th>
                        <th class="text-center" style="width: 14%;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($providers as $provider)
                        @php
                            $hasPending = (float)$provider->pending_total > 0;
                        @endphp
                        <tr>
                            <td class="ps-3 py-3">
                                <div class="fw-bold text-dark">{{ $provider->company_name }}</div>
                                @if($provider->trade_name && $provider->trade_name !== $provider->company_name)
                                    <div class="text-muted small">{{ $provider->trade_name }}</div>
                                @endif
                                <div class="small text-muted mt-0.5">
                                    <span class="badge bg-light text-secondary border">#{{ $provider->provider_id }}</span>
                                    @if($provider->contract_reference)
                                        <span class="badge bg-light text-secondary border ms-1">{{ $provider->contract_reference }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $provider->contact_name ?: '—' }}</div>
                                @if($provider->contact_phone)
                                    <div class="small text-muted mt-1 d-inline-flex align-items-center gap-1">
                                        <i class="fa-solid fa-phone text-secondary opacity-75" style="font-size: 0.72rem;"></i>
                                        <span dir="ltr" class="font-monospace">{{ $provider->contact_phone }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column gap-1 align-items-center">
                                    <span class="badge bg-light text-dark border px-2 py-1 text-nowrap" style="font-size: 0.78rem;" title="{{ __('Regular service commission') }}">
                                        <span class="text-muted fw-normal">{{ __('Regular') }}:</span>
                                        <strong class="font-monospace ms-1">{{ $provider->service_commission_percent !== null ? $provider->service_commission_percent.'%' : '—' }}</strong>
                                    </span>
                                    <span class="badge px-2 py-1 text-nowrap" style="font-size: 0.78rem; background: #fff3eb; color: #d95d08; border: 1px solid #ffd4b8;" title="{{ __('Emergency commission') }}">
                                        <i class="fa-solid fa-bolt text-rounder-orange me-0.5"></i>
                                        <span class="fw-normal">{{ __('Emergency') }}:</span>
                                        <strong class="font-monospace ms-1">{{ $provider->emergency_commission_percent !== null ? $provider->emergency_commission_percent.'%' : '—' }}</strong>
                                    </span>
                                </div>
                            </td>
                            <td class="text-end font-monospace">
                                <div class="fw-semibold text-dark">{{ number_format((float)$provider->gross_total, 2) }}</div>
                                <div class="text-muted small" style="font-size:0.75rem;">{{ $provider->orders_count }} {{ __('orders') }}</div>
                            </td>
                            <td class="text-end font-monospace">
                                <div class="fw-semibold text-dark">{{ number_format((float)$provider->commission_total, 2) }}</div>
                                <div class="text-muted small" style="font-size:0.75rem;">{{ __('SAR') }}</div>
                            </td>
                            <td class="text-end font-monospace">
                                <div class="fw-semibold text-success">{{ number_format((float)$provider->settled_total, 2) }}</div>
                                <div class="text-muted small" style="font-size:0.75rem;">{{ __('SAR') }}</div>
                            </td>
                            <td class="text-end pe-3 font-monospace">
                                @if($hasPending)
                                    <span class="badge badge-orange fs-6 px-2.5 py-1 rounded-pill fw-bold">
                                        {{ number_format((float)$provider->pending_total, 2) }} {{ __('SAR') }}
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill fw-semibold">
                                        <i class="fa-solid fa-check me-1"></i> 0.00
                                    </span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <div class="btn-group btn-group-sm shadow-sm" role="group" style="border-radius: 8px; overflow: hidden;">
                                    <a href="{{ route('admin.providers.commissions.index', ['provider_id' => $provider->provider_id]) }}"
                                       class="btn btn-orange d-inline-flex align-items-center gap-1.5 px-3 py-1.5 fw-semibold text-nowrap"
                                       title="{{ __('View provider commissions') }}">
                                        <i class="fa-solid fa-list-check"></i>
                                        <span>{{ __('Commissions statement') }}</span>
                                    </a>
                                    <a href="{{ route('admin.providers.edit', $provider->provider_id) }}"
                                       class="btn px-2.5 py-1.5 d-inline-flex align-items-center justify-content-center"
                                       style="background: var(--orange-hover) !important; color: #fff !important; border-inline-start: 1px solid rgba(255,255,255,0.3) !important;"
                                       title="{{ __('Edit provider & contract') }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fa-solid fa-folder-open fa-2x mb-2 text-secondary opacity-50"></i>
                                <div>{{ __('No provider records found matching the search criteria.') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $providers->links() }}
        </div>
    </div>
</main>
@endsection

@extends('layouts.admin')
@section('title', __('Detailed commissions ledger').' · Rounder')

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
    .badge-orange {
        background: #fff3eb !important;
        color: #d95d08 !important;
        border: 1px solid #ffd4b8 !important;
    }
</style>

<main class="container py-4">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Detailed commissions ledger') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Comprehensive record of all commission entries and calculations linked to service orders and emergencies.') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.providers.finance.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fa-solid fa-scale-balanced me-1"></i> {{ __('Provider balances') }}
            </a>
            <a href="{{ route('admin.providers.index') }}" class="btn btn-light shadow-sm">
                <i class="fa-solid fa-handshake me-1"></i> {{ __('Providers list') }}
            </a>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Tabs --}}
    <ul class="nav nav-pills nav-pills-orange mb-3 gap-2">
        <li class="nav-item">
            <a class="nav-link fw-semibold px-4 py-2 rounded-3" href="{{ route('admin.providers.finance.index') }}">
                <i class="fa-solid fa-scale-balanced me-1"></i> {{ __('Provider accounts & balances') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active fw-bold px-4 py-2 rounded-3" href="{{ route('admin.providers.commissions.index') }}">
                <i class="fa-solid fa-table-list me-1"></i> {{ __('Commissions ledger') }}
            </a>
        </li>
    </ul>

    {{-- Filters Card --}}
    <div class="panel p-3 p-lg-4 shadow-sm mb-4" style="border-radius: 16px; background: #fff;">
        <form method="get" action="{{ route('admin.providers.commissions.index') }}">
            <div class="row g-3">
                {{-- Quick Search --}}
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-secondary">{{ __('Quick search') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-start-0"
                               value="{{ request('q') }}"
                               placeholder="{{ __('Order #, reference, provider name...') }}">
                    </div>
                </div>

                {{-- Select Provider --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-secondary">{{ __('Service provider') }}</label>
                    <select name="provider_id" class="form-select select2">
                        <option value="">{{ __('All providers') }}</option>
                        @foreach($providersList as $p)
                            <option value="{{ $p->id }}" {{ request('provider_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold text-secondary">{{ __('Commission status') }}</label>
                    <select name="status" class="form-select">
                        <option value="all">{{ __('All') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="settled" {{ request('status') === 'settled' ? 'selected' : '' }}>{{ __('Settled') }}</option>
                        <option value="reversed" {{ request('status') === 'reversed' ? 'selected' : '' }}>{{ __('Reversed') }}</option>
                    </select>
                </div>

                {{-- Order Type --}}
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-secondary">{{ __('Order type') }}</label>
                    <select name="type" class="form-select">
                        <option value="">{{ __('All types') }}</option>
                        <option value="booking" {{ request('type') === 'booking' ? 'selected' : '' }}>{{ __('Regular service order') }}</option>
                        <option value="emergency" {{ request('type') === 'emergency' ? 'selected' : '' }}>{{ __('Emergency request') }} ⚡</option>
                    </select>
                </div>

                {{-- Date From --}}
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-secondary">{{ __('Date from') }}</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                {{-- Date To --}}
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-secondary">{{ __('Date to') }}</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                {{-- Actions --}}
                <div class="col-12 col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-orange px-4 fw-semibold shadow-sm">
                        <i class="fa-solid fa-filter me-1"></i> {{ __('Apply filter') }}
                    </button>
                    @if(request()->anyFilled(['q', 'provider_id', 'status', 'type', 'date_from', 'date_to']))
                        <a href="{{ route('admin.providers.commissions.index') }}" class="btn btn-light border text-secondary">
                            <i class="fa-solid fa-rotate-left me-1"></i> {{ __('Reset') }}
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Filtered Results Summary --}}
        <div class="d-flex flex-wrap align-items-center gap-3 pt-3 mt-3 border-top">
            <span class="text-secondary small fw-semibold">{{ __('Total results:') }}</span>
            <span class="badge bg-light text-dark border px-3 py-2">
                {{ __('Entries count:') }} <strong>{{ $filteredTotals['count'] }}</strong>
            </span>
            <span class="badge bg-light text-dark border px-3 py-2">
                {{ __('Orders value:') }} <strong>{{ number_format($filteredTotals['gross'], 2) }} {{ __('SAR') }}</strong>
            </span>
            <span class="badge badge-orange px-3 py-2">
                {{ __('Total commissions:') }} <strong>{{ number_format($filteredTotals['commission'], 2) }} {{ __('SAR') }}</strong>
            </span>
        </div>
    </div>

    {{-- Commissions Table --}}
    <div class="panel p-3 p-lg-4 shadow-sm" style="border-radius: 16px; background: #fff;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 5%;">#</th>
                        <th style="width: 20%;">{{ __('Provider / Company') }}</th>
                        <th style="width: 13%;">{{ __('Linked order') }}</th>
                        <th style="width: 15%;">{{ __('Service / Details') }}</th>
                        <th class="text-end" style="width: 10%;">{{ __('Order amount') }}</th>
                        <th class="text-center" style="width: 8%;">{{ __('Rate') }}</th>
                        <th class="text-end" style="width: 10%;">{{ __('Commission amount') }}</th>
                        <th class="text-center" style="width: 9%;">{{ __('Status') }}</th>
                        <th style="width: 10%;">{{ __('Calculated at') }}</th>
                        <th class="text-center pe-3" style="width: 7%;">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        @php
                            $isBooking = !empty($entry->booking_id);
                            $isEmergency = !empty($entry->emergency_request_id);

                            $serviceName = $entry->booking?->service?->translations?->firstWhere('locale', app()->getLocale())?->name
                                ?? $entry->booking?->service?->code;
                            $emergencyName = $entry->emergencyRequest?->emergencyType?->translations?->firstWhere('locale', app()->getLocale())?->name
                                ?? $entry->emergencyRequest?->reference;

                            $statusCode = $entry->status?->code ?? 'pending';
                        @endphp
                        <tr>
                            <td class="ps-3 font-monospace text-muted small">
                                #{{ $entry->id }}
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $entry->providerProfile?->company_name ?? '—' }}</div>
                                @if($entry->providerProfile?->user?->phone)
                                    <div class="small text-muted mt-0.5 d-inline-flex align-items-center gap-1">
                                        <i class="fa-solid fa-phone text-secondary opacity-75" style="font-size: 0.72rem;"></i>
                                        <span dir="ltr" class="font-monospace">{{ $entry->providerProfile->user->phone }}</span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($isBooking)
                                    <a href="{{ route('admin.service-orders.show', $entry->booking_id) }}"
                                       class="badge bg-primary-subtle text-primary text-decoration-none fs-7 px-2 py-1 border"
                                       title="{{ __('View order details') }}" target="_blank">
                                        <i class="fa-solid fa-clipboard-list me-1"></i> {{ __('Order #') }}{{ $entry->booking_id }}
                                    </a>
                                @elseif($isEmergency)
                                    <span class="badge bg-warning-subtle text-warning-emphasis fs-7 px-2 py-1 border" title="{{ __('Emergency') }}">
                                        <i class="fa-solid fa-bolt me-1"></i> {{ $entry->emergencyRequest?->reference ?: '#'.$entry->emergency_request_id }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark">{{ $serviceName ?? $emergencyName ?? '—' }}</div>
                            </td>
                            <td class="text-end font-monospace">
                                <span class="fw-semibold text-dark">{{ number_format((float)$entry->gross_amount, 2) }}</span>
                                <span class="text-muted small" style="font-size:0.75rem;">{{ __('SAR') }}</span>
                            </td>
                            <td class="text-center font-monospace">
                                <span class="badge bg-light text-secondary border">
                                    {{ number_format((float)$entry->commission_percent_snapshot, 2) }}%
                                </span>
                            </td>
                            <td class="text-end font-monospace">
                                <span class="fw-bold text-primary">{{ number_format((float)$entry->commission_amount, 2) }}</span>
                                <span class="text-muted small" style="font-size:0.75rem;">{{ __('SAR') }}</span>
                            </td>
                            <td class="text-center">
                                @if($statusCode === 'settled')
                                    <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill fw-semibold">
                                        <i class="fa-solid fa-check-double me-1"></i> {{ __('Settled') }}
                                    </span>
                                @elseif($statusCode === 'reversed')
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill fw-semibold">
                                        <i class="fa-solid fa-ban me-1"></i> {{ __('Reversed') }}
                                    </span>
                                @else
                                    <span class="badge badge-orange px-2 py-1 rounded-pill fw-semibold">
                                        <i class="fa-solid fa-clock me-1"></i> {{ __('Pending') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="small text-dark font-monospace">{{ $entry->calculated_at?->format('Y-m-d') ?? '—' }}</div>
                                <div class="text-muted" style="font-size:0.75rem;">{{ $entry->calculated_at?->format('H:i') }}</div>
                            </td>
                            <td class="text-center pe-3">
                                <form method="post" action="{{ route('admin.providers.commissions.toggle-status', $entry->id) }}" class="d-inline"
                                      onsubmit="return confirm('{{ $statusCode === 'settled' ? __('Are you sure you want to mark this commission as pending?') : __('Are you sure you want to settle this commission?') }}');">
                                    @csrf
                                    @if($statusCode === 'settled')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="{{ __('Cancel settlement') }}">
                                            <i class="fa-solid fa-rotate-left"></i>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-orange" title="{{ __('Settle commission') }}">
                                            <i class="fa-solid fa-check me-1"></i> {{ __('Settle') }}
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="fa-solid fa-receipt fa-2x mb-2 text-secondary opacity-50"></i>
                                <div>{{ __('No commission entries found matching the filter criteria.') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $entries->links() }}
        </div>
    </div>
</main>
@endsection

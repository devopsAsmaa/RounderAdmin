@extends('layouts.admin')
@section('title',__('Dashboard').' · Rounder')
@section('body')
<div class="container-fluid"><div class="row g-3 p-3 p-lg-4">

<main class="col-12 content">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
<div>
    <div class="d-flex align-items-center gap-2 mb-1">
        <span class="badge" style="background: rgba(2, 51, 62, 0.08); color: var(--petrol); font-size: 0.75rem; font-weight: 700;">
            <i class="fa-solid fa-circle text-success me-1" style="font-size: 0.45rem; vertical-align: middle;"></i>{{ __('Live metrics') }}
        </span>
    </div>
    <h1 class="h3 fw-bold mb-1 text-dark">{{ __('Dashboard') }}</h1>
    <p class="text-secondary small mb-0">{{ __('Metrics are queried from MySQL records.') }}</p>
</div>
<div class="d-flex align-items-center gap-2">
    <span class="pill px-3 py-2" style="font-size: 0.88rem; background: #fff; border: 1px solid #dce6e3; box-shadow: 0 1px 3px rgba(2,51,62,0.03);">
        <i class="fa-regular fa-calendar-days text-secondary me-1"></i>
        {{ now()->translatedFormat('d F Y') }}
    </span>
</div>
</div>
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
@if(array_key_exists('residents',$stats))
<div class="col"><a href="{{ route('admin.residents.index') }}" class="kpi-card" aria-label="{{ __('Residents') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Residents') }}</span><span class="kpi-icon"><i class="fa-solid fa-users" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['residents'],0) }}</div>
</a></div>@endif
@if(array_key_exists('providers',$stats))
<div class="col"><a href="{{ route('admin.providers.index') }}" class="kpi-card" aria-label="{{ __('Approved providers') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Approved providers') }}</span><span class="kpi-icon"><i class="fa-solid fa-user-shield" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['providers'],0) }}</div>
</a></div>@endif
@if(array_key_exists('bookings',$stats))
<div class="col"><a href="{{ route('admin.service-orders.index') }}" class="kpi-card" aria-label="{{ __('All bookings') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('All bookings') }}</span><span class="kpi-icon"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['bookings'],0) }}</div>
</a></div>@endif
@if(array_key_exists('pending_bookings',$stats))
<div class="col"><a href="{{ route('admin.service-orders.index') }}" class="kpi-card" aria-label="{{ __('Pending bookings') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Pending bookings') }}</span><span class="kpi-icon"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['pending_bookings'],0) }}</div>
</a></div>@endif
@if(array_key_exists('revenue',$stats))
<div class="col"><article class="kpi-card financial" aria-label="{{ __('Collected payments') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Collected payments') }}</span><span class="kpi-icon"><i class="fa-solid fa-wallet" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['revenue'],2) }} <span class="kpi-currency">{{ __('SAR') }}</span></div>
</article></div>@endif
@if(array_key_exists('linked_homes',$stats))
<div class="col"><a href="{{ route('admin.realestate.index') }}" class="kpi-card" aria-label="{{ __('Linked homes') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Linked homes') }}</span><span class="kpi-icon"><i class="fa-solid fa-building" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['linked_homes'],0) }}</div>
</a></div>@endif
@if(array_key_exists('open_reports',$stats))
<div class="col"><a href="{{ route('admin.content.index') }}" class="kpi-card" aria-label="{{ __('Open facility reports') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Open facility reports') }}</span><span class="kpi-icon"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['open_reports'],0) }}</div>
</a></div>@endif
@if(array_key_exists('community_posts',$stats))
<div class="col"><a href="{{ route('admin.content.index') }}" class="kpi-card" aria-label="{{ __('Community posts') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Community posts') }}</span><span class="kpi-icon"><i class="fa-solid fa-comments" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['community_posts'],0) }}</div>
</a></div>@endif
@if(array_key_exists('active_initiatives',$stats))
<div class="col"><a href="{{ route('admin.content.index') }}" class="kpi-card" aria-label="{{ __('Active initiatives') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Active initiatives') }}</span><span class="kpi-icon"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['active_initiatives'],0) }}</div>
</a></div>@endif
@if(array_key_exists('pending_emergencies',$stats))
<div class="col"><a href="{{ route('admin.emergencies.index') }}" class="kpi-card urgent" aria-label="{{ __('Pending emergency requests') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Pending emergency requests') }}</span><span class="kpi-icon"><i class="fa-solid fa-truck-medical" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['pending_emergencies'],0) }}</div>
</a></div>@endif
@if(array_key_exists('active_subscriptions',$stats))
<div class="col"><a href="{{ route('admin.subscriptions.index') }}" class="kpi-card" aria-label="{{ __('Active subscriptions') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Active subscriptions') }}</span><span class="kpi-icon"><i class="fa-solid fa-repeat" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['active_subscriptions'],0) }}</div>
</a></div>@endif
@if(array_key_exists('available_packages',$stats))
<div class="col"><a href="{{ route('admin.packages.index') }}" class="kpi-card" aria-label="{{ __('Available packages') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Available packages') }}</span><span class="kpi-icon"><i class="fa-solid fa-box-open" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['available_packages'],0) }}</div>
</a></div>@endif
@if(array_key_exists('commission_total',$stats))
<div class="col"><article class="kpi-card financial" aria-label="{{ __('Platform commissions') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Platform commissions') }}</span><span class="kpi-icon"><i class="fa-solid fa-coins" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['commission_total'],2) }} <span class="kpi-currency">{{ __('SAR') }}</span></div>
</article></div>@endif
</div>
@adminCan('admin.service-orders.index')
<div class="panel p-3 p-lg-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="h5 fw-bold mb-1">{{ __('Recent bookings') }}</h2>
            <div class="small text-secondary">{{ __('Latest activity across the system') }}</div>
        </div>
        <a href="{{ route('admin.service-orders.index') }}" class="btn btn-sm btn-outline-primary">
            <span>{{ __('View all') }}</span>
            <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-left' : 'fa-arrow-right' }} ms-1"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Resident') }}</th>
                    <th>{{ __('Service') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent as $booking)
                <tr>
                    <td>
                        <a href="{{ route('admin.service-orders.show', $booking->id) }}" class="fw-bold text-decoration-none" style="color: var(--petrol);">
                            #{{ $booking->id }}
                        </a>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-grid place-items-center" style="width: 30px; height: 30px; background: #eaf3f0; color: var(--petrol); font-weight: 700; font-size: 0.78rem;">
                                {{ mb_substr($booking->resident->name ?? '—', 0, 1) }}
                            </div>
                            <span class="fw-semibold">{{ $booking->resident->name }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge" style="background: rgba(2, 51, 62, 0.06); color: var(--petrol); font-size: 0.82rem; font-weight: 600;">
                            {{ $booking->service->translations->firstWhere('locale',app()->getLocale())?->name ?? $booking->service->code }}
                        </span>
                    </td>
                    <td>
                        <span class="pill">{{ $booking->status->translations->firstWhere('locale',app()->getLocale())?->name ?? $booking->status->code }}</span>
                    </td>
                    <td>
                        <span class="text-secondary small">{{ $booking->created_at->format('Y-m-d') }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.service-orders.show', $booking->id) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="{{ __('View details') }}">
                            <i class="fa-solid fa-eye"></i>
                            <span class="d-none d-md-inline">{{ __('View details') }}</span>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-secondary py-4">
                        <i class="fa-regular fa-folder-open d-block fs-3 mb-2 opacity-50"></i>
                        {{ __('No bookings yet') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endadminCan
</main></div></div>
@endsection

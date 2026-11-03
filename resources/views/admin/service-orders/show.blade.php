@extends('layouts.admin')
@section('title', __('Booking details').' #'.$booking->id.' · Rounder')
@section('body')
@php
    $locale = app()->getLocale();
    $serviceTranslation = $booking->service?->translations->firstWhere('locale', $locale);
    $serviceName = $serviceTranslation?->name ?? $booking->service?->code ?? '—';
    $categoryTranslation = $booking->service?->category?->translations->firstWhere('locale', $locale);
    $categoryName = $categoryTranslation?->name ?? $booking->service?->category?->code ?? null;
    $subServiceTranslation = $booking->subService?->translations->firstWhere('locale', $locale);
    $subServiceName = $subServiceTranslation?->name ?? $booking->subService?->code ?? null;
    $statusTranslation = $booking->status?->translations->firstWhere('locale', $locale);
    $statusName = $statusTranslation?->name ?? $booking->status?->code ?? '—';
    $statusCode = $booking->status?->code ?? 'pending';
    $cityTranslation = $booking->home?->city?->translations->firstWhere('locale', $locale);
    $cityName = $cityTranslation?->name ?? $booking->home?->city?->code ?? '—';
    $assignment = $booking->assignment;
    $providerProfile = $assignment?->providerProfile ?? $booking->serviceProvider;
    $commission = $booking->providerCommissionEntries->first();

    $statusHistory = $booking->bookingStatusHistory ?? collect();
    $submittedAt = $statusHistory->firstWhere('booking_status_id', 81)?->created_at ?? $booking->created_at;
    $confirmedAt = $statusHistory->firstWhere('booking_status_id', 82)?->created_at;
    $inProgressAt = $statusHistory->firstWhere('booking_status_id', 83)?->created_at;
    $completedAt = $statusHistory->firstWhere('booking_status_id', 84)?->created_at ?? $booking->completed_at;
    $cancelledAt = $statusHistory->firstWhere('booking_status_id', 85)?->created_at;

    $isCancelled = $statusCode === 'cancelled';

    // تسلسل الحالات الفعلي للنظام (booking_statuses)
    $sequenceCodes = ['pending', 'confirmed', 'in_progress', 'completed'];
    $sequenceIndex = array_search($statusCode, $sequenceCodes, true);
    if ($sequenceIndex === false) {
        $sequenceIndex = -1;
    }

    $stageIcons = [
        'pending' => 'fa-hourglass-half',
        'confirmed' => 'fa-calendar-check',
        'in_progress' => 'fa-truck-fast',
        'completed' => 'fa-check-double',
    ];

    $stages = [];
    foreach ($sequenceCodes as $idx => $code) {
        $st = $statuses->firstWhere('code', $code);
        $stName = $st?->name ?? $code;
        $stId = $st?->id;

        $timestamp = match($code) {
            'pending' => $submittedAt,
            'confirmed' => $confirmedAt ?? ($assignment?->accepted_at ?? $assignment?->assigned_at),
            'in_progress' => $inProgressAt,
            'completed' => $completedAt,
            default => null,
        };

        $stages[] = [
            'id' => $stId,
            'code' => $code,
            'title' => $stName,
            'is_done' => ($statusCode === 'completed') ? true : ($sequenceIndex >= 0 && $idx <= $sequenceIndex),
            'is_current' => ($statusCode === $code),
            'timestamp' => $timestamp,
            'icon' => $stageIcons[$code] ?? 'fa-circle-dot',
        ];
    }
@endphp

<div class="container-fluid">
    <div class="row g-3 p-3 p-lg-4">
        <main class="col-12 content">
            {{-- Breadcrumb & Top Actions --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 bg-transparent p-0 align-items-center">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-1">
                                <i class="fa-solid fa-house" style="font-size:0.8rem;"></i>
                                <span>{{ __('Dashboard') }}</span>
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.service-orders.index') }}" class="text-decoration-none text-secondary">
                                {{ __('Service orders') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active fw-bold text-dark d-inline-flex align-items-center gap-1" aria-current="page">
                            <span>#{{ $booking->id }}</span>
                        </li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary" title="{{ __('Print details') }}">
                        <i class="fa-solid fa-print me-1"></i> <span class="d-none d-sm-inline">{{ __('Print details') }}</span>
                    </button>
                    <a href="{{ route('admin.service-orders.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid {{ $locale === 'ar' ? 'fa-arrow-right' : 'fa-arrow-left' }} me-1"></i> {{ __('Back to service orders') }}
                    </a>
                    @adminCan('admin.service-orders.destroy', 'DELETE')
                        @include('admin.partials.delete-button', ['action' => route('admin.service-orders.destroy', $booking->id)])
                    @endadminCan
                </div>
            </div>

            {{-- Alerts / Feedback --}}
            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>{{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    <strong>{{ __('Please check the form errors') }}:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Main Header Hero Panel --}}
            <div class="panel p-3 p-lg-4 mb-4 border-0 position-relative overflow-hidden"
                 style="background: linear-gradient(135deg, var(--petrol) 0%, #034b57 100%); color: #fff; box-shadow: 0 4px 20px rgba(2,51,62,0.18);">
                {{-- Subtle decorative glow --}}
                <div class="position-absolute end-0 top-0 w-50 h-100 opacity-25 pe-none"
                     style="background: radial-gradient(circle at top right, rgba(251,130,47,0.4) 0%, transparent 60%);"></div>

                <div class="row align-items-center g-3 position-relative">
                    <div class="col-lg-8">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge px-3 py-2 text-uppercase fw-bold" style="background: rgba(255,255,255,0.18); font-size: 0.85rem; letter-spacing: 0.05em; border: 1px solid rgba(255,255,255,0.2);">
                                <i class="fa-solid fa-hashtag me-1"></i>{{ $booking->id }}
                            </span>

                            @if($statusCode === 'completed')
                                <span class="badge bg-success px-3 py-2 fs-6 shadow-sm">
                                    <i class="fa-solid fa-check-double me-1"></i> {{ $statusName }}
                                </span>
                            @elseif($statusCode === 'confirmed')
                                <span class="badge px-3 py-2 fs-6 shadow-sm" style="background:#025968; color:#DDF3EE; border:1px solid rgba(221,243,238,0.4);">
                                    <i class="fa-solid fa-calendar-check me-1"></i> {{ $statusName }}
                                </span>
                            @elseif($statusCode === 'in_progress')
                                <span class="badge px-3 py-2 fs-6 shadow-sm" style="background:var(--orange); color:#fff;">
                                    <i class="fa-solid fa-truck-fast me-1"></i> {{ $statusName }}
                                </span>
                            @elseif($statusCode === 'cancelled')
                                <span class="badge bg-danger px-3 py-2 fs-6 shadow-sm">
                                    <i class="fa-solid fa-ban me-1"></i> {{ $statusName }}
                                </span>
                            @else
                                <span class="badge px-3 py-2 fs-6 shadow-sm" style="background:#fff3ea; color:#b6530b; border:1px solid #ffd8bf;">
                                    <i class="fa-solid fa-hourglass-half me-1"></i> {{ $statusName }}
                                </span>
                            @endif

                            @if($categoryName)
                                <span class="badge rounded-pill" style="background: rgba(255,255,255,0.12); color:#DDF3EE; border: 1px solid rgba(221,243,238,0.25);">
                                    <i class="fa-solid fa-layer-group me-1"></i>{{ $categoryName }}
                                </span>
                            @endif
                        </div>

                        <h1 class="h2 fw-bold text-white mb-1">{{ $serviceName }}</h1>

                        @if($subServiceName)
                            <div class="text-white-50 fs-6 mb-2 d-flex align-items-center gap-1">
                                <i class="fa-solid fa-arrow-turn-down-right text-rounder-orange"></i>
                                <span>{{ __('Sub service') }}:</span>
                                <span class="text-white fw-semibold">{{ $subServiceName }}</span>
                            </div>
                        @endif

                        <div class="d-flex flex-wrap align-items-center gap-3 text-white-50 small mt-3">
                            <span class="d-inline-flex align-items-center gap-1">
                                <i class="fa-regular fa-clock text-warning"></i>
                                <span>{{ __('Scheduled date and time') }}:</span>
                                <strong class="text-white">{{ $booking->scheduled_at ? $booking->scheduled_at->format('Y-m-d H:i') : '—' }}</strong>
                            </span>
                            <span>•</span>
                            <span class="d-inline-flex align-items-center gap-1">
                                <i class="fa-regular fa-calendar-plus text-info"></i>
                                <span>{{ __('Created at') }}:</span>
                                <strong class="text-white">{{ $booking->created_at ? $booking->created_at->format('Y-m-d H:i') : '—' }}</strong>
                            </span>
                        </div>
                    </div>

                    <div class="col-lg-4 text-lg-end">
                        <div class="p-3 rounded-3 d-inline-block text-start text-lg-end w-100 w-lg-auto"
                             style="background: rgba(255,255,255,0.12); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.18);">
                            <div class="small text-white-50">{{ __('Total price') }}</div>
                            <div class="display-6 fw-bold text-white" style="letter-spacing: -0.02em;">
                                {{ number_format($booking->total_amount, 2) }} <span class="fs-6 fw-normal text-white-50">SAR</span>
                            </div>
                            @if($booking->service && (float)$booking->service->base_price > 0 && (float)$booking->service->base_price != (float)$booking->total_amount)
                                <div class="small text-white-50 mt-1">
                                    {{ __('Base price') }}: {{ number_format($booking->service->base_price, 2) }} SAR
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order Tracking & Stages Progress Stepper ("مراحل الطلب") --}}
            <div class="panel p-3 p-lg-4 mb-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-grid place-items-center" style="width:36px; height:36px; background:var(--mint); color:var(--petrol);">
                            <i class="fa-solid fa-route"></i>
                        </div>
                        <div>
                            <h2 class="h5 fw-bold mb-0 text-dark">{{ __('Order tracking') }}</h2>
                            <div class="small text-muted">{{ __('Order stages') }} ({{ count($stages) }} {{ __('stages') }})</div>
                        </div>
                    </div>
                    <div>
                        @if($isCancelled)
                            <span class="badge bg-danger px-3 py-2">
                                <i class="fa-solid fa-ban me-1"></i> {{ __('Order Cancelled') }}
                            </span>
                        @else
                            <span class="pill fw-bold" style="background:var(--mint); color:var(--petrol); border-color:#bce5dc;">
                                <i class="fa-solid fa-circle-dot me-1 text-rounder-orange"></i>
                                {{ __('Current stage') }}: {{ $statusName }}
                            </span>
                        @endif
                    </div>
                </div>

                @if($isCancelled)
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation fs-5"></i>
                        <div>
                            <strong>{{ __('Order Cancelled') }}</strong>
                            @if($cancelledAt)
                                <span class="ms-1">({{ \Carbon\Carbon::parse($cancelledAt)->format('Y-m-d H:i') }})</span>
                            @endif
                            @php
                                $cancelHistory = $statusHistory->firstWhere('booking_status_id', 85);
                            @endphp
                            @if($cancelHistory?->note)
                                <div class="small mt-1 text-danger-emphasis fst-italic">"{{ $cancelHistory->note }}"</div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Visual 4-Step Progress Stepper --}}
                <div class="order-stepper-wrapper py-2">
                    <div class="row g-3">
                        @foreach($stages as $index => $stage)
                            <div class="col-12 col-sm-6 col-lg-3">
                                <div class="p-3 rounded-3 h-100 position-relative transition-all"
                                     style="background: {{ $stage['is_current'] && !$isCancelled ? 'linear-gradient(180deg, #fff7f0 0%, #fff 100%)' : ($stage['is_done'] && !$isCancelled ? '#f4faf7' : '#fafafa') }};
                                            border: 1.5px solid {{ $stage['is_current'] && !$isCancelled ? 'var(--orange)' : ($stage['is_done'] && !$isCancelled ? '#c2e5dc' : '#e5e7eb') }};
                                            box-shadow: {{ $stage['is_current'] && !$isCancelled ? '0 4px 14px rgba(251,130,47,0.14)' : 'none' }};">
                                    
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        @if($isCancelled && !$stage['is_done'])
                                            <div class="rounded-circle d-grid place-items-center text-muted"
                                                 style="width:38px; height:38px; background:#f1f5f9; border: 2px dashed #cbd5e1; flex-shrink:0;">
                                                <i class="fa-solid fa-xmark"></i>
                                            </div>
                                        @elseif($stage['is_done'] && !$isCancelled)
                                            <div class="rounded-circle d-grid place-items-center"
                                                 style="width:38px; height:38px; background:var(--mint); color:var(--petrol); border: 2px solid #025968; flex-shrink:0;">
                                                <i class="fa-solid fa-check"></i>
                                            </div>
                                        @elseif($stage['is_current'] && !$isCancelled)
                                            <div class="rounded-circle d-grid place-items-center text-white"
                                                 style="width:38px; height:38px; background:var(--orange); box-shadow: 0 0 0 4px rgba(251,130,47,0.22); flex-shrink:0;">
                                                <i class="fa-solid {{ $stage['icon'] }}"></i>
                                            </div>
                                        @else
                                            <div class="rounded-circle d-grid place-items-center text-muted"
                                                 style="width:38px; height:38px; background:#f8faf9; border: 2px dashed #cbd5e1; flex-shrink:0;">
                                                <i class="fa-solid {{ $stage['icon'] }}"></i>
                                            </div>
                                        @endif

                                        <div class="overflow-hidden">
                                            <div class="small text-muted" style="font-size:0.75rem;">
                                                {{ __('Stage') }} {{ $index + 1 }}
                                            </div>
                                            <div class="fw-bold text-dark text-truncate" style="font-size:0.92rem;">
                                                {{ $stage['title'] }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-secondary small mt-1 d-flex align-items-center gap-1" style="font-size:0.78rem;">
                                        <span class="badge bg-light text-secondary border font-monospace" style="font-size:0.7rem;">{{ $stage['code'] }}</span>
                                        <span class="text-muted" style="font-size:0.72rem;">#{{ $stage['id'] }}</span>
                                    </div>

                                    <div class="mt-2 pt-2 border-top d-flex align-items-center justify-content-between" style="font-size:0.73rem;">
                                        @if($stage['timestamp'] && ($stage['is_done'] || $stage['is_current']))
                                            <span class="text-muted"><i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($stage['timestamp'])->format('Y-m-d H:i') }}</span>
                                            <span class="badge {{ $stage['is_current'] && !$isCancelled ? 'bg-warning text-dark' : 'bg-success-subtle text-success' }}">
                                                {{ $stage['is_current'] && !$isCancelled ? __('Current stage') : __('Completed stage') }}
                                            </span>
                                        @else
                                            <span class="text-muted fst-italic">{{ __('Upcoming') }}</span>
                                            <span class="badge bg-light text-secondary border">{{ __('Pending') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 4 Quick Summary KPI Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="panel p-3 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-grid place-items-center flex-shrink-0"
                                 style="width:44px; height:44px; background: var(--mint); color: var(--petrol); font-size:1.15rem;">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="small text-secondary text-truncate">{{ __('Scheduled date and time') }}</div>
                                <div class="fw-bold fs-6 text-dark text-truncate">{{ $booking->scheduled_at ? $booking->scheduled_at->format('Y-m-d H:i') : '—' }}</div>
                                <div class="text-muted text-truncate" style="font-size:0.75rem;">{{ $booking->scheduled_at ? $booking->scheduled_at->diffForHumans() : '' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="panel p-3 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-grid place-items-center flex-shrink-0"
                                 style="width:44px; height:44px; background: #fff3ea; color: var(--orange); font-size:1.15rem;">
                                <i class="fa-solid fa-coins"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="small text-secondary text-truncate">{{ __('Total price') }}</div>
                                <div class="fw-bold fs-5 text-dark text-truncate">{{ number_format($booking->total_amount, 2) }} <span class="fs-6 fw-normal text-secondary">SAR</span></div>
                                <div class="text-muted text-truncate" style="font-size:0.75rem;">{{ $booking->payments->isNotEmpty() ? __('Payments') . ': ' . $booking->payments->count() : __('No payments recorded') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="panel p-3 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-grid place-items-center flex-shrink-0"
                                 style="width:44px; height:44px; background: #eaf2f4; color: var(--petrol); font-size:1.15rem;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="small text-secondary text-truncate">{{ __('Resident') }}</div>
                                <div class="fw-bold fs-6 text-dark text-truncate">{{ $booking->resident?->name ?? '—' }}</div>
                                <div class="text-muted text-truncate" style="font-size:0.75rem;">{{ $booking->residentHome?->unit_label ?? $booking->home?->name ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="panel p-3 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-grid place-items-center flex-shrink-0"
                                 style="width:44px; height:44px; background: #edf5f8; color: #025968; font-size:1.15rem;">
                                <i class="fa-solid fa-building-shield"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="small text-secondary text-truncate">{{ __('Assigned provider') }}</div>
                                <div class="fw-bold fs-6 text-dark text-truncate">
                                    {{ $providerProfile?->company_name ?? __('Not assigned yet') }}
                                </div>
                                <div class="text-muted text-truncate" style="font-size:0.75rem;">
                                    @if($assignment?->assigned_at)
                                        <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>{{ __('Assigned') }}</span>
                                    @else
                                        <span class="text-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>{{ __('Needs assignment') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2-Column Responsive Body --}}
            <div class="row g-4">
                {{-- Left Column: Details, Scope, Photos, Additional Works, Rating --}}
                <div class="col-lg-8">
                    {{-- 1. Service & Scope of Work Card --}}
                    <div class="panel p-3 p-lg-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                            <h2 class="h5 fw-bold mb-0 text-dark">
                                <i class="fa-solid fa-screwdriver-wrench me-2 text-primary"></i>{{ __('Service and scope') }}
                            </h2>
                            <span class="badge bg-light text-secondary border">
                                {{ $booking->service?->code }}
                            </span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="small text-secondary mb-1">{{ __('Available services') }}</div>
                                <div class="fw-semibold text-dark fs-6">{{ $serviceName }}</div>
                                @if($serviceTranslation?->description)
                                    <div class="small text-muted mt-1">{{ $serviceTranslation->description }}</div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="small text-secondary mb-1">{{ __('Service categories') }}</div>
                                <div class="fw-semibold text-dark">
                                    @if($categoryName)
                                        <span class="pill"><i class="fa-solid fa-layer-group me-1 text-rounder-orange"></i>{{ $categoryName }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </div>
                            @if($subServiceName)
                                <div class="col-md-6">
                                    <div class="small text-secondary mb-1">{{ __('Sub service') }}</div>
                                    <div class="fw-semibold text-primary"><i class="fa-solid fa-check me-1 text-rounder-orange"></i>{{ $subServiceName }}</div>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <div class="small text-secondary mb-1">{{ __('Base price') }}</div>
                                <div class="fw-semibold text-dark">{{ number_format($booking->service?->base_price ?? $booking->total_amount, 2) }} SAR</div>
                            </div>
                        </div>

                        {{-- Resident instructions / notes --}}
                        <div class="mt-4 pt-3 border-top">
                            <div class="small fw-bold text-secondary mb-2">
                                <i class="fa-regular fa-comment-dots me-1 text-primary"></i>{{ __('Resident instructions') }}
                            </div>
                            @if($booking->notes)
                                <div class="p-3 rounded-3" style="background:var(--sand); border-inline-start:4px solid var(--orange);">
                                    <div class="fst-italic text-dark">{{ $booking->notes }}</div>
                                </div>
                            @else
                                <div class="text-muted small fst-italic">{{ __('No resident notes provided') }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- 2. Customer and Property Location Card --}}
                    <div class="panel p-3 p-lg-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                            <h2 class="h5 fw-bold mb-0 text-dark">
                                <i class="fa-solid fa-location-dot me-2 text-danger"></i>{{ __('Customer and home details') }}
                            </h2>
                            @if($booking->home?->property_code)
                                <span class="badge bg-light text-dark border">{{ $booking->home->property_code }}</span>
                            @endif
                        </div>
                        <div class="row g-3">
                            {{-- Resident Details --}}
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 h-100" style="background: #f8faf9; border: 1px solid #e8efed;">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="rounded-circle d-grid place-items-center"
                                             style="width:42px; height:42px; background:var(--mint); color:var(--petrol); font-size:1.15rem; flex-shrink:0;">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="fw-bold text-dark text-truncate">{{ $booking->resident?->name ?? '—' }}</div>
                                            <div class="text-secondary small">{{ __('Resident') }} · ID #{{ $booking->resident_id }}</div>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2 small">
                                        @if($booking->resident?->phone)
                                            @php
                                                $cleanPhone = preg_replace('/[^0-9]/', '', $booking->resident->phone);
                                                $waPhone = str_starts_with($cleanPhone, '05') ? ('966' . substr($cleanPhone, 1)) : $cleanPhone;
                                            @endphp
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fa-solid fa-phone text-secondary" style="width:16px"></i>
                                                    <a href="tel:{{ $booking->resident->phone }}" class="text-decoration-none text-dark fw-semibold" dir="ltr">{{ $booking->resident->phone }}</a>
                                                </div>
                                                <div class="d-flex align-items-center gap-1">
                                                    <a href="tel:{{ $booking->resident->phone }}" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size:0.75rem;" title="{{ __('Call resident') }}">
                                                        <i class="fa-solid fa-phone-flip text-primary"></i>
                                                    </a>
                                                    <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success py-1 px-2" style="font-size:0.75rem;" title="{{ __('Contact via WhatsApp') }}">
                                                        <i class="fa-brands fa-whatsapp text-success"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                        @if($booking->resident?->email)
                                            <div class="d-flex align-items-center gap-2 text-truncate">
                                                <i class="fa-solid fa-envelope text-secondary" style="width:16px"></i>
                                                <a href="mailto:{{ $booking->resident->email }}" class="text-decoration-none text-dark text-truncate">{{ $booking->resident->email }}</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Property & Unit Details --}}
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 h-100" style="background: #f8faf9; border: 1px solid #e8efed;">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="rounded-circle d-grid place-items-center"
                                             style="width:42px; height:42px; background:#fff3ea; color:var(--orange); font-size:1.15rem; flex-shrink:0;">
                                            <i class="fa-solid fa-building"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="fw-bold text-dark text-truncate">{{ $booking->home?->name ?? '—' }}</div>
                                            <div class="text-secondary small">{{ $cityName }}</div>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2 small">
                                        @if($booking->home?->address)
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="fa-solid fa-map-pin text-secondary mt-1" style="width:16px"></i>
                                                <span class="text-dark">{{ $booking->home->address }}</span>
                                            </div>
                                        @endif
                                        @if($booking->residentHome)
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-door-open text-secondary" style="width:16px"></i>
                                                <span class="text-dark"><strong>{{ __('Unit label') }}:</strong> {{ $booking->residentHome->unit_label }}</span>
                                                @if($booking->residentHome->floor_number)
                                                    <span class="text-muted">({{ __('Floor') }}: {{ $booking->residentHome->floor_number }})</span>
                                                @endif
                                            </div>
                                            @if($booking->residentHome->area_sqm)
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fa-solid fa-ruler-combined text-secondary" style="width:16px"></i>
                                                    <span class="text-dark"><strong>{{ __('Area') }}:</strong> {{ $booking->residentHome->area_sqm }} m²</span>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Attached Photos & Media Gallery Card --}}
                    <div class="panel p-3 p-lg-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                            <h2 class="h5 fw-bold mb-0 text-dark">
                                <i class="fa-solid fa-images me-2 text-info"></i>{{ __('Attached photos and media') }}
                            </h2>
                            <span class="badge rounded-pill bg-light text-secondary border">
                                {{ $booking->bookingMedia->count() }} {{ __('Photos') }}
                            </span>
                        </div>

                        @if($booking->bookingMedia->isNotEmpty())
                            <div class="row g-3">
                                @foreach($booking->bookingMedia as $media)
                                    <div class="col-6 col-sm-4 col-md-3">
                                        <div class="card h-100 border shadow-sm overflow-hidden" style="border-radius:12px;">
                                            <a href="{{ $media->url }}" target="_blank" rel="noopener noreferrer"
                                               class="d-block position-relative ratio ratio-1x1 bg-light photo-preview-link"
                                               title="{{ __('View full photo') }}"
                                               data-img-url="{{ $media->url }}"
                                               data-img-kind="{{ $media->kind ?? 'photo' }}">
                                                <img src="{{ $media->url }}" alt="Booking photo" class="w-100 h-100" style="object-fit:cover; transition: transform 0.2s ease;">
                                                <span class="position-absolute top-0 end-0 m-2 badge bg-dark bg-opacity-75">
                                                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                                                </span>
                                            </a>
                                            <div class="p-2 small text-secondary bg-white" style="font-size:0.75rem;">
                                                <div class="text-truncate fw-semibold text-dark">
                                                    {{ $media->kind ?? 'photo' }}
                                                </div>
                                                <div class="text-muted text-truncate mt-1">
                                                    <i class="fa-solid fa-arrow-up-from-bracket me-1"></i>
                                                    @if($media->adminUploadedBy)
                                                        {{ __('Administrator') }}: {{ $media->adminUploadedBy->name }}
                                                    @elseif($media->uploadedBy)
                                                        {{ $media->uploadedBy->name }}
                                                    @else
                                                        {{ __('Resident') }}
                                                    @endif
                                                </div>
                                                <div class="text-muted" style="font-size:0.7rem;">
                                                    {{ $media->created_at ? $media->created_at->format('Y-m-d H:i') : '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 text-center text-secondary bg-light rounded-3">
                                <i class="fa-regular fa-image fa-2x mb-2 text-muted"></i>
                                <div>{{ __('No photos attached yet') }}</div>
                                <div class="small text-muted mt-1">{{ __('You can attach photos to describe the problem or requested service.') }}</div>
                            </div>
                        @endif
                    </div>

                    {{-- 4. Additional Work & Warranties (if any) --}}
                    @if($booking->bookingAdditionalWork->isNotEmpty() || $booking->bookingWarranties->isNotEmpty())
                        <div class="panel p-3 p-lg-4 mb-4">
                            <h2 class="h5 fw-bold mb-3 border-bottom pb-2 text-dark">
                                <i class="fa-solid fa-shield-halved me-2 text-success"></i>{{ __('Additional work') }} & {{ __('Warranties') }}
                            </h2>

                            @if($booking->bookingAdditionalWork->isNotEmpty())
                                <h3 class="h6 fw-bold text-dark mt-2 mb-2">{{ __('Additional work') }}</h3>
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Record number') }}</th>
                                                <th>{{ __('Description') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th>{{ __('Approved at') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($booking->bookingAdditionalWork as $work)
                                                <tr>
                                                    <td><strong>{{ $work->reference }}</strong></td>
                                                    <td>{{ $work->description }}</td>
                                                    <td><strong class="text-primary">{{ number_format($work->amount, 2) }} SAR</strong></td>
                                                    <td><span class="pill">{{ $work->status?->translations->firstWhere('locale', $locale)?->name ?? $work->status?->code }}</span></td>
                                                    <td class="small text-secondary">{{ $work->approved_at ? \Carbon\Carbon::parse($work->approved_at)->format('Y-m-d H:i') : '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            @if($booking->bookingWarranties->isNotEmpty())
                                <h3 class="h6 fw-bold text-dark mt-3 mb-2">{{ __('Warranties') }}</h3>
                                <div class="row g-2">
                                    @foreach($booking->bookingWarranties as $warranty)
                                        <div class="col-md-6">
                                            <div class="p-3 rounded-3 border bg-light small">
                                                <div class="fw-bold text-dark mb-1">
                                                    <i class="fa-solid fa-award me-1 text-warning"></i>{{ $warranty->reference }}
                                                </div>
                                                <div class="text-secondary mb-2">{{ $warranty->terms }}</div>
                                                <div class="d-flex justify-content-between text-muted" style="font-size:0.75rem;">
                                                    <span>{{ __('Valid from') }}: {{ $warranty->starts_on }}</span>
                                                    <span>{{ __('Valid until') }}: {{ $warranty->ends_on }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- 5. Customer Rating & Feedback (if rated) --}}
                    @if($booking->bookingRatings->isNotEmpty())
                        <div class="panel p-3 p-lg-4 mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                <h2 class="h5 fw-bold mb-0 text-dark">
                                    <i class="fa-solid fa-star me-2 text-warning"></i>{{ __('Customer review and rating') }}
                                </h2>
                            </div>
                            @foreach($booking->bookingRatings as $rating)
                                <div class="p-3 rounded-3" style="background:var(--sand); border: 1px solid #ffe8cc;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-1 text-warning fs-5">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fa-{{ $i <= $rating->score ? 'solid' : 'regular' }} fa-star"></i>
                                            @endfor
                                            <span class="ms-2 fw-bold text-dark fs-6">{{ $rating->score }} / 5</span>
                                        </div>
                                        <div class="small text-secondary">
                                            {{ $rating->created_at ? $rating->created_at->format('Y-m-d') : '' }}
                                        </div>
                                    </div>
                                    @if($rating->comment)
                                        <div class="text-dark fst-italic mt-2">"{{ $rating->comment }}"</div>
                                    @endif
                                    <div class="small text-muted mt-2">
                                        <i class="fa-solid fa-user me-1"></i>{{ $rating->resident?->name ?? __('Resident') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Right Column: Interactive Management, Assignment, Commission & Timeline --}}
                <div class="col-lg-4">
                    {{-- 1. Fast Management & Assignment Form --}}
                    @adminCan('admin.service-orders.update', 'PUT')
                        <div class="panel p-3 p-lg-4 mb-4 shadow-sm" style="border: 2px solid var(--petrol); border-radius: 18px;">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-grid place-items-center" style="width:34px; height:34px; background:var(--mint); color:var(--petrol);">
                                        <i class="fa-solid fa-sliders"></i>
                                    </div>
                                    <h2 class="h5 fw-bold mb-0 text-dark">
                                        {{ __('Quick order update') }}
                                    </h2>
                                </div>
                                <span class="badge" style="background:var(--petrol); color:#fff;">{{ __('Update') }}</span>
                            </div>
                            <form method="post" action="{{ route('admin.service-orders.update', $booking->id) }}" enctype="multipart/form-data" class="d-grid gap-3">
                                @csrf
                                @method('put')

                                <div>
                                    <label class="form-label fw-semibold small text-dark d-flex align-items-center gap-1">
                                        <i class="fa-solid fa-list-check text-primary"></i>
                                        <span>{{ __('Booking status') }}</span>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select order-status" name="booking_status_id" required>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}" data-code="{{ $status->code }}" @selected($booking->booking_status_id === $status->id)>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label fw-semibold small text-dark d-flex align-items-center gap-1">
                                        <i class="fa-solid fa-handshake text-primary"></i>
                                        <span>{{ __('Assigned provider') }}</span>
                                    </label>
                                    <select class="form-select provider-select" name="provider_profile_id">
                                        <option value="">{{ __('Choose provider') }}</option>
                                        @foreach($providers as $provider)
                                            <option value="{{ $provider->id }}" @selected(($assignment?->provider_profile_id ?? $booking->service_provider_id) === $provider->id)>
                                                {{ $provider->company_name }} · {{ $provider->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label fw-semibold small text-dark d-flex align-items-center gap-1">
                                        <i class="fa-solid fa-coins text-primary"></i>
                                        <span>{{ __('Final amount for commission') }}</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" step=".01" min="0" max="9999999999.99" name="gross_amount" class="form-control gross-amount"
                                               value="{{ old('gross_amount', $commission?->gross_amount ?? $booking->total_amount) }}"
                                               placeholder="{{ __('Final amount for commission') }}">
                                        <span class="input-group-text bg-light text-secondary">SAR</span>
                                    </div>
                                    <div class="form-text small" style="font-size:0.75rem;">
                                        {{ __('Required when completing this booking.') }}
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label fw-semibold small text-dark d-flex align-items-center gap-1">
                                        <i class="fa-solid fa-note-sticky text-primary"></i>
                                        <span>{{ __('Internal team note') }}</span>
                                    </label>
                                    <textarea name="note" rows="2" maxlength="2000" class="form-control" placeholder="{{ __('Internal note') }}"></textarea>
                                </div>

                                <div>
                                    <label class="form-label fw-semibold small text-dark d-flex align-items-center gap-1">
                                        <i class="fa-solid fa-camera text-primary"></i>
                                        <span>{{ __('Add photos') }}</span>
                                    </label>
                                    <input type="file" name="images[]" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp,image/jpg" multiple>
                                    <div class="form-text small" style="font-size:0.75rem;">{{ __('JPG, PNG or WebP, up to 4 MB.') }}</div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> {{ __('Save order update') }}
                                </button>
                            </form>
                        </div>
                    @endadminCan

                    {{-- 2. Provider Assignment & Commission Card --}}
                    <div class="panel p-3 p-lg-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                            <h2 class="h5 fw-bold mb-0 text-dark">
                                <i class="fa-solid fa-handshake me-2 text-primary"></i>{{ __('Financial and commission') }}
                            </h2>
                        </div>
                        @if($assignment)
                            <div class="d-grid gap-2 small mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-secondary">{{ __('Provider') }}:</span>
                                    <strong class="text-dark">{{ $assignment->providerProfile?->company_name ?? '—' }}</strong>
                                </div>
                                @if($assignment->providerContract)
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-secondary">{{ __('Provider contract') }}:</span>
                                        <span class="text-dark">#{{ $assignment->providerContract->id }} ({{ $assignment->providerContract->reference ?? '—' }})</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-secondary">{{ __('Commission rate') }}:</span>
                                        <span class="badge bg-light text-dark border">{{ $assignment->providerContract->service_commission_percent }}%</span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-secondary">{{ __('Assigned at') }}:</span>
                                    <span class="text-dark">{{ \Carbon\Carbon::parse($assignment->assigned_at)->format('Y-m-d H:i') }}</span>
                                </div>
                                @if($assignment->accepted_at)
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-secondary">{{ __('Accepted at') }}:</span>
                                        <span class="text-dark">{{ \Carbon\Carbon::parse($assignment->accepted_at)->format('Y-m-d H:i') }}</span>
                                    </div>
                                @endif
                                @if($assignment->completed_at)
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-secondary">{{ __('Completed at') }}:</span>
                                        <span class="text-dark">{{ \Carbon\Carbon::parse($assignment->completed_at)->format('Y-m-d H:i') }}</span>
                                    </div>
                                @endif
                                @if($assignment->adminActor)
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-secondary">{{ __('Assigned by') }}:</span>
                                        <span class="text-dark">{{ $assignment->adminActor->name }}</span>
                                    </div>
                                @elseif($assignment->assignedBy)
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-secondary">{{ __('Assigned by') }}:</span>
                                        <span class="text-dark">{{ $assignment->assignedBy->name }}</span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="p-3 text-center text-muted bg-light rounded-3 mb-3 small">
                                <i class="fa-solid fa-user-xmark mb-1"></i>
                                <div>{{ __('Not assigned yet') }}</div>
                            </div>
                        @endif

                        {{-- Commission Entry Details --}}
                        @if($commission)
                            <div class="p-3 rounded-3" style="background:var(--sand); border:1px solid #ffe8cc;">
                                <div class="small fw-bold text-dark mb-2">
                                    <i class="fa-solid fa-receipt me-1 text-primary"></i>{{ __('Commission snapshot') }}
                                </div>
                                <div class="d-grid gap-1 small">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-secondary">{{ __('Gross amount') }}:</span>
                                        <strong>{{ number_format($commission->gross_amount, 2) }} SAR</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-secondary">{{ __('Commission rate') }}:</span>
                                        <span>{{ $commission->commission_percent_snapshot }}%</span>
                                    </div>
                                    <div class="d-flex justify-content-between border-top pt-1 mt-1">
                                        <span class="text-secondary fw-bold">{{ __('Commission amount') }}:</span>
                                        <strong class="text-success">{{ number_format($commission->commission_amount, 2) }} SAR</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span class="text-secondary">{{ __('Commission status') }}:</span>
                                        <span class="pill">{{ $commission->status?->code ?? 'recorded' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- 3. Payments Recorded Card --}}
                    <div class="panel p-3 p-lg-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                            <h2 class="h5 fw-bold mb-0 text-dark">
                                <i class="fa-solid fa-credit-card me-2 text-primary"></i>{{ __('Payments') }}
                            </h2>
                            <span class="badge bg-light text-secondary border">{{ $booking->payments->count() }}</span>
                        </div>
                        @if($booking->payments->isNotEmpty())
                            <div class="d-grid gap-2">
                                @foreach($booking->payments as $payment)
                                    <div class="p-2 rounded-2 border bg-light small d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold text-dark">#{{ $payment->reference ?? $payment->id }}</div>
                                            <div class="text-muted" style="font-size:0.7rem;">{{ $payment->created_at ? $payment->created_at->format('Y-m-d H:i') : '' }}</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-primary">{{ number_format($payment->amount, 2) }} SAR</div>
                                            <span class="badge bg-success-subtle text-success" style="font-size:0.65rem;">{{ $payment->status?->code ?? 'paid' }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-3 text-center text-muted bg-light rounded-3 small">
                                <i class="fa-regular fa-credit-card mb-1"></i>
                                <div>{{ __('No payments recorded') }}</div>
                            </div>
                        @endif
                    </div>

                    {{-- 4. Status History & Audit Timeline Card --}}
                    <div class="panel p-3 p-lg-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                            <h2 class="h5 fw-bold mb-0 text-dark">
                                <i class="fa-solid fa-timeline me-2 text-secondary"></i>{{ __('Timeline and status history') }}
                            </h2>
                            <span class="badge bg-light text-secondary border">{{ $booking->bookingStatusHistory->count() }}</span>
                        </div>

                        @if($booking->bookingStatusHistory->isNotEmpty())
                            <div class="position-relative ps-3" style="border-inline-start: 2px dashed #b4dbd2; margin-inline-start: 10px;">
                                @foreach($booking->bookingStatusHistory as $history)
                                    @php
                                        $hStatusTranslation = $history->status?->translations->firstWhere('locale', $locale);
                                        $hStatusName = $hStatusTranslation?->name ?? $history->status?->code ?? '—';
                                        $hCode = $history->status?->code ?? 'pending';
                                        $actorName = $history->adminActor?->name ?? $history->changedBy?->name ?? __('System / User');

                                        $dotColor = match($hCode) {
                                            'completed' => '#10b981',
                                            'in_progress' => 'var(--orange)',
                                            'confirmed' => '#025968',
                                            'cancelled' => '#ef4444',
                                            default => '#f59e0b',
                                        };
                                    @endphp
                                    <div class="position-relative mb-3 pb-2">
                                        {{-- Dot --}}
                                        <span class="position-absolute rounded-circle"
                                              style="width: 12px; height: 12px; inset-inline-start: -23px; top: 4px; border: 2px solid #fff; background-color: {{ $dotColor }}; box-shadow: 0 0 0 2px rgba(2,51,62,0.1);"></span>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="pill fw-bold" style="font-size: 0.75rem;">{{ $hStatusName }}</span>
                                            <span class="text-muted" style="font-size: 0.7rem;">{{ $history->created_at ? $history->created_at->format('Y-m-d H:i') : '' }}</span>
                                        </div>
                                        <div class="small text-secondary mt-1">
                                            <i class="fa-solid fa-user-pen me-1" style="font-size: 0.7rem;"></i>{{ __('Changed by') }}: <strong>{{ $actorName }}</strong>
                                        </div>
                                        @if($history->note)
                                            <div class="small bg-light p-2 rounded-2 mt-1 text-dark fst-italic" style="font-size: 0.75rem;">
                                                "{{ $history->note }}"
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-3 text-center text-muted bg-light rounded-3 small">
                                <div>{{ __('No status history recorded') }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

{{-- Lightbox Image Modal for Fast Photo Preview --}}
<div class="modal fade" id="bookingPhotoModal" tabindex="-1" aria-labelledby="bookingPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; overflow:hidden;">
            <div class="modal-header border-bottom py-2 px-3" style="background:#f8faf9;">
                <h5 class="modal-title fs-6 fw-bold text-dark" id="bookingPhotoModalLabel">
                    <i class="fa-solid fa-image me-1 text-primary"></i> <span id="photoModalTitle">{{ __('Attached photos and media') }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center bg-dark">
                <img id="photoModalImage" src="" alt="Booking Photo" class="img-fluid" style="max-height: 80vh; width: auto; object-fit: contain;">
            </div>
            <div class="modal-footer border-top py-2 px-3 justify-content-between" style="background:#f8faf9;">
                <a id="photoModalRawLink" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> {{ __('Open original image') }}
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    {{ __('Close preview') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.order-status').forEach((select) => {
    const form = select.closest('form');
    if (!form) return;
    const amount = form.querySelector('.gross-amount');
    if (!amount) return;
    const sync = () => {
        const complete = select.options[select.selectedIndex]?.dataset.code === 'completed';
        amount.required = complete;
        amount.placeholder = complete ? @json(__('Final amount for commission')) : @json(__('Optional final amount'));
    };
    select.addEventListener('change', sync);
    sync();
});

// Photo Lightbox Modal handler
document.querySelectorAll('.photo-preview-link').forEach((link) => {
    link.addEventListener('click', function(e) {
        if (window.bootstrap && window.bootstrap.Modal) {
            e.preventDefault();
            const modalEl = document.getElementById('bookingPhotoModal');
            const modalImg = document.getElementById('photoModalImage');
            const modalTitle = document.getElementById('photoModalTitle');
            const rawLink = document.getElementById('photoModalRawLink');
            
            const url = this.getAttribute('data-img-url') || this.href;
            const kind = this.getAttribute('data-img-kind') || '';
            
            modalImg.src = url;
            rawLink.href = url;
            if (kind) {
                modalTitle.textContent = kind;
            }
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    });
});
</script>
@endpush

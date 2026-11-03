@extends('layouts.admin')
@section('title', __('Service orders').' · Rounder')
@section('body')
<div class="container-fluid">
    <div class="row g-3 p-3 p-lg-4">
        <main class="col-12 content">
            <!-- Header Section -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <div class="small text-secondary fw-medium">{{ __('Provider assignment and commissions') }}</div>
                    <h1 class="h3 fw-bold mb-0 text-dark">{{ __('Service orders') }}</h1>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- View Mode Switcher -->
                    <div class="btn-group shadow-sm" role="group" aria-label="{{ __('View mode') }}" style="border-radius: 10px; overflow: hidden;">
                        <button type="button" class="btn btn-sm btn-outline-primary active" id="btnViewCards" onclick="switchOrdersView('cards')" style="padding: 0.45rem 0.9rem; font-weight: 600; font-size: 0.82rem;">
                            <i class="fa-solid fa-table-cells-large me-1"></i> {{ __('Cards view') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnViewTable" onclick="switchOrdersView('table')" style="padding: 0.45rem 0.9rem; font-weight: 600; font-size: 0.82rem;">
                            <i class="fa-solid fa-table-list me-1"></i> {{ __('Table view') }}
                        </button>
                    </div>

                    @adminCan('admin.service-orders.create')
                    <a class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm" href="{{ route('admin.service-orders.create') }}" style="border-radius: 10px; padding: 0.45rem 1rem; font-weight: 600;">
                        <i class="fa-solid fa-plus"></i>
                        <span>{{ __('Create service order') }}</span>
                    </a>
                    @endadminCan
                </div>
            </div>

            @include('admin.partials.feedback')

            <!-- ========================================== -->
            <!-- 1. LUXURY CARDS VIEW (DEFAULT & COMFORTABLE) -->
            <!-- ========================================== -->
            <div id="ordersCardsView" class="orders-view-pane">
                @forelse($orders as $order)
                    @php
                        $statusColor = match($order->status_code) {
                            'completed' => ['bg' => '#eef8f4', 'color' => '#0e6c51', 'border' => '#bde4d5', 'dot' => '#10b981'],
                            'cancelled' => ['bg' => '#fdf2f2', 'color' => '#b32a2a', 'border' => '#f4c7c7', 'dot' => '#ef4444'],
                            'in_progress', 'accepted' => ['bg' => '#e8f4f8', 'color' => '#035368', 'border' => '#bce3eb', 'dot' => '#06b6d4'],
                            default => ['bg' => '#fff8ec', 'color' => '#b45309', 'border' => '#fed7aa', 'dot' => '#f59e0b'],
                        };
                        try {
                            $schedDate = !empty($order->scheduled_at) ? \Carbon\Carbon::parse($order->scheduled_at)->translatedFormat('d M Y · h:i A') : '—';
                        } catch (\Throwable $e) {
                            $schedDate = $order->scheduled_at;
                        }
                    @endphp
                    <div class="card border-0 shadow-sm mb-3 order-card" style="border-radius: 14px; overflow: hidden; border: 1px solid #e2ece8 !important; background: #ffffff;">
                        <!-- Card Header -->
                        <div class="card-header bg-white py-3 px-3 px-lg-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2" style="border-color: #eaf1ee !important;">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge" style="background: var(--petrol); color: #fff; font-size: 0.85rem; padding: 0.42rem 0.8rem; border-radius: 9px; font-weight: 700;">
                                    #{{ $order->id }}
                                </span>
                                <span class="d-inline-flex align-items-center gap-1 text-secondary small fw-medium" style="background: #f7faf9; padding: 0.35rem 0.75rem; border-radius: 8px; border: 1px solid #e1ece7;">
                                    <i class="fa-regular fa-calendar text-rounder-orange"></i>
                                    <span>{{ $schedDate }}</span>
                                </span>
                                <span class="badge rounded-pill d-inline-flex align-items-center gap-1.5" style="background: {{ $statusColor['bg'] }}; color: {{ $statusColor['color'] }}; border: 1px solid {{ $statusColor['border'] }}; font-size: 0.8rem; font-weight: 600; padding: 0.35rem 0.75rem;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $statusColor['dot'] }};"></span>
                                    {{ $order->status_name }}
                                </span>
                                @if($order->provider_name)
                                    <span class="badge d-inline-flex align-items-center gap-1 text-truncate" style="background: #f0f7f5; color: var(--petrol); border: 1px solid #d0e4dd; font-size: 0.78rem; font-weight: 600; max-width: 250px;">
                                        <i class="fa-solid fa-truck-fast text-rounder-orange"></i>
                                        <span class="text-truncate">{{ $order->provider_name }}</span>
                                    </span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('admin.service-orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" style="border-radius: 8px; font-weight: 600; padding: 0.35rem 0.8rem; font-size: 0.82rem;">
                                    <i class="fa-solid fa-eye text-rounder-orange"></i>
                                    <span>{{ __('View details') }}</span>
                                </a>
                                @adminCan('admin.service-orders.destroy','DELETE')
                                    @include('admin.partials.delete-button', ['action' => route('admin.service-orders.destroy', $order->id)])
                                @endadminCan
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body p-3 p-lg-4">
                            <div class="row g-3 g-lg-4 align-items-stretch">
                                <!-- Order Information (Right Column on RTL) -->
                                <div class="col-lg-6 col-xl-6">
                                    <div class="h-100 d-flex flex-column gap-3 justify-content-between">
                                        <!-- Resident & Property Info -->
                                        <div class="p-3 rounded-3" style="background: #f8faf9; border: 1px solid #e5ede9;">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-sm-6">
                                                    <div class="small text-secondary mb-1 fw-medium" style="font-size: 0.78rem;">
                                                        <i class="fa-solid fa-user me-1 text-rounder-orange"></i> {{ __('Resident') }}
                                                    </div>
                                                    <div class="fw-bold text-dark fs-6">{{ $order->resident_name }}</div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="small text-secondary mb-1 fw-medium" style="font-size: 0.78rem;">
                                                        <i class="fa-solid fa-house-chimney me-1 text-rounder-orange"></i> {{ __('Home') }}
                                                    </div>
                                                    <div class="fw-bold text-dark fs-6">{{ $order->home_name }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Service & Price Info -->
                                        <div class="p-3 rounded-3" style="background: #f8faf9; border: 1px solid #e5ede9;">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                <div>
                                                    <div class="small text-secondary mb-1 fw-medium" style="font-size: 0.78rem;">
                                                        <i class="fa-solid fa-screwdriver-wrench me-1 text-rounder-orange"></i> {{ __('Service') }}
                                                    </div>
                                                    <div class="fw-bold text-dark fs-6">{{ $order->service_name ?? $order->service_code }}</div>
                                                    @if($order->sub_service_name || $order->sub_service_code)
                                                        <div class="mt-1">
                                                            <span class="badge" style="background: rgba(2, 51, 62, 0.08); color: var(--petrol); font-weight: 600; padding: 0.3rem 0.65rem; border-radius: 7px; font-size: 0.78rem;">
                                                                {{ $order->sub_service_name ?? $order->sub_service_code }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="text-sm-end">
                                                    <div class="small text-secondary mb-1 fw-medium" style="font-size: 0.78rem;">{{ __('Total amount') }}</div>
                                                    <div class="fw-bold fs-5" style="color: var(--petrol);">
                                                        {{ number_format($order->total_amount, 2) }}
                                                        <span class="small fw-normal text-secondary">{{ __('SAR') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Notes & Media Preview -->
                                        @if($order->notes || ($order->bookingMedia && $order->bookingMedia->isNotEmpty()))
                                            <div class="d-flex flex-column gap-2">
                                                @if($order->notes)
                                                    <div class="p-2.5 px-3 rounded-3" style="background: #fffdfa; border: 1px solid #fae6cb; border-inline-start: 4px solid var(--orange);">
                                                        <div class="small text-secondary fw-medium mb-1" style="font-size: 0.76rem;">
                                                            <i class="fa-regular fa-comment-dots text-rounder-orange me-1"></i> {{ __('Notes') }}
                                                        </div>
                                                        <div class="small text-dark">{{ $order->notes }}</div>
                                                    </div>
                                                @endif

                                                @if($order->bookingMedia && $order->bookingMedia->isNotEmpty())
                                                    <div class="d-flex flex-wrap align-items-center gap-2 pt-1">
                                                        <span class="small text-secondary fw-semibold" style="font-size: 0.78rem;">
                                                            <i class="fa-solid fa-images text-rounder-orange me-1"></i> {{ __('Photos') }}:
                                                        </span>
                                                        @foreach($order->bookingMedia as $media)
                                                            <a href="{{ $media->url }}" target="_blank" rel="noopener noreferrer" title="{{ __('View full image') }}" class="d-inline-block rounded-3 overflow-hidden border shadow-sm" style="transition: transform 0.15s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                                                                <img src="{{ $media->url }}" alt="Booking photo" width="46" height="46" style="object-fit: cover;">
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Quick Assignment & Update Box (Left Column on RTL) -->
                                <div class="col-lg-6 col-xl-6">
                                    <div class="p-3 rounded-3 h-100 d-flex flex-column justify-content-between" style="background: #fbfdfc; border: 1px solid #dce8e4;">
                                        <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom" style="border-color: #e5ede9 !important;">
                                            <span class="fw-bold small text-petrol d-flex align-items-center gap-1.5" style="font-size: 0.84rem;">
                                                <i class="fa-solid fa-sliders text-rounder-orange"></i>
                                                <span>{{ __('Quick assignment & update') }}</span>
                                            </span>
                                            <span class="text-secondary small" style="font-size: 0.75rem;">
                                                {{ __('Save order update') }}
                                            </span>
                                        </div>

                                        @adminCan('admin.service-orders.update','PUT')
                                        <form method="post" action="{{ route('admin.service-orders.update', $order->id) }}" enctype="multipart/form-data" class="d-flex flex-column gap-2 flex-grow-1 justify-content-between">
                                            @csrf
                                            @method('put')

                                            <div class="row g-2">
                                                <!-- Provider selection -->
                                                <div class="col-sm-6">
                                                    <label class="small text-secondary mb-1 fw-medium" style="font-size: 0.76rem;">{{ __('Choose provider') }}</label>
                                                    <select class="form-select form-select-sm provider-select" name="provider_profile_id" style="border-radius: 8px; border-color: #d2deda;">
                                                        <option value="">{{ __('Choose provider') }}</option>
                                                        @foreach($providers as $provider)
                                                            <option value="{{ $provider->id }}" @selected($order->provider_profile_id === $provider->id)>
                                                                {{ $provider->company_name }} · {{ $provider->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Status selection -->
                                                <div class="col-sm-6">
                                                    <label class="small text-secondary mb-1 fw-medium" style="font-size: 0.76rem;">{{ __('Status') }} <span class="text-danger">*</span></label>
                                                    <select class="form-select form-select-sm order-status" name="booking_status_id" required style="border-radius: 8px; border-color: #d2deda;">
                                                        @foreach($statuses as $status)
                                                            <option value="{{ $status->id }}" data-code="{{ $status->code }}" @selected($order->status_code === $status->code)>
                                                                {{ $status->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Gross Amount -->
                                                <div class="col-sm-6">
                                                    <label class="small text-secondary mb-1 fw-medium" style="font-size: 0.76rem;">{{ __('Final amount') }}</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" step=".01" min="0" max="9999999999.99" name="gross_amount" class="form-control form-control-sm gross-amount" placeholder="{{ __('Final amount for commission') }}" style="border-radius: 8px 0 0 8px; border-color: #d2deda;">
                                                        <span class="input-group-text small text-secondary bg-light" style="border-radius: 0 8px 8px 0; border-color: #d2deda; font-size: 0.75rem;">{{ __('SAR') }}</span>
                                                    </div>
                                                </div>

                                                <!-- Internal Note -->
                                                <div class="col-sm-6">
                                                    <label class="small text-secondary mb-1 fw-medium" style="font-size: 0.76rem;">{{ __('Internal note') }}</label>
                                                    <input name="note" maxlength="2000" class="form-control form-control-sm" placeholder="{{ __('Internal note') }}" style="border-radius: 8px; border-color: #d2deda;">
                                                </div>

                                                <!-- Upload Photos -->
                                                <div class="col-12">
                                                    <label class="small text-secondary mb-1 fw-medium" for="card-img-{{ $order->id }}" style="font-size: 0.76rem;">
                                                        <i class="fa-solid fa-camera me-1"></i>{{ __('Add photos') }}
                                                    </label>
                                                    <input id="card-img-{{ $order->id }}" type="file" name="images[]" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp,image/jpg" multiple style="border-radius: 8px; border-color: #d2deda;">
                                                </div>
                                            </div>

                                            <!-- Submit Button -->
                                            <div class="mt-2 pt-2 border-top" style="border-color: #e8f0ec !important;">
                                                <button class="btn btn-sm btn-primary w-100 fw-bold py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm" style="border-radius: 9px;">
                                                    <i class="fa-solid fa-floppy-disk"></i>
                                                    <span>{{ __('Save order update') }}</span>
                                                </button>
                                            </div>
                                        </form>
                                        @endadminCan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="panel p-5 text-center text-secondary mb-4" style="border-radius: 14px;">
                        <i class="fa-solid fa-clipboard-list fs-1 mb-3 text-secondary opacity-50"></i>
                        <p class="mb-0 fs-6">{{ __('No service orders found') }}</p>
                    </div>
                @endforelse
            </div>

            <!-- ========================================== -->
            <!-- 2. REFINED COMPACT TABLE VIEW              -->
            <!-- ========================================== -->
            <div id="ordersTableView" class="orders-view-pane d-none">
                <div class="panel p-3 p-lg-4 mb-4" style="border-radius: 14px;">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr style="border-bottom: 2px solid #e5ede9;">
                                    <th style="width: 13%;">{{ __('Order') }}</th>
                                    <th style="width: 14%;">{{ __('Resident and home') }}</th>
                                    <th style="width: 15%;">{{ __('Service') }}</th>
                                    <th style="width: 10%;">{{ __('Photos') }}</th>
                                    <th style="width: 11%;">{{ __('Status') }}</th>
                                    <th style="width: 37%;">{{ __('Provider assignment and commissions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    @php
                                        $statusColor = match($order->status_code) {
                                            'completed' => ['bg' => '#eef8f4', 'color' => '#0e6c51', 'border' => '#bde4d5', 'dot' => '#10b981'],
                                            'cancelled' => ['bg' => '#fdf2f2', 'color' => '#b32a2a', 'border' => '#f4c7c7', 'dot' => '#ef4444'],
                                            'in_progress', 'accepted' => ['bg' => '#e8f4f8', 'color' => '#035368', 'border' => '#bce3eb', 'dot' => '#06b6d4'],
                                            default => ['bg' => '#fff8ec', 'color' => '#b45309', 'border' => '#fed7aa', 'dot' => '#f59e0b'],
                                        };
                                        try {
                                            $schedDate = !empty($order->scheduled_at) ? \Carbon\Carbon::parse($order->scheduled_at)->translatedFormat('d M Y · h:i A') : '—';
                                        } catch (\Throwable $e) {
                                            $schedDate = $order->scheduled_at;
                                        }
                                    @endphp
                                    <tr style="border-bottom: 1px solid #edf3f0;">
                                        <!-- Order info -->
                                        <td class="align-top py-3">
                                            <a href="{{ route('admin.service-orders.show', $order->id) }}" class="fw-bold text-decoration-none fs-6 d-inline-block mb-1" style="color: var(--petrol);">
                                                #{{ $order->id }}
                                            </a>
                                            <div class="small text-secondary mb-1" style="font-size: 0.78rem;">
                                                <i class="fa-regular fa-calendar text-rounder-orange me-1"></i>{{ $schedDate }}
                                            </div>
                                            @if($order->notes)
                                                <div class="small text-muted text-truncate mb-2" style="max-width: 160px; font-size: 0.75rem;" title="{{ $order->notes }}">
                                                    <i class="fa-regular fa-comment-dots text-rounder-orange me-1"></i>{{ $order->notes }}
                                                </div>
                                            @endif
                                            <div>
                                                <a href="{{ route('admin.service-orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary py-1 px-2 d-inline-flex align-items-center gap-1" style="font-size: 0.75rem; border-radius: 7px;">
                                                    <i class="fa-solid fa-eye text-rounder-orange"></i>
                                                    <span>{{ __('View details') }}</span>
                                                </a>
                                            </div>
                                        </td>

                                        <!-- Resident & Home -->
                                        <td class="align-top py-3">
                                            <div class="fw-bold text-dark fs-6">{{ $order->resident_name }}</div>
                                            <div class="small text-secondary mt-1" style="font-size: 0.78rem;">
                                                <i class="fa-solid fa-house-chimney text-rounder-orange me-1"></i>{{ $order->home_name }}
                                            </div>
                                        </td>

                                        <!-- Service & Price -->
                                        <td class="align-top py-3">
                                            <div class="fw-bold text-dark">{{ $order->service_name ?? $order->service_code }}</div>
                                            @if($order->sub_service_name || $order->sub_service_code)
                                                <div class="mt-1">
                                                    <span class="badge" style="background: rgba(2, 51, 62, 0.08); color: var(--petrol); font-weight: 600; padding: 0.25rem 0.55rem; border-radius: 6px; font-size: 0.74rem;">
                                                        {{ $order->sub_service_name ?? $order->sub_service_code }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="fw-bold mt-2" style="color: var(--petrol); font-size: 0.95rem;">
                                                {{ number_format($order->total_amount, 2) }}
                                                <span class="small fw-normal text-secondary" style="font-size: 0.78rem;">{{ __('SAR') }}</span>
                                            </div>
                                        </td>

                                        <!-- Photos -->
                                        <td class="align-top py-3">
                                            @if($order->bookingMedia && $order->bookingMedia->isNotEmpty())
                                                <div class="d-flex flex-wrap gap-1.5 align-items-center" style="max-width: 120px;">
                                                    @foreach($order->bookingMedia as $media)
                                                        <a href="{{ $media->url }}" target="_blank" rel="noopener noreferrer" title="{{ __('View full image') }}" class="d-inline-block rounded-2 overflow-hidden border shadow-sm">
                                                            <img src="{{ $media->url }}" alt="Booking photo" width="38" height="38" style="object-fit: cover;">
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-secondary small">—</span>
                                            @endif
                                        </td>

                                        <!-- Status -->
                                        <td class="align-top py-3">
                                            <span class="badge rounded-pill d-inline-flex align-items-center gap-1.5" style="background: {{ $statusColor['bg'] }}; color: {{ $statusColor['color'] }}; border: 1px solid {{ $statusColor['border'] }}; font-size: 0.78rem; font-weight: 600; padding: 0.35rem 0.75rem;">
                                                <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $statusColor['dot'] }};"></span>
                                                {{ $order->status_name }}
                                            </span>
                                            @if($order->provider_name)
                                                <div class="small text-secondary mt-2 text-truncate" style="max-width: 130px; font-size: 0.74rem;" title="{{ $order->provider_name }}">
                                                    <i class="fa-solid fa-truck-fast text-rounder-orange me-1"></i>{{ $order->provider_name }}
                                                </div>
                                            @endif
                                        </td>

                                        <!-- 2-Column Compact Quick Form -->
                                        <td class="align-top py-3" style="min-width: 360px;">
                                            <div class="p-2.5 rounded-3" style="background: #fbfdfc; border: 1px solid #dce8e4;">
                                                @adminCan('admin.service-orders.update','PUT')
                                                <form method="post" action="{{ route('admin.service-orders.update', $order->id) }}" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('put')
                                                    <div class="row g-2 mb-2">
                                                        <div class="col-6">
                                                            <select class="form-select form-select-sm provider-select" name="provider_profile_id" style="border-radius: 8px; border-color: #d2deda; font-size: 0.8rem;">
                                                                <option value="">{{ __('Choose provider') }}</option>
                                                                @foreach($providers as $provider)
                                                                    <option value="{{ $provider->id }}" @selected($order->provider_profile_id === $provider->id)>
                                                                        {{ $provider->company_name }} · {{ $provider->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <select class="form-select form-select-sm order-status" name="booking_status_id" required style="border-radius: 8px; border-color: #d2deda; font-size: 0.8rem;">
                                                                @foreach($statuses as $status)
                                                                    <option value="{{ $status->id }}" data-code="{{ $status->code }}" @selected($order->status_code === $status->code)>
                                                                        {{ $status->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <input type="number" step=".01" min="0" max="9999999999.99" name="gross_amount" class="form-control form-control-sm gross-amount" placeholder="{{ __('Final amount for commission') }}" style="border-radius: 8px; border-color: #d2deda; font-size: 0.8rem;">
                                                        </div>
                                                        <div class="col-6">
                                                            <input name="note" maxlength="2000" class="form-control form-control-sm" placeholder="{{ __('Internal note') }}" style="border-radius: 8px; border-color: #d2deda; font-size: 0.8rem;">
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="d-flex align-items-center gap-1.5">
                                                                <label class="small text-secondary mb-0 text-nowrap" for="table-img-{{ $order->id }}" style="font-size: 0.74rem;">
                                                                    <i class="fa-solid fa-camera me-1"></i>{{ __('Add photos') }}:
                                                                </label>
                                                                <input id="table-img-{{ $order->id }}" type="file" name="images[]" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp,image/jpg" multiple style="border-radius: 8px; border-color: #d2deda; font-size: 0.76rem;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between gap-2 pt-1 border-top" style="border-color: #e8f0ec !important;">
                                                        <button class="btn btn-sm btn-primary flex-grow-1 fw-bold d-inline-flex align-items-center justify-content-center gap-1" style="border-radius: 8px; padding: 0.35rem 0.8rem; font-size: 0.82rem;">
                                                            <i class="fa-solid fa-floppy-disk"></i>
                                                            <span>{{ __('Save order update') }}</span>
                                                        </button>
                                                        @adminCan('admin.service-orders.destroy','DELETE')
                                                            @include('admin.partials.delete-button', ['action' => route('admin.service-orders.destroy', $order->id)])
                                                        @endadminCan
                                                    </div>
                                                </form>
                                                @endadminCan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-secondary">
                                            <i class="fa-solid fa-clipboard-list fs-1 mb-2 text-secondary opacity-50"></i>
                                            <div>{{ __('No service orders found') }}</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Synchronize gross amount required status dynamically
document.querySelectorAll('.order-status').forEach((select) => {
    const form = select.closest('form');
    const amount = form ? form.querySelector('.gross-amount') : null;
    if (!amount) return;
    const sync = () => {
        const complete = select.options[select.selectedIndex]?.dataset.code === 'completed';
        amount.required = complete;
        amount.placeholder = complete ? @json(__('Final amount for commission')) : @json(__('Optional final amount'));
    };
    select.addEventListener('change', sync);
    sync();
});

// View switch handler (Cards vs Table) with localStorage memory
function switchOrdersView(mode) {
    const cardsPane = document.getElementById('ordersCardsView');
    const tablePane = document.getElementById('ordersTableView');
    const btnCards = document.getElementById('btnViewCards');
    const btnTable = document.getElementById('btnViewTable');

    if (!cardsPane || !tablePane || !btnCards || !btnTable) return;

    if (mode === 'table') {
        cardsPane.classList.add('d-none');
        tablePane.classList.remove('d-none');
        btnTable.classList.add('active');
        btnCards.classList.remove('active');
        try { localStorage.setItem('rounder_orders_view', 'table'); } catch(e){}
    } else {
        tablePane.classList.add('d-none');
        cardsPane.classList.remove('d-none');
        btnCards.classList.add('active');
        btnTable.classList.remove('active');
        try { localStorage.setItem('rounder_orders_view', 'cards'); } catch(e){}
    }
}

// Restore user view preference
document.addEventListener('DOMContentLoaded', function() {
    try {
        const savedView = localStorage.getItem('rounder_orders_view');
        if (savedView === 'table') {
            switchOrdersView('table');
        }
    } catch(e) {}
});
</script>
@endpush

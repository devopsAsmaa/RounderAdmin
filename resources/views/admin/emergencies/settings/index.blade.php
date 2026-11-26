@extends('layouts.admin')
@section('title', __('Emergency requests and pricing settings').' · Rounder')

@section('body')
<main class="container py-4">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-truck-medical text-danger" style="font-size: 1.35rem;"></i>
                <span>{{ __('Emergency requests settings') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Define emergency service fees, pricing, and geographic coverage.') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($availableRegions->isNotEmpty())
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5 rounded-3" data-bs-toggle="modal" data-bs-target="#createSettingModal">
                <i class="fa-solid fa-plus"></i>
                <span>{{ __('Add region') }}</span>
            </button>
            @endif
            <button type="submit" form="bulkEmergencyForm" class="btn btn-primary d-inline-flex align-items-center gap-2 rounded-3 shadow-sm px-4 py-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span class="fw-bold">{{ __('Save changes') }}</span>
            </button>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-lg-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom flex-wrap gap-2">
            <div>
                <h2 class="h6 fw-bold text-dark mb-0">{{ __('Emergency pricing schedule by geographic region') }}</h2>
                <p class="text-muted small mb-0">{{ __('Adjust emergency pricing and activation status directly in the table and save.') }}</p>
            </div>
            <span class="badge bg-light text-secondary border font-monospace">{{ __('Available regions: :count', ['count' => $settings->count()]) }}</span>
        </div>

        <form id="bulkEmergencyForm" method="POST" action="{{ route('admin.emergency-settings.bulk-update') }}">
            @csrf
            @method('PUT')

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light-subtle">
                        <tr class="text-secondary small fw-bold">
                            <th style="min-width: 200px;">{{ __('Region') }}</th>
                            <th style="min-width: 220px;">{{ __('Emergency fee (Price)') }}</th>
                            <th style="min-width: 170px;">{{ __('Estimated response time') }}</th>
                            <th style="min-width: 140px;">{{ __('Service status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settings as $idx => $setting)
                            @php
                                $regionName = $setting->region?->translations->firstWhere('locale', $locale)?->name 
                                            ?? $setting->region?->translations->firstWhere('locale', 'ar')?->name 
                                            ?? $setting->region?->code ?? __('Unspecified region');
                            @endphp
                            <tr>
                                {{-- المنطقة --}}
                                <td>
                                    <input type="hidden" name="settings[{{ $idx }}][id]" value="{{ $setting->id }}">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary" style="width: 38px; height: 38px; flex-shrink: 0;">
                                            <i class="fa-solid fa-location-dot"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-6">{{ $regionName }}</div>
                                            <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">{{ $setting->region?->code }}</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- السعر (إدخال مباشر) --}}
                                <td>
                                    <div class="input-group" style="max-width: 210px;">
                                        <input type="number" step="0.5" min="0" max="99999" 
                                               class="form-control font-monospace fw-bold fs-6 text-center border-primary-subtle" 
                                               name="settings[{{ $idx }}][price]" 
                                               value="{{ old('settings.'.$idx.'.price', $setting->price) }}" 
                                               required 
                                               style="background: #fafcfb;">
                                        <span class="input-group-text bg-light text-muted small fw-semibold">{{ __('SAR') }}</span>
                                    </div>
                                </td>

                                {{-- وقت الاستجابة --}}
                                <td>
                                    <div class="input-group input-group-sm" style="max-width: 140px;">
                                        <input type="number" step="1" min="5" max="1440" 
                                               class="form-control text-center font-monospace" 
                                               name="settings[{{ $idx }}][estimated_time_minutes]" 
                                               value="{{ old('settings.'.$idx.'.estimated_time_minutes', $setting->estimated_time_minutes) }}">
                                        <span class="input-group-text bg-light text-muted">{{ __('min') }}</span>
                                    </div>
                                </td>

                                {{-- حالة التفعيل --}}
                                <td>
                                    <div class="form-check form-switch m-0 d-inline-flex align-items-center gap-2">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                               id="active_switch_{{ $setting->id }}" 
                                               name="settings[{{ $idx }}][is_active]" 
                                               value="1" 
                                               @checked(old('settings.'.$idx.'.is_active', $setting->is_active))>
                                        <label class="form-check-label fw-semibold small {{ $setting->is_active ? 'text-success' : 'text-secondary' }}" for="active_switch_{{ $setting->id }}">
                                            {{ $setting->is_active ? __('Active') : __('Inactive') }}
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    {{ __('No regions added yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</main>

{{-- Modal إضافة منطقة جديدة --}}
@if($availableRegions->isNotEmpty())
<div class="modal fade" id="createSettingModal" tabindex="-1" aria-labelledby="createSettingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom pb-3">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="createSettingModalLabel">{{ __('Add region pricing') }}</h5>
                    <span class="text-muted small">{{ __('Set emergency pricing for a new region') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <form method="POST" action="{{ route('admin.emergency-settings.store') }}">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="new_region_id">{{ __('Select region') }} <span class="text-danger">*</span></label>
                        <select class="form-select" id="new_region_id" name="region_id" required>
                            <option value="">{{ __('-- Select region --') }}</option>
                            @foreach($availableRegions as $avail)
                                @php
                                    $availName = $avail->translations->firstWhere('locale', $locale)?->name 
                                               ?? $avail->translations->firstWhere('locale', 'ar')?->name 
                                               ?? $avail->code;
                                @endphp
                                <option value="{{ $avail->id }}" @selected(old('region_id') == $avail->id)>
                                    {{ $availName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="new_price">{{ __('Emergency service price') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.5" min="0" max="99999" class="form-control form-control-lg font-monospace fw-bold" id="new_price" name="price" value="{{ old('price', '150.00') }}" required placeholder="150.00">
                            <span class="input-group-text bg-light text-muted">{{ __('SAR') }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted" for="new_time">{{ __('Estimated response time (Optional)') }}</label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="1" min="5" max="1440" class="form-control" id="new_time" name="estimated_time_minutes" value="{{ old('estimated_time_minutes', '30') }}">
                            <span class="input-group-text bg-light text-muted">{{ __('min') }}</span>
                        </div>
                    </div>

                    <div class="form-check form-switch pt-1">
                        <input class="form-check-input" type="checkbox" role="switch" id="new_active" name="is_active" value="1" checked>
                        <label class="form-check-label fw-semibold small" for="new_active">{{ __('Activate service immediately') }}</label>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4">
                        {{ __('Add') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

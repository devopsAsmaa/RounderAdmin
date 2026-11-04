@extends('layouts.admin')
@section('title', ($emergency ? __('Create emergency request') : __('Create service order')) . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header & Navigation --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a class="btn btn-sm btn-outline-secondary rounded-pill px-3" href="{{ route($emergency ? 'admin.emergencies.index' : 'admin.service-orders.index') }}">
                    <i class="fa-solid fa-arrow-right me-1"></i> {{ $emergency ? __('Emergency requests') : __('Service orders') }}
                </a>
                <span class="text-muted">/</span>
                <span class="text-secondary small fw-semibold">{{ $emergency ? __('Create emergency request') : __('Create service order') }}</span>
            </div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                @if($emergency)
                    <i class="fa-solid fa-triangle-exclamation text-danger"></i>
                    <span>{{ __('Create emergency request') }}</span>
                @else
                    <i class="fa-solid fa-bell-concierge text-rounder-orange"></i>
                    <span>{{ __('Create service order') }}</span>
                @endif
            </h1>
            <p class="text-secondary mb-0 small">
                {{ $emergency ? __('Submit an urgent emergency request for immediate provider dispatch.') : __('Create and schedule a new service order for a resident unit.') }}
            </p>
        </div>
    </div>

    @include('admin.partials.feedback')

    @if($memberships->isEmpty())
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" style="border-radius: 10px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ __('Add a resident linked to a property before creating a request.') }}</span>
        </div>
    @endif

    @if($options->isEmpty())
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" style="border-radius: 10px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ __('No active request types are available.') }}</span>
        </div>
    @endif

    {{-- Main Panel Form --}}
    <div class="panel p-3 p-lg-4 shadow-sm" style="border-top: 3px solid {{ $emergency ? '#dc3545' : 'var(--orange, #fb822f)' }}; border-radius: 14px;">
        <form method="post" action="{{ route($emergency ? 'admin.emergencies.store' : 'admin.service-orders.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- 1. Resident & Property Details --}}
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-user-gear text-rounder-orange"></i>
                    <span>{{ __('Resident and property details') }}</span>
                </h6>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-secondary" for="membership-search">
                            <i class="fa-solid fa-magnifying-glass me-1 text-muted"></i> {{ __('Search resident or property') }}
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input id="membership-search" type="search" class="form-control border-start-0" placeholder="{{ __('Search resident or property') }}..." style="border-radius: 10px 0 0 10px;">
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-secondary" for="resident_home_id">
                            <i class="fa-solid fa-building-user text-rounder-orange me-1"></i> {{ __('Resident and unit') }} <span class="text-danger">*</span>
                        </label>
                        <select id="resident_home_id" name="resident_home_id" class="form-select @error('resident_home_id') is-invalid @enderror" required style="border-radius: 10px;">
                            <option value="">{{ __('Select a resident unit') }}</option>
                            @foreach($memberships as $membership)
                                <option data-phone="{{ $membership->contact_phone ?? '' }}" value="{{ $membership->id }}" @selected(old('resident_home_id') == $membership->id)>
                                    {{ $membership->resident_name }} · {{ $membership->home_name }} · {{ $membership->unit_label }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small text-muted mt-1">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i> {{ __('The resident and property are resolved from the selected unit.') }}
                        </div>
                        @error('resident_home_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="my-4 text-muted opacity-25">

            {{-- 2. Request Details --}}
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-clipboard-list text-rounder-orange"></i>
                    <span>{{ __('Request details') }}</span>
                </h6>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-secondary" for="request_option">
                            <i class="fa-solid fa-wrench text-rounder-orange me-1"></i> {{ $emergency ? __('Emergency type') : __('Service') }} <span class="text-danger">*</span>
                        </label>
                        <select id="request_option" name="{{ $emergency ? 'emergency_type_id' : 'service_id' }}" class="form-select @error($emergency ? 'emergency_type_id' : 'service_id') is-invalid @enderror" required style="border-radius: 10px;">
                            <option value="">{{ __('Choose an option') }}</option>
                            @foreach($options as $option)
                                <option value="{{ $option->id }}" @selected(old($emergency ? 'emergency_type_id' : 'service_id') == $option->id)>
                                    {{ $option->name ?? $option->code }}
                                </option>
                            @endforeach
                        </select>
                        @error($emergency ? 'emergency_type_id' : 'service_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($emergency)
                        <div class="col-12 col-md-6">
                            <label for="contact_phone" class="form-label small fw-semibold text-secondary">
                                <i class="fa-solid fa-phone text-rounder-orange me-1"></i> {{ __('Contact phone') }} <span class="text-danger">*</span>
                            </label>
                            <input id="contact_phone" name="contact_phone" type="tel" dir="ltr" value="{{ old('contact_phone') }}" class="form-control font-monospace @error('contact_phone') is-invalid @enderror" pattern="05[0-9]{8}" maxlength="10" placeholder="05XXXXXXXX" required style="border-radius: 10px;">
                            @error('contact_phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="location_details" class="form-label small fw-semibold text-secondary">
                                <i class="fa-solid fa-location-dot text-rounder-orange me-1"></i> {{ __('Location details') }} <span class="text-danger">*</span>
                            </label>
                            <input id="location_details" name="location_details" value="{{ old('location_details') }}" class="form-control @error('location_details') is-invalid @enderror" minlength="4" maxlength="255" placeholder="{{ __('e.g. Building A, Floor 2, Apartment 204') }}" required style="border-radius: 10px;">
                            @error('location_details')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label small fw-semibold text-secondary">
                                <i class="fa-regular fa-comment-dots text-rounder-orange me-1"></i> {{ __('Emergency description') }} <span class="text-danger">*</span>
                            </label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4" minlength="10" maxlength="3000" placeholder="{{ __('Describe the emergency situation in detail...') }}" required style="border-radius: 10px;">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check p-3 bg-danger-subtle rounded-3 border border-danger-subtle">
                                <input id="confirm_emergency" type="checkbox" name="confirm_emergency" value="1" class="form-check-input me-2" @checked(old('confirm_emergency')) required>
                                <label for="confirm_emergency" class="form-check-label text-danger fw-semibold">
                                    {{ __('I confirm this is an emergency request.') }}
                                </label>
                            </div>
                            @error('confirm_emergency')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        <div class="col-12 col-md-6">
                            <label for="scheduled_at" class="form-label small fw-semibold text-secondary">
                                <i class="fa-regular fa-calendar-check text-rounder-orange me-1"></i> {{ __('Scheduled date and time') }} <span class="text-danger">*</span>
                            </label>
                            <input id="scheduled_at" name="scheduled_at" type="datetime-local" class="form-control font-monospace @error('scheduled_at') is-invalid @enderror" value="{{ old('scheduled_at') }}" required style="border-radius: 10px;">
                            @error('scheduled_at')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label small fw-semibold text-secondary">
                                <i class="fa-regular fa-note-sticky text-rounder-orange me-1"></i> {{ __('Notes') }}
                            </label>
                            <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" maxlength="2000" rows="3" placeholder="{{ __('Add any special instructions or resident notes...') }}" style="border-radius: 10px;">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="images" class="form-label small fw-semibold text-secondary">
                                <i class="fa-regular fa-images text-rounder-orange me-1"></i> {{ __('Photos / Attachments') }}
                            </label>
                            <input id="images" name="images[]" type="file" class="form-control @error('images.*') is-invalid @enderror" accept="image/jpeg,image/png,image/webp,image/jpg" multiple style="border-radius: 10px;">
                            <div class="form-text small text-muted">{{ __('You can attach photos to describe the problem or requested service.') }}</div>
                        </div>

                        <div class="col-12">
                            <div class="p-3 bg-light-subtle rounded-3 border d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-info text-primary"></i>
                                <span class="small text-secondary">{{ __('The service price is taken from the current service catalogue.') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="d-flex justify-content-end gap-2 pt-4 border-top">
                <a class="btn btn-outline-secondary px-4" href="{{ route($emergency ? 'admin.emergencies.index' : 'admin.service-orders.index') }}" style="border-radius: 10px;">
                    {{ __('Cancel') }}
                </a>
                <button class="btn {{ $emergency ? 'btn-danger' : 'btn-brand' }} px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm" type="submit" @disabled($memberships->isEmpty() || $options->isEmpty()) style="border-radius: 10px;">
                    <i class="fa-solid fa-check"></i>
                    <span>{{ $emergency ? __('Create emergency request') : __('Create request') }}</span>
                </button>
            </div>
        </form>
    </div>
</main>
@endsection

@push('scripts')
<script>
(() => {
    const select = document.getElementById('resident_home_id');
    const searchInput = document.getElementById('membership-search');

    if (searchInput && select) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLocaleLowerCase();
            for (const option of select.options) {
                option.hidden = option.value !== '' && !option.text.toLocaleLowerCase().includes(query);
            }
        });
    }

    const phone = document.getElementById('contact_phone');
    if (select && phone) {
        select.addEventListener('change', () => {
            if (!phone.value) {
                phone.value = select.selectedOptions[0]?.dataset.phone || '';
            }
        });
    }
})();
</script>
@endpush

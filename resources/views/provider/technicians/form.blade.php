@extends('layouts.provider')
@section('title', $technician ? __('Edit technician') : __('Add technician'))
@section('body')
<div class="mb-4">
    <a href="{{ route('provider.technicians.index') }}" class="text-decoration-none">{{ __('Technicians') }}</a>
    <h1 class="h3 fw-bold mt-2">{{ $technician ? __('Edit technician') : __('Add technician') }}</h1>
</div>

<form method="post" action="{{ $technician ? route('provider.technicians.update', $technician->id) : route('provider.technicians.store') }}" class="panel p-3 p-lg-4">
    @csrf
    @if($technician) @method('put') @endif
    @php
        $selectedServices = old('service_ids', $technician?->technicianServices->modelKeys() ?? []);
        $selectedSubServices = old('sub_service_ids', $technician?->technicianSubServices->modelKeys() ?? []);
    @endphp
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="name">{{ __('Name') }}</label>
            <input class="form-control" id="name" name="name" value="{{ old('name', $technician?->name) }}" maxlength="120" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="phone">{{ __('Phone') }}</label>
            <input class="form-control" id="phone" name="phone" type="tel" value="{{ old('phone', $technician?->phone) }}" dir="ltr" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="password">{{ $technician ? __('New password (leave blank to keep current)') : __('Password') }}</label>
            <input class="form-control" id="password" name="password" type="password" minlength="8" autocomplete="new-password" {{ $technician ? '' : 'required' }}>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="password_confirmation">{{ __('Confirm password') }}</label>
            <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" {{ $technician ? '' : 'required' }}>
        </div>
        <div class="col-12">
            <h2 class="h5 fw-bold">{{ __('Select services') }}</h2>
            <p class="text-secondary">{{ __('Choose the primary services and, optionally, the sub-services this technician handles.') }}</p>
            @if($services->isEmpty())
                <div class="alert alert-warning mb-0">{{ __('Add services to your company profile before creating a technician.') }}</div>
            @else
                <div class="row g-3">
                    @foreach($services as $service)
                        @php($serviceName = $service->translations->firstWhere('locale', app()->getLocale())?->name ?? $service->code)
                        <div class="col-md-6">
                            <section class="border rounded-3 p-3 h-100">
                                <label class="form-check fw-semibold mb-2">
                                    <input class="form-check-input technician-service" type="checkbox" name="service_ids[]" value="{{ $service->id }}"
                                        @checked(in_array($service->id, $selectedServices))>
                                    <span class="form-check-label">{{ $serviceName }}</span>
                                </label>
                                @if($service->subServices->isNotEmpty())
                                    <div class="technician-sub-services border-top pt-2" data-service-id="{{ $service->id }}">
                                        @foreach($service->subServices as $subService)
                                            <label class="form-check ms-3 small">
                                                <input class="form-check-input" type="checkbox" name="sub_service_ids[]" value="{{ $subService->id }}"
                                                    @checked(in_array($subService->id, $selectedSubServices))>
                                                <span class="form-check-label">{{ $subService->translations->firstWhere('locale', app()->getLocale())?->name ?? $subService->code }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </section>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary" type="submit">{{ __('Save') }}</button>
            <a class="btn btn-outline-secondary" href="{{ route('provider.technicians.index') }}">{{ __('Cancel') }}</a>
        </div>
    </div>
</form>
@push('scripts')
<script>
    document.querySelectorAll('.technician-service').forEach((serviceInput) => {
        const group = document.querySelector(`.technician-sub-services[data-service-id="${serviceInput.value}"]`);
        if (!group) return;
        const syncSubServices = () => {
            group.hidden = !serviceInput.checked;
            group.querySelectorAll('input').forEach((input) => { input.disabled = !serviceInput.checked; });
        };
        serviceInput.addEventListener('change', syncSubServices);
        syncSubServices();
    });
</script>
@endpush
@endsection

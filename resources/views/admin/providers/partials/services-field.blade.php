@php
    $selectedServiceIds = old('service_ids', isset($provider) ? $provider->services->modelKeys() : []);
    $selectedServiceIds = array_map('strval', (array) $selectedServiceIds);
@endphp
<div class="col-12">
    <label class="form-label">{{ __('Services') }}</label>
    <input type="hidden" name="_service_ids_submitted" value="1">
    <div class="row g-2">
        @foreach($services as $service)
            @php
                $serviceName = $service->translations->firstWhere('locale', app()->getLocale())?->name ?? $service->code;
            @endphp
            <div class="col-md-4">
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="service_ids[]" value="{{ $service->id }}"
                        @checked(in_array((string) $service->id, $selectedServiceIds, true))>
                    <span class="form-check-label">{{ $serviceName }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

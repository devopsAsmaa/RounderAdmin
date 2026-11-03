@php
    $names = $names ?? collect();
    $locale = app()->getLocale();
@endphp
<form method="post" action="{{ $action }}">@csrf
@if(($method ?? 'POST') !== 'POST')@method($method)@endif
<input type="hidden" name="country_code" value="{{ old('country_code', $city?->country_code?->value ?? $city?->country_code ?? 'SA') }}">
<div class="row g-3">
<div class="col-md-4">
    <label class="form-label">{{ __('City code') }}</label>
    <input name="code" value="{{ old('code', $city?->code) }}" class="form-control" required maxlength="60" pattern="[A-Za-z0-9_-]+" placeholder="e.g. sa-1, ruh, dmm">
</div>
<div class="col-md-4">
    <label class="form-label">{{ __('Region') }}</label>
    <select name="region_id" class="form-select">
        <option value="">{{ __('Select a region') }}</option>
        @foreach($regions as $region)
            @php
                $rName = $region->translations->firstWhere('locale', $locale)?->name
                    ?? $region->translations->firstWhere('locale', 'ar')?->name
                    ?? $region->code;
            @endphp
            <option value="{{ $region->id }}" @selected(old('region_id', $city?->region_id) == $region->id)>
                {{ $rName }} ({{ $region->code }})
            </option>
        @endforeach
    </select>
</div>
<div class="col-md-4">
    <label class="form-label">{{ __('Status') }}</label>
    <select name="is_active" class="form-select">
        <option value="1" @selected((int) old('is_active', $city?->is_active ?? 1) === 1)>{{ __('Active') }}</option>
        <option value="0" @selected((int) old('is_active', $city?->is_active ?? 1) === 0)>{{ __('Inactive') }}</option>
    </select>
</div>

@foreach(['ar' => 'العربية', 'en' => 'English', 'ur' => 'اردو'] as $langCode => $label)
<div class="col-md-4">
    <label class="form-label">{{ __('City name') }} · {{ $label }}</label>
    <input name="name_{{ $langCode }}" value="{{ old('name_'.$langCode, $names[$langCode] ?? '') }}" class="form-control" @required($langCode === 'ar') maxlength="120" placeholder="{{ $label }}">
</div>
@endforeach

<div class="col-12 d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.cities.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
    <button class="btn btn-primary px-4">{{ __('Save') }}</button>
</div>
</div>
</form>


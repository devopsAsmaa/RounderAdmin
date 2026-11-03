@php
    $names = $names ?? collect();
    $locale = app()->getLocale();
@endphp
<form method="post" action="{{ $action }}">@csrf
@if(($method ?? 'POST') !== 'POST')@method($method)@endif
<input type="hidden" name="country_code" value="{{ old('country_code', $region?->country_code?->value ?? $region?->country_code ?? 'SA') }}">
@if($region?->code)
<input type="hidden" name="code" value="{{ $region->code }}">
@endif
<div class="row g-3">
<div class="col-md-12">
    <label class="form-label">{{ __('Status') }}</label>
    <select name="is_active" class="form-select" style="max-width: 260px;">
        <option value="1" @selected((int) old('is_active', $region?->is_active ?? 1) === 1)>{{ __('Active') }}</option>
        <option value="0" @selected((int) old('is_active', $region?->is_active ?? 1) === 0)>{{ __('Inactive') }}</option>
    </select>
</div>

@foreach(['ar' => 'العربية', 'en' => 'English', 'ur' => 'اردو'] as $langCode => $label)
<div class="col-md-4">
    <label class="form-label">{{ __('Region name') }} · {{ $label }}</label>
    <input name="name_{{ $langCode }}" value="{{ old('name_'.$langCode, $names[$langCode] ?? '') }}" class="form-control" @required($langCode === 'ar') maxlength="120" placeholder="{{ $label }}">
</div>
@endforeach

<div class="col-12 d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.regions.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
    <button class="btn btn-primary px-4">{{ __('Save') }}</button>
</div>
</div>
</form>


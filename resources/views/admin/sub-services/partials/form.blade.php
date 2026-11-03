@php
    $names = $names ?? collect();
    $descriptions = $descriptions ?? collect();
    $locale = app()->getLocale();
@endphp
<form method="post" enctype="multipart/form-data" action="{{ $action }}">@csrf
@if(($method ?? 'POST') !== 'POST')@method($method)@endif
<div class="row g-3">
<div class="col-md-4"><label class="form-label">{{ __('Service') }}</label><select name="service_id" class="form-select" required><option value="">{{ __('Choose service') }}</option>@foreach($services as $service)<option value="{{ $service->id }}" @selected(old('service_id', $subService?->service_id)==$service->id)>{{ $service->translations->firstWhere('locale',$locale)?->name ?? $service->code }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">{{ __('Sub service code') }}</label><input name="code" value="{{ old('code', $subService?->code) }}" class="form-control" required maxlength="100" pattern="[A-Za-z0-9_-]+"></div>
<div class="col-md-2"><label class="form-label">{{ __('Sort order') }}</label><input name="sort_order" type="number" min="0" value="{{ old('sort_order', $subService?->sort_order ?? 0) }}" class="form-control"></div>
<div class="col-md-3"><label class="form-label">{{ __('Status') }}</label><select name="is_active" class="form-select"><option value="1" @selected((int) old('is_active', $subService?->is_active ?? 1)===1)>{{ __('Active') }}</option><option value="0" @selected((int) old('is_active', $subService?->is_active ?? 1)===0)>{{ __('Inactive') }}</option></select></div>
@foreach(['ar'=>'العربية','en'=>'English','ur'=>'اردو'] as $code=>$label)
<div class="col-md-4"><label class="form-label">{{ __('Sub service name') }} · {{ $label }}</label><input name="name_{{ $code }}" value="{{ old('name_'.$code, $names[$code] ?? '') }}" class="form-control" @required($code==='ar') maxlength="255"></div>
<div class="col-md-4"><label class="form-label">{{ __('Description') }} · {{ $label }}</label><textarea name="description_{{ $code }}" class="form-control" rows="2" maxlength="2000">{{ old('description_'.$code, $descriptions[$code] ?? '') }}</textarea></div>
<div class="col-md-4"></div>
@endforeach
<div class="col-md-6"><label class="form-label">{{ __('Image') }}</label><input name="image" type="file" accept="image/*" class="form-control">
@if($subService?->image_path)<img class="rounded mt-2" width="64" height="64" style="object-fit:cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($subService->image_path) }}" alt="{{ __('Product image') }}">@endif</div>
<div class="col-md-6 d-flex align-items-end"><button class="btn btn-primary w-100">{{ __('Save sub service') }}</button></div>
</div></form>

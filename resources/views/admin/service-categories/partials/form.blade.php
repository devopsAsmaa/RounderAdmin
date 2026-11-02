@php
    $names = $names ?? collect();
    $locale = app()->getLocale();
@endphp
<form method="post" enctype="multipart/form-data" action="{{ $action }}">@csrf
@if(($method ?? 'POST') !== 'POST')@method($method)@endif
<div class="row g-3">
<div class="col-md-4"><label class="form-label">{{ __('Service category code') }}</label><input name="code" value="{{ old('code', $category?->code) }}" class="form-control" required maxlength="60" pattern="[A-Za-z0-9_-]+"></div>
<div class="col-md-4"><label class="form-label">{{ __('Status') }}</label><select name="is_active" class="form-select"><option value="1" @selected((int) old('is_active', $category?->is_active ?? 1)===1)>{{ __('Active') }}</option><option value="0" @selected((int) old('is_active', $category?->is_active ?? 1)===0)>{{ __('Inactive') }}</option></select></div>
<div class="col-md-4"><label class="form-label">{{ __('Image') }}</label><input name="image" type="file" accept="image/*" class="form-control">
@if($category?->image_path)<img class="rounded mt-2" width="64" height="64" style="object-fit:cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($category->image_path) }}" alt="{{ __('Category image') }}">@endif</div>
@foreach(['ar'=>'العربية','en'=>'English','ur'=>'اردو'] as $code=>$label)
<div class="col-md-4"><label class="form-label">{{ __('Service category name') }} · {{ $label }}</label><input name="name_{{ $code }}" value="{{ old('name_'.$code, $names[$code] ?? '') }}" class="form-control" @required($code==='ar') maxlength="120"></div>
@endforeach
<div class="col-12 d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.service-categories.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
    <button class="btn btn-primary px-4">{{ __('Save') }}</button>
</div>
</div></form>

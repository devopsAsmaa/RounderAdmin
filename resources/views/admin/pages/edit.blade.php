@extends('layouts.admin')
@section('title', __('Edit page').' · Rounder')
@section('body')
<main class="container py-4">
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">{{ __('Pages and policies') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $translationAr?->title ?? $page->slug }}</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">{{ __('Edit page') }}: <span class="text-primary">{{ $translationAr?->title ?? $page->slug }}</span></h1>
    </div>
    <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Back') }}
    </a>
</div>

@include('admin.partials.feedback')

<form method="post" action="{{ route('admin.pages.update', $page->id) }}">
    @csrf
    @method('put')

    <div class="row g-4 mb-4">
        {{-- الترجمة العربية --}}
        <div class="col-lg-6">
            <div class="panel p-3 p-lg-4 h-100 border-start border-4 border-primary">
                <div class="mb-3">
                    <h2 class="h5 mb-0 text-primary">
                        <i class="fa-solid fa-globe me-1"></i> {{ __('Arabic Content') }}
                    </h2>
                </div>

                <div class="mb-3">
                    <label for="title_ar" class="form-label fw-bold">{{ __('Page title in Arabic') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title_ar') is-invalid @enderror" id="title_ar" name="title_ar" value="{{ old('title_ar', $translationAr?->title) }}" dir="rtl" required>
                    @error('title_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="content_ar" class="form-label fw-bold">{{ __('Page content in Arabic') }} <span class="text-danger">*</span></label>
                    <textarea class="form-control font-monospace @error('content_ar') is-invalid @enderror" id="content_ar" name="content_ar" rows="15" dir="rtl" required style="font-size: 0.95rem; line-height: 1.6;">{{ old('content_ar', $translationAr?->content) }}</textarea>
                    @error('content_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- الترجمة الإنجليزية --}}
        <div class="col-lg-6">
            <div class="panel p-3 p-lg-4 h-100 border-start border-4 border-info">
                <div class="mb-3">
                    <h2 class="h5 mb-0 text-info">
                        <i class="fa-solid fa-language me-1"></i> {{ __('English Content') }}
                    </h2>
                </div>

                <div class="mb-3">
                    <label for="title_en" class="form-label fw-bold">{{ __('Page title in English') }}</label>
                    <input type="text" class="form-control @error('title_en') is-invalid @enderror" id="title_en" name="title_en" value="{{ old('title_en', $translationEn?->title) }}" dir="ltr">
                    @error('title_en')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="content_en" class="form-label fw-bold">{{ __('Page content in English') }}</label>
                    <textarea class="form-control font-monospace @error('content_en') is-invalid @enderror" id="content_en" name="content_en" rows="15" dir="ltr" style="font-size: 0.95rem; line-height: 1.6; text-align: left;">{{ old('content_en', $translationEn?->content) }}</textarea>
                    @error('content_en')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="is_active" value="1">

    <div class="panel p-3 p-lg-4 mb-4">
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary px-4">
                {{ __('Cancel') }}
            </a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fa-solid fa-floppy-disk me-1"></i> {{ __('Save changes') }}
            </button>
        </div>
    </div>
</form>
</main>
@endsection


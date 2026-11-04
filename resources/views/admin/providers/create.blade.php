@extends('layouts.admin')
@section('title', __('Add provider').' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                @adminCan('admin.providers.index')
                <a class="btn btn-sm btn-outline-secondary py-1 px-3 rounded-pill" href="{{ route('admin.providers.index') }}">
                    <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-right' : 'fa-arrow-left' }} me-1"></i>
                    {{ __('Providers and contracts') }}
                </a>
                @endadminCan
            </div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-handshake text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Register service provider') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Register a new partner service provider and initial contract.') }}</p>
        </div>
    </div>

    @include('admin.partials.feedback')
    @if($errors->any())
        <div class="alert alert-danger mb-4 shadow-sm" style="border-radius: 14px;">
            <div class="fw-bold mb-1"><i class="fa-solid fa-circle-xmark me-1"></i>{{ __('Please correct the errors below:') }}</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @adminCan('admin.providers.store', 'POST')
    <form method="post" action="{{ route('admin.providers.store') }}">
        @csrf

        {{-- Section 1: Company & Contact Details --}}
        <div class="panel p-3 p-lg-4 mb-4 shadow-sm" style="border-radius: 18px; border-top: 4px solid var(--orange);">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                <span class="d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;border-radius:10px;background:#fff3eb;color:var(--orange);font-size:1.05rem;">
                    <i class="fa-solid fa-building"></i>
                </span>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">{{ __('Company and contact details') }}</h5>
                    <div class="text-muted small">{{ __('Basic registered information for the partner company.') }}</div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">{{ __('Company name') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-city"></i></span>
                        <input class="form-control" name="company_name" required minlength="2" maxlength="160" value="{{ old('company_name') }}" placeholder="{{ __('Company name') }}">
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">{{ __('Contact name') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-user-tie"></i></span>
                        <input class="form-control" name="name" required minlength="2" maxlength="120" value="{{ old('name') }}" placeholder="{{ __('Contact name') }}">
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold">{{ __('Phone') }} (05…) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-phone"></i></span>
                        <input type="tel" dir="ltr" class="form-control font-monospace" name="phone" required pattern="05[0-9]{8}" maxlength="10" placeholder="05XXXXXXXX" value="{{ old('phone') }}">
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold">{{ __('Email address') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" class="form-control font-monospace" name="email" maxlength="254" value="{{ old('email') }}" placeholder="name@company.com">
                    </div>
                </div>

                <div class="col-12 col-md-2">
                    <label class="form-label fw-semibold">{{ __('Account language') }} <span class="text-danger">*</span></label>
                    <select class="form-select" name="locale" required>
                        <option value="ar" @selected(old('locale','ar')==='ar')>العربية</option>
                        <option value="en" @selected(old('locale','ar')==='en')>English</option>
                        <option value="ur" @selected(old('locale','ar')==='ur')>اردو</option>
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <label class="form-label fw-semibold">{{ __('Approval') }} <span class="text-danger">*</span></label>
                    <select class="form-select" name="is_approved" required>
                        <option value="0" @selected(old('is_approved',0)==0)>{{ __('Pending approval') }}</option>
                        <option value="1" @selected(old('is_approved',0)==1)>{{ __('Approved') }}</option>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">{{ __('Temporary password') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-key"></i></span>
                        <input type="password" class="form-control" name="password" required minlength="12" maxlength="72" autocomplete="new-password">
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">{{ __('Confirm password') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" class="form-control" name="password_confirmation" required minlength="12" maxlength="72" autocomplete="new-password">
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Services --}}
        <div class="panel p-3 p-lg-4 mb-4 shadow-sm" style="border-radius: 18px;">
            @include('admin.providers.partials.services-field')
        </div>

        {{-- Section 3: Contract & Terms --}}
        <div class="panel p-3 p-lg-4 mb-4 shadow-sm" style="border-radius: 18px;">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                <span class="d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;border-radius:10px;background:#fff3eb;color:var(--orange);font-size:1.05rem;">
                    <i class="fa-solid fa-file-contract"></i>
                </span>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">{{ __('Contract terms and commissions') }}</h5>
                    <div class="text-muted small">{{ __('Set commissions, operational dates and legal terms.') }}</div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">{{ __('Service commission (%)') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-percent"></i></span>
                        <input type="number" step="0.01" min="0" max="100" class="form-control font-monospace" name="service_commission_percent" required value="{{ old('service_commission_percent', 10) }}">
                        <span class="input-group-text bg-white text-muted">%</span>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">{{ __('Emergency commission (%)') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-bolt text-rounder-orange"></i></span>
                        <input type="number" step="0.01" min="0" max="100" class="form-control font-monospace" name="emergency_commission_percent" required value="{{ old('emergency_commission_percent', 15) }}">
                        <span class="input-group-text bg-white text-muted">%</span>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">{{ __('Contract starts on') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-regular fa-calendar-check text-success"></i></span>
                        <input type="date" class="form-control" name="starts_on" required value="{{ old('starts_on', now()->toDateString()) }}">
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">{{ __('Contract ends on') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-regular fa-calendar-xmark text-danger"></i></span>
                        <input type="date" class="form-control" name="ends_on" value="{{ old('ends_on') }}">
                    </div>
                </div>

                <div class="col-12">
                    @include('admin.providers.partials.terms-editor')
                </div>
            </div>
        </div>

        {{-- Section 4: Action Bar --}}
        <div class="d-flex align-items-center justify-content-between p-3 panel shadow-sm flex-wrap gap-2 mb-4" style="border-radius: 16px;">
            <a href="{{ route('admin.providers.index') }}" class="btn btn-outline-secondary px-4">
                <i class="fa-solid fa-xmark me-1"></i> {{ __('Cancel') }}
            </a>
            <button type="submit" class="btn btn-brand px-5 fw-bold shadow-sm" style="font-size: 0.95rem;">
                <i class="fa-solid fa-plus me-1.5"></i> {{ __('Register provider and contract') }}
            </button>
        </div>
    </form>
    @endadminCan
</main>
@endsection

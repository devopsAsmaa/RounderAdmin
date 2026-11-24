@extends('layouts.provider')
@section('title', __('Settings / Profile') . ' · ' . $profile->company_name)
@section('body')
@php
    $tab1Errors = $errors->hasAny(['company_name','trade_name','provider_type','years_of_experience','name','phone','email','locale','password','password_confirmation','commercial_registration','legal_representative','legal_representative_id']);
    $tab2Errors = $errors->hasAny(['service_ids','service_ids.*']);
    $tab3Errors = $errors->hasAny(array_keys(\App\Modules\Providers\Services\ProviderDocumentService::DOCUMENTS));
    $activeTab = 'company';
    if (!$tab1Errors) {
        if ($tab2Errors) $activeTab = 'services';
        elseif ($tab3Errors) $activeTab = 'documents';
    }

    $selectedServiceIds = old('service_ids', $services->pluck('id')->all());
    $selectedServiceIds = array_map('intval', (array) $selectedServiceIds);
@endphp

<style>
.provider-tabs-nav {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 10px;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    box-shadow: 0 4px 20px rgba(2, 51, 62, 0.05);
}
@media (max-width: 991.98px) {
    .provider-tabs-nav {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 575.98px) {
    .provider-tabs-nav {
        grid-template-columns: 1fr;
    }
}
.provider-tabs-nav .nav-item {
    margin: 0;
    min-width: 0;
}
.provider-tabs-nav .nav-link {
    width: 100%;
    height: 100%;
    border-radius: 14px;
    padding: 14px 16px;
    color: #334155;
    border: 1.5px solid #eef2f6;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.22s ease;
    text-align: start;
    position: relative;
    overflow: hidden;
}
.provider-tabs-nav .nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    right: 0;
    left: 0;
    height: 3px;
    background: transparent;
    transition: background 0.22s ease;
}
.provider-tabs-nav .nav-link:hover {
    background: #fffaf5;
    color: #02333E;
    border-color: #fbd3b6;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(251, 130, 47, 0.08);
}
.provider-tabs-nav .nav-link.has-tab-error {
    border-color: #f87171 !important;
    background: #fef2f2 !important;
    color: #991b1b !important;
}
.provider-tabs-nav .nav-link.has-tab-error::after {
    background: #dc2626;
}
.provider-tabs-nav .nav-link.active {
    background: linear-gradient(135deg, #02333E 0%, #054a59 100%) !important;
    color: #ffffff !important;
    border-color: #02333E !important;
    box-shadow: 0 8px 20px rgba(2, 51, 62, 0.2);
    transform: translateY(-1px);
}
.provider-tabs-nav .nav-link.active::after {
    background: #FB822F;
}
.provider-tabs-nav .step-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #f1f5f9;
    color: #02333E;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
    position: relative;
    transition: all 0.22s ease;
    border: 1px solid #e2e8f0;
}
.provider-tabs-nav .nav-link:hover .step-icon-box {
    background: #fff0e5;
    color: #FB822F;
    border-color: #fbd3b6;
}
.provider-tabs-nav .nav-link.active .step-icon-box {
    background: #FB822F;
    color: #ffffff;
    border-color: #FB822F;
    box-shadow: 0 4px 10px rgba(251, 130, 47, 0.35);
}
.provider-tabs-nav .step-num-pill {
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    color: #FB822F;
    background: rgba(251, 130, 47, 0.1);
    padding: 2px 8px;
    border-radius: 999px;
    display: inline-block;
    margin-bottom: 3px;
}
.provider-tabs-nav .nav-link.active .step-num-pill {
    color: #ffd8bf;
    background: rgba(255, 255, 255, 0.14);
}
.provider-tabs-nav .step-title {
    font-weight: 800;
    font-size: 0.94rem;
    line-height: 1.25;
    margin-bottom: 2px;
}
.provider-tabs-nav .step-sub {
    font-size: 0.76rem;
    opacity: 0.75;
    font-weight: 500;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.doc-upload-card {
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    padding: 14px 16px;
    background: #f8fafc;
    transition: border-color 0.2s ease, background 0.2s ease;
}
.doc-upload-card:hover {
    border-color: #FB822F;
    background: #fffaf5;
}
.doc-upload-card.has-error {
    border-color: #dc3545;
    background: #fff5f5;
}
.doc-upload-card.has-file {
    border-color: #16a34a;
    border-style: solid;
    background: #f0fdf4;
}

.service-checkbox-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.18s ease;
    user-select: none;
}
.service-checkbox-card:hover {
    border-color: #fbd3b6;
    background: #fffdfb;
}
.service-checkbox-card.is-selected {
    border-color: var(--orange, #fb822f);
    background: #fff8f3;
}
.service-checkbox-card.is-locked {
    background: #f1f5f9;
    border-color: #cbd5e1;
    cursor: not-allowed;
    opacity: 0.85;
}
.contract-view-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 15px rgba(0, 52, 62, 0.03);
}
.contract-terms-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    max-height: 480px;
    overflow-y: auto;
    font-size: 0.95rem;
    line-height: 1.85;
    color: #1e293b;
}
</style>

<div class="mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge" style="background: rgba(251, 130, 47, 0.12); color: var(--orange); font-weight: 700; border: 1px solid rgba(251, 130, 47, 0.25);">
                    #{{ $profile->id }}
                </span>
                @if($profile->is_approved)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                        <i class="fa-solid fa-circle-check me-1"></i>{{ __('Approved') }}
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                        <i class="fa-solid fa-hourglass-half me-1"></i>{{ __('Pending approval') }}
                    </span>
                @endif

                @if($profile->contract_accepted_at)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        <i class="fa-solid fa-signature me-1"></i>العقد معتمد ومقبول
                    </span>
                @else
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>بانتظار قبول العقد
                    </span>
                @endif
            </div>

            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2 flex-wrap">
                <i class="fa-solid fa-building-gear text-rounder-orange"></i>
                <span>{{ __('Settings / Profile') }}:</span>
                <span class="text-rounder-orange">{{ $profile->company_name }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Manage your company profile, services, documents, and view your approved contract.') }}</p>
        </div>

        @if($contract)
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('provider.contract.pdf') }}" class="btn btn-outline-danger shadow-sm d-inline-flex align-items-center gap-2" style="border-radius: 10px;">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>{{ __('Download contract PDF') }}</span>
                </a>
            </div>
        @endif
    </div>
</div>

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-radius: 14px;">
        <i class="fa-solid fa-circle-check me-2"></i>{{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

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

<form method="post" action="{{ route('provider.settings.update') }}" id="provider-settings-form" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- 4-Step Tabs Navigation --}}
    <ul class="nav nav-pills provider-tabs-nav mb-4" id="providerSettingsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'company' ? 'active' : '' }} {{ $tab1Errors ? 'has-tab-error' : '' }}" id="tab-company-btn" data-bs-toggle="pill" data-bs-target="#tab-company" type="button" role="tab">
                <span class="step-icon-box"><i class="fa-solid fa-building-user"></i></span>
                <div class="flex-grow-1 min-w-0">
                    <span class="step-num-pill">الخطوة ١</span>
                    <div class="step-title">بيانات المنشأة والممثل</div>
                    <small class="step-sub">الحساب والسجل التجاري</small>
                </div>
                @if($tab1Errors)<span class="badge bg-danger rounded-pill">!</span>@endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'services' ? 'active' : '' }} {{ $tab2Errors ? 'has-tab-error' : '' }}" id="tab-services-btn" data-bs-toggle="pill" data-bs-target="#tab-services" type="button" role="tab">
                <span class="step-icon-box"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                <div class="flex-grow-1 min-w-0">
                    <span class="step-num-pill">الخطوة ٢</span>
                    <div class="step-title">الخدمات المقدمة</div>
                    <small class="step-sub">التخصصات المعتمدة</small>
                </div>
                @if($tab2Errors)<span class="badge bg-danger rounded-pill">!</span>@endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'documents' ? 'active' : '' }} {{ $tab3Errors ? 'has-tab-error' : '' }}" id="tab-documents-btn" data-bs-toggle="pill" data-bs-target="#tab-documents" type="button" role="tab">
                <span class="step-icon-box"><i class="fa-solid fa-folder-open"></i></span>
                <div class="flex-grow-1 min-w-0">
                    <span class="step-num-pill">الخطوة ٣</span>
                    <div class="step-title">المرفقات والمستندات</div>
                    <small class="step-sub">السجل والرخص والشهادات</small>
                </div>
                @if($tab3Errors)<span class="badge bg-danger rounded-pill">!</span>@endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-contract-btn" data-bs-toggle="pill" data-bs-target="#tab-contract" type="button" role="tab">
                <span class="step-icon-box"><i class="fa-solid fa-file-signature"></i></span>
                <div class="flex-grow-1 min-w-0">
                    <span class="step-num-pill">الخطوة ٤</span>
                    <div class="step-title">العقد والعمولات</div>
                    <small class="step-sub">استعراض العقد والموافقة</small>
                </div>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="providerSettingsTabContent">
        {{-- TAB 1: Company & Contact & Legal Rep --}}
        <div class="tab-pane fade {{ $activeTab === 'company' ? 'show active' : '' }}" id="tab-company" role="tabpanel">
            <div class="panel p-3 p-lg-4 mb-4 shadow-sm" style="border-radius: 18px; border-top: 4px solid var(--orange);">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <span class="d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;border-radius:10px;background:#fff3eb;color:var(--orange);font-size:1.05rem;">
                        <i class="fa-solid fa-building"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ __('Company and contact details') }}</h5>
                        <div class="text-muted small">البيانات الأساسية المسجلة لمنشأتكم لدى راوندر.</div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="company_name">{{ __('Company name') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-city"></i></span>
                            <input class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" required minlength="2" maxlength="160" value="{{ old('company_name', $profile->company_name) }}" placeholder="{{ __('Company name') }}">
                        </div>
                        @error('company_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="trade_name">{{ __('Commercial name') }}</label>
                        <input class="form-control @error('trade_name') is-invalid @enderror" id="trade_name" name="trade_name" maxlength="160" value="{{ old('trade_name', $profile->trade_name) }}" placeholder="الاسم التجاري للمنشأة">
                        @error('trade_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="provider_type">{{ __('Provider type') }}</label>
                        <select class="form-select @error('provider_type') is-invalid @enderror" id="provider_type" name="provider_type">
                            <option value="">{{ __('Choose provider type') }}</option>
                            @foreach(['فني مستقل' => __('Independent technician'), 'مؤسسة خدمات' => __('Service establishment'), 'شركة صيانة' => __('Maintenance company')] as $value => $type)
                                <option value="{{ $value }}" @selected(old('provider_type', $profile->provider_type) === $value)>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('provider_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="years_of_experience">{{ __('Years of experience') }}</label>
                        <input class="form-control @error('years_of_experience') is-invalid @enderror" type="number" id="years_of_experience" name="years_of_experience" min="0" max="100" value="{{ old('years_of_experience', $profile->years_of_experience) }}">
                        @error('years_of_experience')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="name">{{ __('Contact name') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-user-tie"></i></span>
                            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" required minlength="2" maxlength="120" value="{{ old('name', $profile->user?->name) }}" placeholder="{{ __('Contact name') }}">
                        </div>
                        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="phone">{{ __('Phone') }} (05…) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-phone"></i></span>
                            <input type="tel" dir="ltr" class="form-control font-monospace @error('phone') is-invalid @enderror" id="phone" name="phone" required pattern="05[0-9]{8}" maxlength="10" placeholder="05XXXXXXXX" value="{{ old('phone', $profile->user?->phone) }}">
                        </div>
                        @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="email">{{ __('Email address') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" class="form-control font-monospace @error('email') is-invalid @enderror" id="email" name="email" maxlength="254" value="{{ old('email', $profile->user?->email) }}" placeholder="name@company.com">
                        </div>
                        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="locale">{{ __('Account language') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('locale') is-invalid @enderror" id="locale" name="locale" required>
                            @php $currentLocale = $profile->user?->locale instanceof \BackedEnum ? $profile->user->locale->value : ($profile->user?->locale ?? 'ar'); @endphp
                            <option value="ar" @selected(old('locale', $currentLocale)==='ar')>العربية</option>
                            <option value="en" @selected(old('locale', $currentLocale)==='en')>English</option>
                            <option value="ur" @selected(old('locale', $currentLocale)==='ur')>اردو</option>
                        </select>
                        @error('locale')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="provider_password">
                            {{ __('New password') }}
                            <span class="badge bg-secondary-subtle text-secondary fw-normal ms-1">{{ __('Optional') }}</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-key"></i></span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="provider_password" minlength="8" maxlength="72" autocomplete="new-password" placeholder="اتركها فارغة للإبقاء على الحالية">
                            <button type="button" class="btn btn-outline-secondary bg-light text-secondary border-start-0 toggle-password-btn" data-target="provider_password" title="إظهار / إخفاء كلمة المرور" style="border-color: #dee2e6;">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="provider_password_confirmation">
                            {{ __('Confirm password') }}
                            <span class="badge bg-secondary-subtle text-secondary fw-normal ms-1">{{ __('Optional') }}</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" class="form-control" name="password_confirmation" id="provider_password_confirmation" minlength="8" maxlength="72" autocomplete="new-password" placeholder="أعد إدخال كلمة المرور للتأكيد">
                            <button type="button" class="btn btn-outline-secondary bg-light text-secondary border-start-0 toggle-password-btn" data-target="provider_password_confirmation" title="إظهار / إخفاء تأكيد كلمة المرور" style="border-color: #dee2e6;">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-12 mt-1">
                        <div class="p-2.5 px-3 rounded-3 border d-flex align-items-center gap-2 small" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                            <i class="fa-solid fa-circle-info text-rounder-orange fs-6"></i>
                            <span class="text-secondary">
                                <strong class="text-dark">{{ __('Password requirements:') }}</strong>
                                {{ __('At least 8 characters, containing uppercase (A-Z), lowercase (a-z), and numbers (0-9).') }}
                                <span class="text-muted">(اترك الحقلين فارغين إذا لم ترغب بتغيير كلمة المرور)</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel p-3 p-lg-4 mb-4 shadow-sm" style="border-radius: 18px;">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <span class="d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;border-radius:10px;background:#eaf4f6;color:#02333E;font-size:1.05rem;">
                        <i class="fa-solid fa-id-card-clip"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ __('Commercial and legal representative details') }}</h5>
                        <div class="text-muted small">تُدرج هذه البيانات تلقائياً في بنود عقدكم الرسمي مع راوندر (الطرف الثاني).</div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="commercial_registration">{{ __('Commercial register') }}</label>
                        <input class="form-control @error('commercial_registration') is-invalid @enderror" id="commercial_registration" name="commercial_registration" maxlength="80" value="{{ old('commercial_registration', $profile->commercial_registration) }}" placeholder="رقم السجل التجاري">
                        @error('commercial_registration')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="legal_representative">{{ __('Legal representative') }}</label>
                        <input class="form-control @error('legal_representative') is-invalid @enderror" id="legal_representative" name="legal_representative" maxlength="160" value="{{ old('legal_representative', $profile->legal_representative) }}" placeholder="اسم الممثل النظامي">
                        @error('legal_representative')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="legal_representative_id">{{ __('Representative national ID') }}</label>
                        <input class="form-control @error('legal_representative_id') is-invalid @enderror" id="legal_representative_id" name="legal_representative_id" maxlength="30" value="{{ old('legal_representative_id', $profile->legal_representative_id) }}" placeholder="رقم الهوية الوطنية / الإقامة">
                        @error('legal_representative_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-primary px-4" data-switch-tab="tab-services-btn">
                        <span>التالي: الخدمات المقدمة</span>
                        <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-left' : 'fa-arrow-right' }} ms-1"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- TAB 2: Services --}}
        <div class="tab-pane fade {{ $activeTab === 'services' ? 'show active' : '' }}" id="tab-services" role="tabpanel">
            <div class="panel p-3 p-lg-4 mb-4 shadow-sm" style="border-radius: 18px; border-top: 4px solid var(--orange);">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;border-radius:10px;background:#fff3eb;color:var(--orange);font-size:1.05rem;">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">{{ __('Assigned services') }}</h5>
                            <div class="text-muted small">حدد الخدمات وتخصصات الصيانة التي تقدمها شركتكم.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5" id="btn-select-all-services" style="border-radius: 8px; font-size: 0.78rem;">
                            <i class="fa-solid fa-check-double me-1 text-rounder-orange"></i>{{ __('Select all') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5" id="btn-deselect-all-services" style="border-radius: 8px; font-size: 0.78rem;">
                            <i class="fa-solid fa-xmark me-1 text-danger"></i>{{ __('Deselect all') }}
                        </button>
                        <span class="pill px-3 py-1 fw-bold" style="background: rgba(251, 130, 47, 0.12); color: var(--orange); border-color: rgba(251, 130, 47, 0.25); font-size: 0.82rem;">
                            <i class="fa-solid fa-circle-check me-1"></i>
                            <span id="services-counter">{{ count($selectedServiceIds) }}</span> {{ __('Selected') }}
                        </span>
                    </div>
                </div>

                <input type="hidden" name="_service_ids_submitted" value="1">

                @foreach($serviceCategories as $category)
                    @if($category->services->isNotEmpty())
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-2 pb-1 border-bottom">
                                <span class="badge bg-light text-dark border px-2 py-1">{{ $category->translations->firstWhere('locale', app()->getLocale())?->name ?? $category->code }}</span>
                            </div>
                            <div class="row g-2.5">
                                @foreach($category->services as $service)
                                    @php
                                        $serviceName = $service->translations->firstWhere('locale', app()->getLocale())?->name ?? $service->code;
                                        $isChecked = in_array($service->id, $selectedServiceIds, true);
                                        $isLocked = isset($ordersByService[$service->id]);
                                    @endphp
                                    <div class="col-12 col-sm-6 col-lg-4">
                                        <label class="service-checkbox-card w-100 {{ $isChecked ? 'is-selected' : '' }} {{ $isLocked ? 'is-locked' : '' }}" title="{{ $isLocked ? 'مرتبطة بطلبات نشطة لا يمكن إزالتها' : '' }}">
                                            <div class="d-flex align-items-center gap-2.5 text-truncate">
                                                <span class="service-icon flex-shrink-0 text-rounder-orange">
                                                    <i class="fa-solid fa-wrench"></i>
                                                </span>
                                                <span class="fw-bold text-dark fs-6 text-truncate">{{ $serviceName }}</span>
                                                @if($isLocked)
                                                    <span class="badge bg-secondary-subtle text-secondary ms-1 small" style="font-size: 0.68rem;">طلب نشط</span>
                                                @endif
                                            </div>
                                            <input class="form-check-input service-check-box flex-shrink-0" type="checkbox" name="service_ids[]" value="{{ $service->id }}"
                                                @checked($isChecked) @disabled($isLocked)>
                                            @if($isLocked)
                                                <input type="hidden" name="service_ids[]" value="{{ $service->id }}">
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach

                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-outline-secondary px-4" data-switch-tab="tab-company-btn">
                        <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-right' : 'fa-arrow-left' }} me-1"></i>
                        <span>السابق: بيانات المنشأة</span>
                    </button>
                    <button type="button" class="btn btn-primary px-4" data-switch-tab="tab-documents-btn">
                        <span>التالي: المرفقات والمستندات</span>
                        <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-left' : 'fa-arrow-right' }} ms-1"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- TAB 3: Documents --}}
        <div class="tab-pane fade {{ $activeTab === 'documents' ? 'show active' : '' }}" id="tab-documents" role="tabpanel">
            <div class="panel p-3 p-lg-4 mb-4 shadow-sm" style="border-radius: 18px; border-top: 4px solid var(--orange);">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <span class="d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;border-radius:10px;background:#fff3eb;color:var(--orange);font-size:1.05rem;">
                        <i class="fa-solid fa-folder-open"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ __('Provider documents (PDF or image, maximum 10 MB)') }}</h5>
                        <div class="text-muted small">المستندات الرسمية المرفوعة لاعتماد منشأتكم؛ يمكنك عرض المرفق الحالي أو تعديله برفع ملف جديد.</div>
                    </div>
                </div>

                <div class="row g-3">
                    @foreach(\App\Modules\Providers\Services\ProviderDocumentService::DOCUMENTS as $field => $label)
                        <div class="col-md-6">
                            <div class="doc-upload-card h-100 {{ $profile->$field ? 'has-file' : '' }} @error($field) has-error @enderror" id="doc-card-{{ $field }}">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2 text-truncate me-2">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 32px; height: 32px; background: {{ $profile->$field ? '#e8f5e9' : '#fff3eb' }}; color: {{ $profile->$field ? '#16a34a' : 'var(--orange)' }}; font-size: 0.95rem;">
                                            <i class="fa-solid {{ $profile->$field ? 'fa-file-circle-check' : 'fa-file-lines' }}"></i>
                                        </span>
                                        <span class="fw-bold text-dark fs-6 text-truncate">{{ __($label) }}</span>
                                    </div>

                                    <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                                        @if($profile->$field)
                                            {{-- زر عرض --}}
                                            <a href="{{ route('provider.documents.show', $field) }}" target="_blank" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1 px-2.5 py-1" style="border-radius: 8px; font-weight: 700; font-size: 0.8rem;">
                                                <i class="fa-solid fa-eye"></i>
                                                <span>عرض</span>
                                            </a>
                                            {{-- زر تعديل --}}
                                            <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 px-2.5 py-1 btn-trigger-doc-input" data-target="{{ $field }}" style="border-radius: 8px; font-weight: 700; font-size: 0.8rem;">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                                <span>تعديل</span>
                                            </button>
                                        @else
                                            {{-- زر رفع في حال لم يكن مرفوعاً --}}
                                            <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 px-2.5 py-1 btn-trigger-doc-input" data-target="{{ $field }}" style="border-radius: 8px; font-weight: 700; font-size: 0.8rem;">
                                                <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                                <span>رفع الملف</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- حقل الإدخال المخفي --}}
                                <input class="d-none doc-file-hidden-input @error($field) is-invalid @enderror" type="file" id="{{ $field }}" name="{{ $field }}" accept=".pdf,.jpg,.jpeg,.png">

                                {{-- صندوق الحالة والتعديل المختار --}}
                                <div class="doc-file-info-preview p-2 rounded-2 mt-2 border {{ $profile->$field ? 'bg-white border-success-subtle' : 'bg-light border-dashed' }}" id="preview-box-{{ $field }}">
                                    <div class="d-flex align-items-center justify-content-between gap-2 small">
                                        <div class="d-flex align-items-center gap-2 text-truncate">
                                            <i class="fa-solid {{ $profile->$field ? 'fa-check text-success' : 'fa-circle-info text-secondary' }} status-icon-{{ $field }}"></i>
                                            <span class="text-truncate status-text-{{ $field }}" style="font-size: 0.82rem;">
                                                {{ $profile->$field ? 'المستند الحالي محفوظ في النظام' : 'لم يتم رفع هذا المستند بعد' }}
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 d-none btn-clear-selected-doc" data-target="{{ $field }}" title="إلغاء التعديل">
                                            <i class="fa-solid fa-xmark fs-6"></i>
                                        </button>
                                    </div>
                                </div>

                                @error($field)<div class="text-danger small mt-1 fw-semibold">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-outline-secondary px-4" data-switch-tab="tab-services-btn">
                        <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-right' : 'fa-arrow-left' }} me-1"></i>
                        <span>السابق: الخدمات المقدمة</span>
                    </button>
                    <button type="button" class="btn btn-primary px-4" data-switch-tab="tab-contract-btn">
                        <span>التالي: العقد والعمولات</span>
                        <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-left' : 'fa-arrow-right' }} ms-1"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- TAB 4: Contract & Commissions (Read-only + Accept) --}}
        <div class="tab-pane fade" id="tab-contract" role="tabpanel">
            <div class="panel p-3 p-lg-4 mb-4 shadow-sm" style="border-radius: 18px; border-top: 4px solid var(--orange);">
                @if($contract)
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;border-radius:12px;background:#fff3eb;color:var(--orange);font-size:1.2rem;">
                                <i class="fa-solid fa-file-contract"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">{{ __('Contract terms and commissions') }}</h5>
                                <div class="text-muted small">
                                    {{ __('Contract reference') }}: <strong class="text-dark font-monospace">{{ $contract->reference }}</strong>
                                    · <span class="badge {{ $contract->status?->code === 'active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-secondary border' }}">{{ __($contract->status?->code ?? 'active') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('provider.contract.pdf') }}" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                                <i class="fa-solid fa-file-pdf"></i>
                                <span>{{ __('Download contract PDF') }}</span>
                            </a>
                            <button class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" type="button" onclick="window.print()">
                                <i class="fa-solid fa-print"></i>
                                <span>{{ __('Print contract') }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- Read-only Commission & Validity Overview --}}
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 rounded-3 border bg-light">
                                <span class="text-muted small d-block mb-1">{{ __('Service commission (%)') }}</span>
                                <div class="h4 fw-bold mb-0 text-dark font-monospace">{{ number_format($contract->service_commission_percent, 2) }} %</div>
                                <span class="badge bg-secondary-subtle text-secondary mt-1 small">نسبة معتمدة من الإدارة</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 rounded-3 border bg-light">
                                <span class="text-muted small d-block mb-1">{{ __('Emergency commission (%)') }}</span>
                                <div class="h4 fw-bold mb-0 text-dark font-monospace">{{ number_format($contract->emergency_commission_percent, 2) }} %</div>
                                <span class="badge bg-secondary-subtle text-secondary mt-1 small">خدمات الطوارئ</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 rounded-3 border bg-light">
                                <span class="text-muted small d-block mb-1">{{ __('Contract starts on') }}</span>
                                <div class="h5 fw-bold mb-0 text-dark font-monospace">{{ $contract->starts_on?->format('Y-m-d') ?? '—' }}</div>
                                <span class="badge bg-success-subtle text-success mt-1 small">تاريخ السريان</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 rounded-3 border bg-light">
                                <span class="text-muted small d-block mb-1">{{ __('Contract ends on') }}</span>
                                <div class="h5 fw-bold mb-0 text-dark font-monospace">{{ $contract->ends_on?->format('Y-m-d') ?? __('Open-ended') }}</div>
                                <span class="badge bg-info-subtle text-info mt-1 small">نهاية العقد</span>
                            </div>
                        </div>
                    </div>

                    {{-- Full Contract Terms Document --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-scroll me-1 text-rounder-orange"></i>نص بنود العقد والشروط المعتمدة:</h6>
                            <span class="text-muted small">العقد محدد ومعتمد من إدارة راوندر ولا يمكن تعديل بنوده من المزود</span>
                        </div>
                        <div class="contract-terms-box">
                            {!! $terms !!}
                        </div>
                    </div>

                    {{-- Contract Acceptance Section --}}
                    @if($profile->contract_accepted_at)
                        <div class="alert alert-success d-flex align-items-center gap-3 p-3 rounded-3 border border-success-subtle">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:40px;height:40px;background:#d1e7dd;color:#0f5132;font-size:1.2rem;">
                                <i class="fa-solid fa-signature"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold mb-1">تمت الموافقة على هذا العقد بنجاح</h6>
                                <p class="mb-0 small text-success-emphasis">
                                    أقر ممثل المنشأة بقبول كافة بنود هذا العقد والالتزام بها بتاريخ: 
                                    <strong dir="ltr">{{ $profile->contract_accepted_at->format('Y-m-d H:i') }}</strong>.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="p-3 rounded-3 border border-warning bg-warning-subtle mb-3">
                            <div class="d-flex align-items-start gap-2.5">
                                <i class="fa-solid fa-circle-exclamation text-warning-emphasis fs-4 mt-0.5"></i>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1 text-dark">الموافقة على العقد وإقراره</h6>
                                    <p class="small text-secondary mb-2">لم يتم تسجيل موافقتكم على بنود هذا العقد بعد. يرجى مراجعة البنود أعلاه والتأشير بالموافقة لحفظ إقراركم الرسمي.</p>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accept_contract" id="accept_contract" value="1">
                                        <label class="form-check-label fw-bold text-dark" for="accept_contract">
                                            {{ __('I confirm that I have read and agree to the contract terms.') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                @else
                    <div class="alert alert-warning mb-0">
                        <i class="fa-solid fa-circle-info me-2"></i>{{ __('The contract terms are not available yet.') }}
                    </div>
                @endif

                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-outline-secondary px-4" data-switch-tab="tab-documents-btn">
                        <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-right' : 'fa-arrow-left' }} me-1"></i>
                        <span>السابق: المرفقات والمستندات</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Persistent Bottom Action Bar --}}
    <div class="d-flex align-items-center justify-content-between p-3 panel shadow-sm flex-wrap gap-2 mb-4" style="border-radius: 16px;">
        <span class="text-secondary small">
            <i class="fa-solid fa-shield-halved me-1 text-rounder-orange"></i>
            يتم حفظ جميع التعديلات في ملف المنشأة وحسابك الرسمي.
        </span>
        <button type="submit" class="btn btn-brand px-5 fw-bold shadow-sm" style="font-size: 0.95rem;">
            <i class="fa-solid fa-floppy-disk me-1.5"></i> {{ __('Save changes') }}
        </button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Switch tabs buttons
    document.querySelectorAll('[data-switch-tab]').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-switch-tab');
            const targetTab = document.getElementById(targetId);
            if (targetTab) {
                const tabTrigger = new bootstrap.Tab(targetTab);
                tabTrigger.show();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });

    // Services selection counter and toggle
    const servicesCounter = document.getElementById('services-counter');
    const serviceCheckboxes = document.querySelectorAll('.service-check-box');

    function updateServicesCount() {
        let count = 0;
        serviceCheckboxes.forEach(cb => {
            const card = cb.closest('.service-checkbox-card');
            if (cb.checked) {
                count++;
                if (card) card.classList.add('is-selected');
            } else {
                if (card && !card.classList.contains('is-locked')) card.classList.remove('is-selected');
            }
        });
        if (servicesCounter) servicesCounter.textContent = count;
    }

    serviceCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateServicesCount);
    });

    const selectAllBtn = document.getElementById('btn-select-all-services');
    const deselectAllBtn = document.getElementById('btn-deselect-all-services');

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', () => {
            serviceCheckboxes.forEach(cb => { cb.checked = true; });
            updateServicesCount();
        });
    }

    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', () => {
            serviceCheckboxes.forEach(cb => {
                if (!cb.disabled) cb.checked = false;
            });
            updateServicesCount();
        });
    }

    updateServicesCount();

    // Document Edit & Trigger Buttons
    document.querySelectorAll('.btn-trigger-doc-input').forEach(btn => {
        btn.addEventListener('click', function () {
            const field = this.dataset.target;
            const input = document.getElementById(field);
            if (input) input.click();
        });
    });

    // Handle File Change
    document.querySelectorAll('.doc-file-hidden-input').forEach(input => {
        input.addEventListener('change', function () {
            const field = this.id;
            const previewBox = document.getElementById('preview-box-' + field);
            const statusIcon = document.querySelector('.status-icon-' + field);
            const statusText = document.querySelector('.status-text-' + field);
            const clearBtn = document.querySelector('.btn-clear-selected-doc[data-target="' + field + '"]');
            const card = document.getElementById('doc-card-' + field);

            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                const sizeKb = (file.size / 1024).toFixed(1);
                const sizeText = sizeKb > 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';

                if (statusIcon) {
                    statusIcon.className = 'fa-solid fa-file-circle-plus text-warning-emphasis';
                }
                if (statusText) {
                    statusText.innerHTML = '<strong class="text-dark">ملف جديد:</strong> ' + file.name + ' (' + sizeText + ') - <span class="badge bg-warning text-dark">جاهز للحفظ</span>';
                }
                if (previewBox) {
                    previewBox.className = 'doc-file-info-preview p-2 rounded-2 mt-2 border bg-warning-subtle border-warning-subtle';
                }
                if (clearBtn) clearBtn.classList.remove('d-none');
                if (card) card.classList.add('has-file');
            }
        });
    });

    // Handle Cancel / Clear selected file
    document.querySelectorAll('.btn-clear-selected-doc').forEach(btn => {
        btn.addEventListener('click', function () {
            const field = this.dataset.target;
            const input = document.getElementById(field);
            const previewBox = document.getElementById('preview-box-' + field);
            const statusIcon = document.querySelector('.status-icon-' + field);
            const statusText = document.querySelector('.status-text-' + field);
            const card = document.getElementById('doc-card-' + field);

            if (input) input.value = '';
            this.classList.add('d-none');

            const hasOriginal = card && card.classList.contains('has-file');
            if (statusIcon) {
                statusIcon.className = hasOriginal ? 'fa-solid fa-check text-success' : 'fa-solid fa-circle-info text-secondary';
            }
            if (statusText) {
                statusText.textContent = hasOriginal ? 'المستند الحالي محفوظ في النظام' : 'لم يتم رفع هذا المستند بعد';
            }
            if (previewBox) {
                previewBox.className = 'doc-file-info-preview p-2 rounded-2 mt-2 border ' + (hasOriginal ? 'bg-white border-success-subtle' : 'bg-light border-dashed');
            }
        });
    });

    // Toggle password visibility
    document.querySelectorAll('.toggle-password-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            if (!input) return;
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash', 'text-rounder-orange');
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('fa-eye-slash', 'text-rounder-orange');
                    icon.classList.add('fa-eye');
                }
            }
        });
    });
});
</script>
@endpush
@endsection

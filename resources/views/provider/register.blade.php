<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#00343e">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="سجل كمزود خدمة لدى راوندر وقدم خدماتك للعملاء.">
    <title>تسجيل مزود خدمة · راوندر</title>
    <link rel="icon" href="{{ asset('assets/favicon.svg') }}" type="image/svg+xml">
    <link rel="preload" href="{{ asset('assets/fonts/tajawal-bold.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
    <style>
        body.provider-register-page { background: #f5f8f7; }
        .provider-register-main { padding-block: 40px 64px; }
        .provider-register-card {
            max-width: 860px;
            margin-inline: auto;
            padding: clamp(22px, 5vw, 42px);
            border: 1px solid #e0eae7;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 12px 36px rgba(0, 52, 62, .07);
        }
        .provider-register-heading { margin-bottom: 24px; }
        .provider-register-brand { color: var(--rounder-petrol, #00343e); font-size: 1.1rem; font-weight: 800; }
        .provider-register-brand span { color: var(--rounder-orange, #fb822f); }
        .provider-register-heading h1 { margin: 8px 0 5px; font-size: clamp(1.4rem, 3vw, 1.85rem); font-weight: 800; color: #02333E; }
        .provider-register-heading p { color: var(--rounder-muted, #5e737c); margin: 0; font-size: .92rem; }
        .form-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 24px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #eef3f2;
            color: #02333E;
            font-size: 1.05rem;
            font-weight: 800;
        }
        .form-section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(251, 130, 47, 0.12);
            color: var(--rounder-orange, #fb822f);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
        }
        .provider-register-form .form-label { margin-bottom: 6px; color: #35565a; font-size: .84rem; font-weight: 700; }
        .provider-register-form .form-control,
        .provider-register-form .form-select { min-height: 46px; background-color: #fff; border-radius: 10px; }
        .provider-service-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 9px; }
        .provider-service-option {
            display: flex;
            align-items: center;
            gap: 9px;
            min-height: 48px;
            margin: 0;
            padding: 10px 12px;
            border: 1px solid #e0eae7;
            border-radius: 10px;
            background: #fff;
            cursor: pointer;
            transition: all .18s ease;
        }
        .provider-service-option:hover { border-color: #a6c4bd; background: #fbfdfc; }
        .provider-service-option:has(input:checked) {
            border-color: var(--rounder-orange, #fb822f);
            background: #fff8f3;
            box-shadow: inset 0 0 0 1px var(--rounder-orange, #fb822f);
        }
        .provider-service-option .form-check-input { float: none; flex: 0 0 auto; margin: 0; }
        .provider-service-option .form-check-label { flex: 1; color: var(--rounder-ink, #1b2e35); font-size: .82rem; font-weight: 600; }
        
        .doc-item-card {
            border: 1px solid #e0eae7;
            border-radius: 12px;
            padding: 14px;
            background: #fbfdfc;
            transition: all 0.2s ease;
        }
        .doc-item-card:hover {
            border-color: #cbd5e1;
            background: #ffffff;
        }
        .doc-item-card.has-file {
            border-color: #16a34a;
            background: #f0fdf4;
        }
        .provider-register-actions { margin-top: 30px; padding-top: 20px; border-top: 1px solid #edf2f0; }
        .provider-register-actions .btn { min-height: 48px; padding-inline: 26px; font-size: .92rem; font-weight: 700; border-radius: 10px; }
        @media (max-width: 575.98px) {
            .provider-register-main { padding-block: 24px 40px; }
            .provider-register-card { border-radius: 16px; padding: 18px; }
            .provider-service-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 7px; }
            .provider-register-actions { flex-direction: column; }
            .provider-register-actions .btn { width: 100%; }
        }
    </style>
    <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
</head>
<body class="provider-register-page">
<a class="visually-hidden-focusable skip-link" href="#main">تخطّ إلى المحتوى</a>
<svg class="sprite" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <symbol id="i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol>
    <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 2 3 6v6c0 6 9 10 9 10s9-4 9-10V6zM8 12l3 3 5-6"/></symbol>
    <symbol id="i-arrow" viewBox="0 0 24 24"><path d="M20 12H4m6-6-6 6 6 6"/></symbol>
    <symbol id="i-menu" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></symbol>
</svg>

<header class="site-header sticky-top">
    <nav class="navbar navbar-expand-lg" aria-label="القائمة الرئيسية" data-bs-theme="dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}" aria-label="راوندر — الرئيسية">
                <img class="wordmark" src="{{ asset('assets/images/rounder-wordmark.png') }}" width="2172" height="724" alt="rounder — راوندر">
            </a>
            <div class="site-actions d-flex align-items-center gap-2 gap-lg-3 order-lg-3">
                @auth
                    <a class="btn btn-sm d-none d-md-inline-flex align-items-center gap-1 admin-portal-btn" href="{{ route('admin.dashboard') }}">
                        <svg class="icon" aria-hidden="true" style="width:14px;height:14px"><use href="#i-grid"></use></svg>
                        <span>لوحة التحكم</span>
                    </a>
                @else
                    <a class="btn btn-sm d-none d-md-inline-flex align-items-center gap-1 admin-portal-btn" href="{{ route('admin.login') }}">
                        <svg class="icon" aria-hidden="true" style="width:14px;height:14px"><use href="#i-shield"></use></svg>
                        <span>دخول الإدارة</span>
                    </a>
                @endauth
                <a class="btn btn-link header-partner d-none d-xl-inline-flex" href="{{ route('provider.register') }}">
                    انضم كمزود خدمة <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg>
                </a>
                <a class="btn btn-primary btn-sm" href="{{ route('provider.login') }}">دخول مزود الخدمة</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav"
                    aria-controls="main-nav" aria-expanded="false" aria-label="فتح القائمة">
                    <svg class="icon" aria-hidden="true"><use href="#i-menu"></use></svg>
                </button>
            </div>
            <div class="collapse navbar-collapse order-lg-2" id="main-nav">
                <ul class="navbar-nav mx-lg-auto gap-lg-3">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#services">خدماتنا</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#business">للأعمال</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#how">كيف نعمل</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#faq">الأسئلة الشائعة</a></li>
                    <li class="nav-item d-md-none"><a class="nav-link text-warning fw-bold" href="{{ route('provider.login') }}">دخول مزود الخدمة</a></li>
                    @guest
                        <li class="nav-item d-md-none"><a class="nav-link text-warning fw-bold" href="{{ route('admin.login') }}">دخول الإدارة</a></li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>
</header>

<main id="main" class="provider-register-main">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="provider-register-card">
                    <div class="provider-register-heading text-center">
                        <div class="provider-register-brand">rounder <span>مزود الخدمة</span></div>
                        <h1>طلب الانضمام كمزود خدمة شريك</h1>
                        <p class="text-secondary mb-0">سجل بيانات منشأتكم وخدماتكم للانضمام إلى شبكة مزودي راوندر المعتمدين.</p>
                    </div>

                    @if(session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="fw-bold mb-1"><i class="fa-solid fa-circle-xmark me-1"></i>يرجى تصحيح الأخطاء التالية:</div>
                            <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form class="provider-register-form" method="post" action="{{ route('provider.register.submit') }}" enctype="multipart/form-data">
                        @csrf

                        {{-- 1. بيانات المنشأة --}}
                        <div class="form-section-title">
                            <span class="form-section-icon"><i class="fa-solid fa-building"></i></span>
                            <span>١. بيانات المنشأة والنشاط</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="company_name">اسم الشركة / المؤسسة <span class="text-danger">*</span></label>
                                <input class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name') }}" required maxlength="160" placeholder="مثال: شركة راوندر للصيانة">
                                @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="trade_name">الاسم التجاري <span class="text-danger">*</span></label>
                                <input class="form-control @error('trade_name') is-invalid @enderror" id="trade_name" name="trade_name" value="{{ old('trade_name') }}" required maxlength="160" placeholder="الاسم التجاري المعتمد">
                                @error('trade_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="provider_type">نوع مزود الخدمة <span class="text-danger">*</span></label>
                                <select class="form-select @error('provider_type') is-invalid @enderror" id="provider_type" name="provider_type" required>
                                    <option value="">اختر نوع المزود</option>
                                    @foreach(['فني مستقل', 'مؤسسة خدمات', 'شركة صيانة'] as $type)
                                        <option value="{{ $type }}" @selected(old('provider_type') === $type)>{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('provider_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="years_of_experience">سنوات الخبرة <span class="text-danger">*</span></label>
                                <input class="form-control @error('years_of_experience') is-invalid @enderror" type="number" id="years_of_experience" name="years_of_experience" min="0" max="100" value="{{ old('years_of_experience') }}" required placeholder="0">
                                @error('years_of_experience')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- 2. بيانات السجل التجاري والممثل القانوني --}}
                        <div class="form-section-title">
                            <span class="form-section-icon"><i class="fa-solid fa-id-card-clip"></i></span>
                            <span>٢. بيانات السجل والممثل القانوني</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="form-label" for="commercial_registration">رقم السجل التجاري</label>
                                <input class="form-control @error('commercial_registration') is-invalid @enderror" id="commercial_registration" name="commercial_registration" value="{{ old('commercial_registration') }}" maxlength="80" placeholder="رقم السجل التجاري">
                                @error('commercial_registration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label" for="legal_representative">اسم الممثل القانوني</label>
                                <input class="form-control @error('legal_representative') is-invalid @enderror" id="legal_representative" name="legal_representative" value="{{ old('legal_representative') }}" maxlength="160" placeholder="اسم الممثل النظامي">
                                @error('legal_representative')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label" for="legal_representative_id">رقم هوية الممثل القانوني</label>
                                <input class="form-control @error('legal_representative_id') is-invalid @enderror" id="legal_representative_id" name="legal_representative_id" value="{{ old('legal_representative_id') }}" maxlength="30" placeholder="الهوية الوطنية / الإقامة">
                                @error('legal_representative_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- 3. بيانات المسؤول وحساب الدخول --}}
                        <div class="form-section-title">
                            <span class="form-section-icon"><i class="fa-solid fa-user-lock"></i></span>
                            <span>٣. بيانات المسؤول وحساب الدخول</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="name">اسم المسؤول عن الحساب <span class="text-danger">*</span></label>
                                <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required maxlength="120" placeholder="الاسم الكامل لمدير الحساب">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="phone">رقم الهاتف (05…) <span class="text-danger">*</span></label>
                                <input class="form-control font-monospace @error('phone') is-invalid @enderror" dir="ltr" type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="05XXXXXXXX" pattern="05[0-9]{8}" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label" for="email">البريد الإلكتروني</label>
                                <input class="form-control font-monospace @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email') }}" maxlength="254" placeholder="info@company.com">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="password">كلمة المرور <span class="text-danger">*</span></label>
                                <input class="form-control @error('password') is-invalid @enderror" type="password" id="password" name="password" minlength="6" autocomplete="new-password" required placeholder="٦ خانات على الأقل">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="password_confirmation">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                                <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" minlength="6" autocomplete="new-password" required placeholder="أعد إدخال كلمة المرور">
                            </div>
                            <div class="col-12 mt-1">
                                <div class="form-text text-muted small d-flex align-items-center gap-1.5">
                                    <i class="fa-solid fa-circle-info text-primary"></i>
                                    <span>يجب أن تتكون كلمة المرور من 6 خانات على الأقل وتطابق حقل التأكيد.</span>
                                </div>
                            </div>
                        </div>

                        {{-- 4. التخصصات والخدمات المقدمة --}}
                        <div class="form-section-title">
                            <span class="form-section-icon"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                            <span>٤. التخصصات والخدمات المقدمة</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-secondary small">حدد الخدمات التي تقدمها شركتكم:</span>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2" id="btn-select-all" style="font-size: 0.75rem;">تحديد الكل</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0.5 px-2" id="btn-deselect-all" style="font-size: 0.75rem;">إلغاء الكل</button>
                                <span class="badge bg-light text-dark border"><span id="reg-services-count">0</span> محدد</span>
                            </div>
                        </div>

                        @php $oldServices = (array) old('service_ids', []); @endphp
                        @if(isset($serviceCategories) && $serviceCategories->isNotEmpty())
                            @foreach($serviceCategories as $category)
                                @if($category->services->isNotEmpty())
                                    <div class="mb-3">
                                        <div class="text-muted small fw-bold mb-1.5 pb-1 border-bottom">{{ $category->translations->firstWhere('locale', 'ar')?->name ?? $category->code }}</div>
                                        <div class="provider-service-grid">
                                            @foreach($category->services as $service)
                                                @php($serviceName = $service->translations->firstWhere('locale', 'ar')?->name ?? $service->code)
                                                <label class="provider-service-option">
                                                    <input class="form-check-input reg-service-check" type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked(in_array($service->id, $oldServices))>
                                                    <span class="form-check-label">{{ $serviceName }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="provider-service-grid mb-3">
                                @foreach($services as $service)
                                    @php($serviceName = $service->translations->firstWhere('locale', 'ar')?->name ?? $service->code)
                                    <label class="provider-service-option">
                                        <input class="form-check-input reg-service-check" type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked(in_array($service->id, $oldServices))>
                                        <span class="form-check-label">{{ $serviceName }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                        @error('service_ids')<div class="text-danger small mt-1 fw-semibold">{{ $message }}</div>@enderror

                        {{-- 5. المرفقات والمستندات الرسمية مع واجهة رفع وتعديل عصرية --}}
                        <div class="form-section-title">
                            <span class="form-section-icon"><i class="fa-solid fa-folder-open"></i></span>
                            <span>٥. المرفقات والمستندات الرسمية</span>
                        </div>
                        <p class="text-secondary small mb-3">يمكنك إرفاق المستندات الآن أو استكمالها لاحقًا قبل اعتماد الحساب. الصيغ المقبولة: PDF أو صور (حد أقصى 10 ميجابايت).</p>

                        <div class="row g-3">
                            @foreach([
                                'commercial_registration_document' => 'السجل التجاري',
                                'legal_representative_id_document' => 'هوية الممثل القانوني',
                                'municipal_license' => 'الرخصة البلدية',
                                'signed_annex_a' => 'الملحق (أ) الموقّع',
                                'company_seal' => 'الختم أو الشعار',
                            ] as $field => $label)
                                <div class="col-md-6">
                                    <div class="doc-item-card h-100" id="reg-doc-card-{{ $field }}">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <label class="form-label mb-0 fw-bold text-dark d-flex align-items-center gap-1.5" for="{{ $field }}">
                                                <i class="fa-solid fa-file-lines text-rounder-orange"></i>
                                                <span>{{ $label }}</span>
                                            </label>
                                            <button type="button" class="btn btn-sm btn-outline-primary py-0.5 px-2.5 btn-reg-trigger-file" data-target="{{ $field }}" style="border-radius: 8px; font-size: 0.78rem; font-weight: 700;">
                                                <i class="fa-solid fa-arrow-up-from-bracket me-1"></i>
                                                <span class="btn-text-{{ $field }}">رفع الملف</span>
                                            </button>
                                        </div>

                                        {{-- Input file مخفي --}}
                                        <input class="d-none reg-hidden-file-input @error($field) is-invalid @enderror" type="file" id="{{ $field }}" name="{{ $field }}" accept=".jpg,.jpeg,.png,.pdf">

                                        {{-- معاينة الملف المختار --}}
                                        <div class="p-2 rounded-2 border bg-white small d-flex align-items-center justify-content-between gap-2" id="reg-preview-{{ $field }}">
                                            <span class="text-muted text-truncate reg-preview-text-{{ $field }}" style="font-size: 0.78rem;">
                                                لم يتم اختيار ملف بعد
                                            </span>
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0 d-none btn-reg-clear-file" data-target="{{ $field }}" title="إلغاء الملف">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                        @error($field)<div class="text-danger small mt-1 fw-semibold">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- أزرار الإرسال --}}
                        <div class="provider-register-actions d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <button class="btn btn-primary px-5 shadow-sm" type="submit">
                                <i class="fa-solid fa-paper-plane me-1"></i> إرسال طلب التسجيل
                            </button>
                            <a class="btn btn-outline-secondary" href="{{ route('provider.login') }}">
                                لديك حساب بالفعل؟ تسجيل الدخول
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="row gy-4 footer-top">
            <div class="col-12 col-lg-3 footer-brand">
                <a href="{{ route('home') }}" aria-label="راوندر — الرئيسية"><img class="wordmark" src="{{ asset('assets/images/rounder-wordmark.png') }}" alt="rounder — راوندر" width="2172" height="724" loading="lazy"></a>
                <p>عناية للمنزل. راحة لك.</p>
            </div>
            <div class="col-6 col-lg-3">
                <h3>اكتشف راوندر</h3>
                <ul class="list-unstyled footer-links">
                    <li><a href="{{ route('home') }}#services">الخدمات المنزلية</a></li>
                    <li><a href="{{ route('home') }}#business">حلول الأعمال</a></li>
                    <li><a href="{{ route('provider.register') }}">التسجيل كمزود خدمة</a></li>
                    <li><a href="{{ route('provider.login') }}">دخول مزود الخدمة</a></li>
                    @auth<li><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>@else<li><a href="{{ route('admin.login') }}">دخول الإدارة</a></li>@endauth
                </ul>
            </div>
            <div class="col-6 col-lg-3">
                <h3>كيف نقدر نساعدك؟</h3>
                <ul class="list-unstyled footer-links">
                    <li><a href="{{ route('home') }}#how">طريقة الحجز</a></li>
                    <li><a href="{{ route('home') }}#faq">الأسئلة الشائعة</a></li>
                    <li><a href="{{ route('terms') }}">الشروط والأحكام</a></li>
                    <li><a href="{{ route('privacy') }}">سياسة الخصوصية</a></li>
                </ul>
            </div>
            <div class="col-12 col-lg-3 footer-note">
                <span class="concept-label">نسخة تصميم تجريبية</span>
                <p>واجهة راوندر للخدمات المنزلية.<br>المحتوى والطلبات لأغراض المعاينة.</p>
            </div>
        </div>
        <div class="footer-bottom d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <span>راوندر © {{ date('Y') }} — جميع الحقوق محفوظة</span>
            <div class="d-flex align-items-center gap-2 small">
                <a href="{{ route('terms') }}" class="text-white-50 text-decoration-none">الشروط والأحكام</a>
                <span class="text-white-50">|</span>
                <a href="{{ route('privacy') }}" class="text-white-50 text-decoration-none">سياسة الخصوصية</a>
            </div>
            <span>صُمّم ليكون أقرب لك <span class="brand-heart" aria-hidden="true">♡</span></span>
        </div>
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Services counter & select/deselect all
    const checks = document.querySelectorAll('.reg-service-check');
    const counter = document.getElementById('reg-services-count');
    const updateCount = () => {
        let count = 0;
        checks.forEach(c => { if (c.checked) count++; });
        if (counter) counter.textContent = count;
    };
    checks.forEach(c => c.addEventListener('change', updateCount));
    updateCount();

    const selectAllBtn = document.getElementById('btn-select-all');
    const deselectAllBtn = document.getElementById('btn-deselect-all');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', () => {
            checks.forEach(c => c.checked = true);
            updateCount();
        });
    }
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', () => {
            checks.forEach(c => c.checked = false);
            updateCount();
        });
    }

    // Document trigger buttons (رفع الملف / تعديل)
    document.querySelectorAll('.btn-reg-trigger-file').forEach(btn => {
        btn.addEventListener('click', function () {
            const field = this.dataset.target;
            const input = document.getElementById(field);
            if (input) input.click();
        });
    });

    // When file selected
    document.querySelectorAll('.reg-hidden-file-input').forEach(input => {
        input.addEventListener('change', function () {
            const field = this.id;
            const textEl = document.querySelector('.reg-preview-text-' + field);
            const clearBtn = document.querySelector('.btn-reg-clear-file[data-target="' + field + '"]');
            const card = document.getElementById('reg-doc-card-' + field);
            const btnText = document.querySelector('.btn-text-' + field);
            const previewBox = document.getElementById('reg-preview-' + field);

            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                const sizeKb = (file.size / 1024).toFixed(1);
                const sizeText = sizeKb > 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';
                if (textEl) {
                    textEl.innerHTML = '<i class="fa-solid fa-circle-check text-success me-1"></i><strong>' + file.name + '</strong> (' + sizeText + ')';
                }
                if (clearBtn) clearBtn.classList.remove('d-none');
                if (card) card.classList.add('has-file');
                if (btnText) btnText.textContent = 'تعديل';
                if (previewBox) {
                    previewBox.classList.remove('bg-white');
                    previewBox.classList.add('bg-success-subtle', 'border-success-subtle');
                }
            }
        });
    });

    // Clear file selection
    document.querySelectorAll('.btn-reg-clear-file').forEach(btn => {
        btn.addEventListener('click', function () {
            const field = this.dataset.target;
            const input = document.getElementById(field);
            const textEl = document.querySelector('.reg-preview-text-' + field);
            const card = document.getElementById('reg-doc-card-' + field);
            const btnText = document.querySelector('.btn-text-' + field);
            const previewBox = document.getElementById('reg-preview-' + field);

            if (input) input.value = '';
            this.classList.add('d-none');
            if (textEl) textEl.textContent = 'لم يتم اختيار ملف بعد';
            if (card) card.classList.remove('has-file');
            if (btnText) btnText.textContent = 'رفع الملف';
            if (previewBox) {
                previewBox.classList.remove('bg-success-subtle', 'border-success-subtle');
                previewBox.classList.add('bg-white');
            }
        });
    });
});
</script>
</body>
</html>

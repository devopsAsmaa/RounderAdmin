@extends('layouts.provider')
@section('title', 'طلب التسجيل قيد المراجعة')

@push('styles')
<style>
    .pending-page-container {
        max-width: 1040px;
        margin: 0 auto;
    }
    .pending-hero-card {
        background: #fff;
        border: 1px solid #e3ece9;
        border-radius: 22px;
        padding: 2.5rem 1.75rem;
        box-shadow: 0 8px 30px rgba(2, 51, 62, 0.04);
        position: relative;
        overflow: hidden;
    }
    .pending-hero-card::before {
        content: '';
        position: absolute;
        top: 0;
        inset-inline: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--petrol), var(--orange), #fec59a);
    }
    .pending-icon-halo {
        width: 88px;
        height: 88px;
        margin: 0 auto 1.35rem;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(251, 130, 47, 0.15) 0%, rgba(251, 130, 47, 0.03) 70%);
        border: 2px solid rgba(251, 130, 47, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .pending-icon-halo::after {
        content: '';
        position: absolute;
        inset: -7px;
        border-radius: 50%;
        border: 1.5px dashed rgba(251, 130, 47, 0.35);
        animation: rotatePendingRing 24s linear infinite;
    }
    @keyframes rotatePendingRing {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .pending-status-badge {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        background: rgba(251, 130, 47, 0.12);
        color: #d66415;
        border: 1px solid rgba(251, 130, 47, 0.25);
        border-radius: 50rem;
        padding: .45rem 1.15rem;
        font-size: .88rem;
        font-weight: 700;
    }

    /* Stepper Timeline */
    .timeline-steps {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        position: relative;
        margin: 2.25rem 0 1.5rem;
    }
    @media(max-width: 991.98px) {
        .timeline-steps {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media(max-width: 575.98px) {
        .timeline-steps {
            grid-template-columns: 1fr;
        }
    }
    .timeline-step-item {
        background: #fbfdfc;
        border: 1px solid #e7eeec;
        border-radius: 14px;
        padding: 1.1rem .95rem;
        text-align: center;
        position: relative;
        transition: all .2s ease;
    }
    .timeline-step-item.is-done {
        background: #f3faf7;
        border-color: #cde7dc;
    }
    .timeline-step-item.is-current {
        background: #fff9f4;
        border-color: #ffd8ba;
        box-shadow: 0 4px 14px rgba(251, 130, 47, 0.08);
    }
    .timeline-step-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        margin-bottom: .6rem;
    }
    .timeline-step-item.is-done .timeline-step-circle {
        background: #198754;
        color: #fff;
    }
    .timeline-step-item.is-current .timeline-step-circle {
        background: var(--orange);
        color: #fff;
        box-shadow: 0 0 0 4px rgba(251, 130, 47, 0.2);
    }
    .timeline-step-item.is-upcoming .timeline-step-circle {
        background: #eef2f1;
        color: #8da49f;
    }
    .timeline-step-title {
        font-size: .86rem;
        font-weight: 700;
        color: var(--petrol);
        margin-bottom: .25rem;
    }
    .timeline-step-status {
        font-size: .74rem;
        font-weight: 600;
    }
    .timeline-step-item.is-done .timeline-step-status { color: #198754; }
    .timeline-step-item.is-current .timeline-step-status { color: #d66415; }
    .timeline-step-item.is-upcoming .timeline-step-status { color: #8da49f; }

    /* Info Panels */
    .pending-info-panel {
        background: #fff;
        border: 1px solid #e3ece9;
        border-radius: 18px;
        box-shadow: 0 4px 16px rgba(2, 51, 62, 0.025);
    }
    .pending-data-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding: .65rem 0;
        border-bottom: 1px dashed #e9f0ee;
        font-size: .88rem;
    }
    .pending-data-row:last-child {
        border-bottom: none;
    }
    .pending-data-label {
        color: #648187;
        font-weight: 500;
        flex-shrink: 0;
    }
    .pending-data-value {
        color: var(--petrol);
        font-weight: 700;
        text-align: end;
        word-break: break-word;
    }
    .doc-check-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .75rem .95rem;
        border-radius: 12px;
        background: #fbfdfc;
        border: 1px solid #eaf0ee;
        margin-bottom: .6rem;
        font-size: .86rem;
        transition: all .15s ease;
    }
    .doc-check-item:last-child {
        margin-bottom: 0;
    }
    .doc-check-item:hover {
        background: #fff;
        border-color: #d1dfdb;
    }

    /* Service Chips Grid */
    .service-chip-card {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .7rem .85rem;
        background: #fbfdfc;
        border: 1px solid #e7eeec;
        border-radius: 12px;
        transition: all .18s ease;
        height: 100%;
        text-align: start;
    }
    .service-chip-card:hover {
        background: #fff;
        border-color: #b5d7cc;
        box-shadow: 0 3px 8px rgba(2, 51, 62, 0.04);
        transform: translateY(-1px);
    }
    .service-chip-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: #eef8f4;
        color: #198754;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .88rem;
        flex-shrink: 0;
    }
    .service-chip-body {
        min-width: 0;
        flex-grow: 1;
        overflow: hidden;
    }
    .service-chip-name {
        display: block;
        font-size: .86rem;
        font-weight: 700;
        color: var(--petrol);
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .service-chip-cat {
        display: block;
        font-size: .72rem;
        color: #7b959b;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
@endpush

@section('body')
<div class="pending-page-container py-3 py-lg-4">
    {{-- Hero Card --}}
    <div class="pending-hero-card text-center mb-4">
        <div class="pending-icon-halo">
            <i class="fa-solid fa-hourglass-half text-rounder-orange" style="font-size: 2.2rem;"></i>
        </div>

        <div class="mb-3">
            <span class="pending-status-badge">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>طلب التسجيل قيد المراجعة والتدقيق</span>
            </span>
        </div>

        <h1 class="h3 fw-bold text-dark mb-2">طلب انضمامكم قيد التدقيق لدى إدارة راوندر</h1>
        <p class="text-secondary mx-auto mb-0" style="max-width: 660px; font-size: 0.96rem; line-height: 1.6;">
            مرحباً بك شريكنا العزيز <strong class="text-dark">{{ $profile->trade_name ?: $profile->company_name }}</strong>. تم استلام بيانات منشأتكم ومرفقاتكم بنجاح، ويقوم فريق العمليات والامتثال بالتحقق من المستندات لإصدار العقد وتفعيل الحساب.
        </p>

        {{-- الخط الزمني لمراحل الاعتماد --}}
        <div class="timeline-steps">
            <div class="timeline-step-item is-done">
                <div class="timeline-step-circle"><i class="fa-solid fa-check"></i></div>
                <div class="timeline-step-title">١. تقديم الطلب</div>
                <div class="timeline-step-status"><i class="fa-solid fa-circle-check me-1"></i>مكتمل بنجاح</div>
            </div>
            <div class="timeline-step-item is-current">
                <div class="timeline-step-circle"><i class="fa-solid fa-arrows-rotate fa-spin"></i></div>
                <div class="timeline-step-title">٢. مراجعة وتدقيق الإدارة</div>
                <div class="timeline-step-status"><i class="fa-solid fa-hourglass-half me-1"></i>قيد التنفيذ حالياً</div>
            </div>
            <div class="timeline-step-item is-upcoming">
                <div class="timeline-step-circle"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                <div class="timeline-step-title">٣. اعتماد الحساب وإصدار العقد</div>
                <div class="timeline-step-status">الخطوة القادمة</div>
            </div>
            <div class="timeline-step-item is-upcoming">
                <div class="timeline-step-circle"><i class="fa-solid fa-signature"></i></div>
                <div class="timeline-step-title">٤. توقيع العقد وبدء العمل</div>
                <div class="timeline-step-status">في انتظار الاعتماد</div>
            </div>
        </div>

        {{-- تنبيه مدة التدقيق --}}
        <div class="p-3 rounded-3 text-start d-flex align-items-center gap-3 mx-auto" style="max-width: 760px; background: rgba(2, 51, 62, 0.04); border: 1px solid rgba(2, 51, 62, 0.1);">
            <div class="flex-shrink-0" style="width: 40px; height: 40px; border-radius: 10px; background: rgba(2, 51, 62, 0.08); display: flex; align-items: center; justify-content: center; color: var(--petrol); font-size: 1.15rem;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="small flex-grow-1" style="line-height: 1.55;">
                <div class="fw-bold text-dark mb-0.5">ماذا يحدث الآن؟</div>
                <div class="text-secondary">
                    تستغرق المراجعة والتدقيق النظامي عادة بين <strong>24 إلى 48 ساعة عمل</strong>. فور الاعتماد، ستصلكم رسالة نصية وبريد إلكتروني، ويمكنكم الدخول لمراجعة العقد المعتمد والشروط وتوقيعه إلكترونياً لبدء استقبال الطلبات فوراً.
                </div>
            </div>
        </div>
    </div>

    {{-- الصف الأول: بيانات المنشأة والمستندات الرسمية بتوازن تام --}}
    <div class="row g-4 mb-4">
        {{-- بيانات المنشأة المسجلة --}}
        <div class="col-lg-6">
            <div class="pending-info-panel p-3.5 p-md-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h2 class="h6 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-building text-rounder-orange"></i>
                        <span>بيانات المنشأة المسجلة</span>
                    </h2>
                    <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">#{{ $profile->id }}</span>
                </div>

                <div class="pending-data-row">
                    <span class="pending-data-label">اسم الشركة / المؤسسة:</span>
                    <span class="pending-data-value">{{ $profile->company_name }}</span>
                </div>
                <div class="pending-data-row">
                    <span class="pending-data-label">الاسم التجاري:</span>
                    <span class="pending-data-value">{{ $profile->trade_name ?: '—' }}</span>
                </div>
                <div class="pending-data-row">
                    <span class="pending-data-label">نوع مزود الخدمة:</span>
                    <span class="pending-data-value"><span class="badge bg-light text-dark border">{{ $profile->provider_type }}</span></span>
                </div>
                <div class="pending-data-row">
                    <span class="pending-data-label">سنوات الخبرة:</span>
                    <span class="pending-data-value">{{ $profile->years_of_experience }} سنوات</span>
                </div>
                <div class="pending-data-row">
                    <span class="pending-data-label">رقم السجل التجاري:</span>
                    <span class="pending-data-value font-monospace">{{ $profile->commercial_registration ?: 'غير مدخل' }}</span>
                </div>
                <div class="pending-data-row">
                    <span class="pending-data-label">الممثل القانوني:</span>
                    <span class="pending-data-value">{{ $profile->legal_representative ?: 'غير مدخل' }}</span>
                </div>
                <div class="pending-data-row">
                    <span class="pending-data-label">هوية الممثل القانوني:</span>
                    <span class="pending-data-value font-monospace">{{ $profile->legal_representative_id ?: 'غير مدخل' }}</span>
                </div>
                <div class="pending-data-row">
                    <span class="pending-data-label">مسؤول الحساب:</span>
                    <span class="pending-data-value">{{ $profile->user?->name ?? '—' }}</span>
                </div>
                <div class="pending-data-row">
                    <span class="pending-data-label">رقم الهاتف:</span>
                    <span class="pending-data-value font-monospace" dir="ltr">{{ $profile->user?->phone ?? '—' }}</span>
                </div>
                @if($profile->user?->email)
                <div class="pending-data-row">
                    <span class="pending-data-label">البريد الإلكتروني:</span>
                    <span class="pending-data-value font-monospace">{{ $profile->user->email }}</span>
                </div>
                @endif
                <div class="pending-data-row">
                    <span class="pending-data-label">تاريخ تقديم الطلب:</span>
                    <span class="pending-data-value small text-secondary">
                        {{ $profile->created_at ? $profile->created_at->locale(app()->getLocale())->translatedFormat('d F Y - h:i A') : '—' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- المستندات الرسمية المرفوعة --}}
        <div class="col-lg-6">
            <div class="pending-info-panel p-3.5 p-md-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h2 class="h6 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-folder-check text-rounder-orange"></i>
                        <span>حالة المستندات والمرفقات الرسمية</span>
                    </h2>
                    <span class="badge bg-light text-secondary border small">5 مستندات</span>
                </div>

                @php
                    $docs = [
                        'commercial_registration_document' => [
                            'title' => 'السجل التجاري',
                            'desc' => 'وثيقة السجل التجاري المعتمدة من وزارة التجارة'
                        ],
                        'legal_representative_id_document' => [
                            'title' => 'هوية الممثل القانوني',
                            'desc' => 'الهوية الوطنية أو الإقامة للشخص المخول نظاماً'
                        ],
                        'municipal_license' => [
                            'title' => 'الرخصة البلدية',
                            'desc' => 'رخصة مزاولة النشاط التجاري سارية المفعول'
                        ],
                        'signed_annex_a' => [
                            'title' => 'الملحق (أ) الموقّع',
                            'desc' => 'إقرار الضوابط الفنية المعتمد والموقّع'
                        ],
                        'company_seal' => [
                            'title' => 'الختم الرسمي / الشعار',
                            'desc' => 'الختم الرسمي للمنشأة أو الشعار المعتمد'
                        ],
                    ];
                @endphp

                <div class="d-flex flex-column gap-2">
                    @foreach($docs as $docKey => $docInfo)
                        @php $isUploaded = !empty($profile->$docKey); @endphp
                        <div class="doc-check-item">
                            <div class="d-flex align-items-center gap-2.5 min-w-0">
                                <span class="d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px; border-radius: 9px; background: rgba(2,51,62,0.06); color: var(--petrol); font-size: 0.92rem; flex-shrink: 0;">
                                    <i class="fa-solid fa-file-shield"></i>
                                </span>
                                <div class="text-truncate">
                                    <span class="fw-bold text-dark d-block text-truncate" style="font-size: 0.88rem;">{{ $docInfo['title'] }}</span>
                                    <span class="text-muted small d-block text-truncate" style="font-size: 0.74rem;">{{ $docInfo['desc'] }}</span>
                                </div>
                            </div>
                            @if($isUploaded)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 flex-shrink-0" style="font-size: 0.78rem; font-weight: 700;">
                                    <i class="fa-solid fa-check me-1"></i> تم الإرفاق
                                </span>
                            @else
                                <span class="badge bg-light text-secondary border px-2.5 py-1.5 flex-shrink-0" style="font-size: 0.78rem;">
                                    <i class="fa-solid fa-clock me-1"></i> لم يُرفق بعد
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- الصف الثاني: بطاقة الخدمات والتخصصات المقدمة بعرض كامل وشبكة منظمة --}}
    <div class="pending-info-panel p-3.5 p-md-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom flex-wrap gap-2">
            <h2 class="h6 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fa-solid fa-screwdriver-wrench text-rounder-orange"></i>
                <span>الخدمات والتخصصات التي تقدمها منشأتكم</span>
            </h2>
            <span class="badge bg-light text-dark border px-3 py-1.5 font-monospace-normal" style="font-size: 0.82rem;">
                <i class="fa-solid fa-circle-check text-success me-1"></i>
                <span>{{ $profile->services->count() }} خدمة معتمدة في الطلب</span>
            </span>
        </div>

        @if($profile->services->isNotEmpty())
            <div class="row g-2.5">
                @foreach($profile->services as $service)
                    @php
                        $serviceName = $service->translations->firstWhere('locale', app()->getLocale())?->name ?? $service->code;
                        $categoryName = $service->category?->translations->firstWhere('locale', app()->getLocale())?->name ?? $service->category?->code;
                    @endphp
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="service-chip-card">
                            <span class="service-chip-icon"><i class="fa-solid fa-check"></i></span>
                            <div class="service-chip-body">
                                <span class="service-chip-name" title="{{ $serviceName }}">{{ $serviceName }}</span>
                                @if($categoryName)
                                    <span class="service-chip-cat" title="{{ $categoryName }}">{{ $categoryName }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-muted small py-3 text-center">لم يتم تحديد خدمات محددة بعد.</div>
        @endif
    </div>

    {{-- أزرار التفاعل وقنوات المساعدة --}}
    <div class="pending-info-panel p-4 text-center">
        <div class="d-flex flex-wrap align-items-center justify-content-center gap-3 mb-3">
            <button type="button" class="btn btn-primary px-4 py-2 shadow-sm" onclick="window.location.reload();">
                <i class="fa-solid fa-arrows-rotate me-1.5"></i>
                <span>تحديث حالة الطلب الآن</span>
            </button>
            <form method="post" action="{{ route('provider.logout') }}" class="d-inline m-0">
                @csrf
                <button type="submit" class="btn btn-outline-secondary px-4 py-2">
                    <i class="fa-solid fa-right-from-bracket me-1.5"></i>
                    <span>تسجيل الخروج</span>
                </button>
            </form>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-center gap-4 text-secondary small pt-3 border-top">
            <span class="fw-semibold text-dark">هل لديك استفسار أو ترغب في تعديل مستنداتك؟</span>
            <a href="tel:920000488" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-1.5 hover-dark">
                <i class="fa-solid fa-phone text-rounder-orange"></i>
                <span dir="ltr">920000488</span>
            </a>
            <a href="mailto:support@rounder.sa" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-1.5 hover-dark">
                <i class="fa-solid fa-envelope text-rounder-orange"></i>
                <span>support@rounder.sa</span>
            </a>
        </div>
    </div>
</div>
@endsection

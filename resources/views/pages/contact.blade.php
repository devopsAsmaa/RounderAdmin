<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#00343e">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تواصل معنا · راوندر لخدمات المنزل والأعمال</title>
    <link rel="icon" href="{{ asset('assets/favicon.svg') }}" type="image/svg+xml">
    <link rel="preload" href="{{ asset('assets/fonts/tajawal-bold.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css?v=' . (file_exists(public_path('assets/css/style.css')) ? filemtime(public_path('assets/css/style.css')) : time())) }}">

    <style>
      :root {
        --brand-petrol: #00343e;
        --brand-petrol-dark: #00252d;
        --brand-petrol-light: #004b5a;
        --brand-orange: #ff7b24;
        --brand-orange-hover: #ff914c;
        --brand-ink: #123d44;
        --brand-muted: #697e82;
        --brand-line: #e2eceb;
        --brand-soft: #f4f8f7;
        --brand-green: #25d366;
      }

      body {
        background-color: #f7faf9;
        color: var(--brand-ink);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
      }

      /* Hero Section */
      .contact-hero {
        background: radial-gradient(circle at 85% 20%, rgba(255, 123, 36, 0.15) 0%, transparent 45%),
                    radial-gradient(circle at 15% 80%, rgba(0, 84, 97, 0.25) 0%, transparent 40%),
                    linear-gradient(135deg, var(--brand-petrol-dark) 0%, var(--brand-petrol) 100%);
        color: #ffffff;
        padding: 56px 0 96px;
        position: relative;
        overflow: hidden;
      }

      .contact-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px);
        background-size: 24px 24px;
        pointer-events: none;
      }

      .hero-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.14);
        backdrop-filter: blur(8px);
        padding: 6px 16px;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #d8ece8;
        margin-bottom: 18px;
      }

      .pulse-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #2ecc71;
        box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.7);
        animation: pulse-green 2s infinite;
      }

      @keyframes pulse-green {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(46, 204, 113, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
      }

      .contact-hero-title {
        font-size: 2.6rem;
        font-weight: 800;
        line-height: 1.25;
        margin-bottom: 14px;
        letter-spacing: -0.5px;
      }

      .contact-hero-desc {
        color: #b7d1ce;
        font-size: 1.05rem;
        line-height: 1.7;
        max-width: 640px;
        margin: 0 auto;
      }

      .sparkle-accent {
        color: var(--brand-orange);
        display: inline-block;
        margin-inline-start: 4px;
        animation: spin-slow 12s linear infinite;
      }

      @keyframes spin-slow {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
      }

      /* Quick Connect Floating Bar */
      .quick-channels-wrap {
        margin-top: -55px;
        position: relative;
        z-index: 10;
        margin-bottom: 35px;
      }

      .quick-channel-card {
        background: #ffffff;
        border: 1px solid var(--brand-line);
        border-radius: 18px;
        padding: 22px 20px;
        box-shadow: 0 10px 30px rgba(0, 52, 62, 0.06);
        transition: all 0.25s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
      }

      .quick-channel-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 40px rgba(0, 52, 62, 0.1);
        border-color: #bcd2cc;
      }

      .channel-icon-bubble {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
        margin-bottom: 14px;
      }

      .bubble-orange { background: rgba(255, 123, 36, 0.12); color: var(--brand-orange); }
      .bubble-green { background: rgba(37, 211, 102, 0.14); color: #1da851; }
      .bubble-teal { background: rgba(0, 84, 97, 0.12); color: #005461; }

      .quick-channel-card h3 {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--brand-petrol);
        margin-bottom: 4px;
      }

      .quick-channel-card p {
        font-size: 0.85rem;
        color: var(--brand-muted);
        margin-bottom: 16px;
        line-height: 1.5;
      }

      .quick-channel-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
      }

      .btn-channel-orange {
        background: #fff4ed;
        color: var(--brand-orange);
        border: 1px solid #ffd5bb;
      }
      .btn-channel-orange:hover {
        background: var(--brand-orange);
        color: #ffffff;
        border-color: var(--brand-orange);
      }

      .btn-channel-green {
        background: #eafbf1;
        color: #179848;
        border: 1px solid #b7f0cb;
      }
      .btn-channel-green:hover {
        background: #25d366;
        color: #ffffff;
        border-color: #25d366;
      }

      .btn-channel-teal {
        background: #edf7f6;
        color: #005461;
        border: 1px solid #c2e3de;
      }
      .btn-channel-teal:hover {
        background: #005461;
        color: #ffffff;
        border-color: #005461;
      }

      /* Main Content Cards */
      .main-form-card {
        background: #ffffff;
        border: 1px solid var(--brand-line);
        border-radius: 22px;
        padding: 34px 30px;
        box-shadow: 0 10px 32px rgba(0, 52, 62, 0.05);
      }

      .side-info-card {
        background: #ffffff;
        border: 1px solid var(--brand-line);
        border-radius: 22px;
        padding: 28px 24px;
        box-shadow: 0 10px 32px rgba(0, 52, 62, 0.05);
      }

      .section-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eef6f5;
        color: #005461;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        margin-bottom: 8px;
      }

      /* Form Elements */
      .form-input-wrap {
        position: relative;
      }

      .input-icon-prepend {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        inset-inline-start: 14px;
        color: #8da4a7;
        font-size: 0.95rem;
        pointer-events: none;
        transition: color 0.2s ease;
      }

      .form-control-iconic {
        padding-inline-start: 42px !important;
        border-radius: 12px;
        border: 1.5px solid #dbe6e4;
        padding: 12px 16px;
        font-size: 0.92rem;
        transition: all 0.2s ease;
      }

      .form-control-iconic:focus {
        border-color: var(--brand-orange);
        box-shadow: 0 0 0 3px rgba(255, 123, 36, 0.15);
      }

      .form-control-iconic:focus + .input-icon-prepend,
      .form-input-wrap:focus-within .input-icon-prepend {
        color: var(--brand-orange);
      }

      .upload-dropzone {
        border: 2px dashed #cfe0dc;
        background: #fafdfd;
        border-radius: 14px;
        padding: 20px;
        text-align: center;
        transition: all 0.2s ease;
        cursor: pointer;
      }

      .upload-dropzone:hover {
        border-color: var(--brand-orange);
        background: #fff9f5;
      }

      .upload-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #edf6f5;
        color: var(--brand-petrol);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 8px;
      }

      /* Information Cards in Sidebar */
      .info-row-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 14px 0;
        border-bottom: 1px solid #edf4f3;
      }

      .info-row-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
      }

      .info-item-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #edf6f5;
        color: #005461;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
      }

      .info-item-content h5 {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--brand-petrol);
        margin-bottom: 2px;
      }

      .info-item-content p {
        font-size: 0.82rem;
        color: var(--brand-muted);
        margin: 0;
        line-height: 1.5;
      }


      /* FAQ Section */
      .support-faq-section {
        padding: 50px 0 70px;
      }

      .faq-card {
        background: #ffffff;
        border: 1px solid var(--brand-line);
        border-radius: 18px;
        overflow: hidden;
      }

      .faq-card .accordion-item {
        border: 0;
        border-bottom: 1px solid var(--brand-line);
      }

      .faq-card .accordion-item:last-child {
        border-bottom: 0;
      }

      .faq-card .accordion-button {
        padding: 18px 22px;
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--brand-petrol);
        background: #ffffff;
      }

      .faq-card .accordion-button:not(.collapsed) {
        color: var(--brand-orange);
        background: #fffcf9;
      }

      .faq-card .accordion-body {
        padding: 0 22px 20px;
        color: var(--brand-muted);
        font-size: 0.88rem;
        line-height: 1.8;
      }

      /* Responsive adjustments */
      @media (max-width: 767.98px) {
        .contact-hero { padding: 40px 0 80px; }
        .contact-hero-title { font-size: 2rem; }
        .main-form-card, .side-info-card { padding: 22px 18px; border-radius: 18px; }
        .quick-channels-wrap { margin-top: -45px; }
      }
    </style>
  </head>
  <body>

    {{-- Official Sticky Navbar --}}
    <header class="site-header sticky-top">
      <nav class="navbar navbar-expand-lg" aria-label="القائمة الرئيسية" data-bs-theme="dark">
        <div class="container">
          <a class="navbar-brand" href="{{ url('/') }}" aria-label="راوندر — الرئيسية">
            <img class="wordmark" src="{{ asset('assets/images/rounder-wordmark.png') }}" width="2172" height="724" alt="rounder — راوندر">
          </a>
          <div class="site-actions d-flex align-items-center gap-2 gap-lg-3 order-lg-3">
            @auth
              <a class="btn btn-sm d-none d-md-inline-flex align-items-center gap-1 admin-portal-btn text-white" href="{{ route('admin.dashboard') }}">
                <i class="fa-solid fa-gauge-high"></i>
                <span>لوحة التحكم</span>
              </a>
            @else
              <a class="btn btn-sm d-none d-md-inline-flex align-items-center gap-1 admin-portal-btn text-white" href="{{ route('admin.login') }}">
                <i class="fa-solid fa-shield-halved"></i>
                <span>دخول الإدارة</span>
              </a>
            @endauth
            <a class="btn btn-link header-partner d-none d-xl-inline-flex text-white-50" href="{{ route('provider.register') }}">
              انضم كمزود خدمة <i class="fa-solid fa-arrow-left ms-1"></i>
            </a>
            <a class="btn btn-primary btn-sm" href="{{ url('/') }}#services">
              اطلب خدمة <i class="fa-solid fa-arrow-left ms-1"></i>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav"
              aria-controls="main-nav" aria-expanded="false" aria-label="فتح القائمة">
              <i class="fa-solid fa-bars text-white"></i>
            </button>
          </div>
          <div class="collapse navbar-collapse order-lg-2" id="main-nav">
            <ul class="navbar-nav mx-lg-auto gap-lg-3">
              <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">الرئيسية</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ url('/#services') }}">خدماتنا</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ url('/#business') }}">للأعمال</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ url('/#how') }}">كيف نعمل</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ url('/#faq') }}">الأسئلة الشائعة</a></li>
              <li class="nav-item"><a class="nav-link active" href="{{ route('contact') }}">تواصل معنا</a></li>
            </ul>
          </div>
        </div>
      </nav>
    </header>

    {{-- Hero Section --}}
    <section class="contact-hero text-center">
      <div class="container position-relative" style="z-index: 2;">
        <div class="hero-status-pill">
          <span class="pulse-indicator"></span>
          <span>مركز خدمة العملاء والدعم الفني · متاحون لمساعدتكم</span>
        </div>

        <h1 class="contact-hero-title">
          تواصل معنا<span style="color: var(--brand-orange)">.</span> نحن هنا لأجلك <span class="sparkle-accent">✳</span>
        </h1>

        <p class="contact-hero-desc">
          نسعد دائماً بالاستماع إليك والرد على كافة استفساراتك واقتراحاتك. تواصل معنا عبر قنوات الدعم المباشرة أو اترك رسالتك وسنرد عليك بأسرع وقت.
        </p>
      </div>
    </section>

    {{-- Quick Connect Floating Channels --}}
    <div class="container quick-channels-wrap">
      <div class="row g-3 g-md-4">
        {{-- كارت الهاتف الموحد --}}
        <div class="col-12 col-md-4">
          <div class="quick-channel-card">
            <div>
              <div class="channel-icon-bubble bubble-orange">
                <i class="fa-solid fa-phone"></i>
              </div>
              <h3>الهاتف الموحد</h3>
              <p>اتصال فوري ومباشر مع مركز الدعم وحجوزات الصيانة.</p>
            </div>
            <a href="tel:{{ $settings->support_phone ?: '920000488' }}" class="quick-channel-btn btn-channel-orange">
              <i class="fa-solid fa-phone-flip"></i>
              <span dir="ltr">{{ $settings->support_phone ?: '920000488' }}</span>
              <span>— اتصل الآن</span>
            </a>
          </div>
        </div>

        {{-- كارت واتساب الدعم الفوري --}}
        <div class="col-12 col-md-4">
          <div class="quick-channel-card">
            <div>
              <div class="channel-icon-bubble bubble-green">
                <i class="fa-brands fa-whatsapp"></i>
              </div>
              <h3>واتساب راوندر</h3>
              <p>محادثة فورية وسريعة للإجابة على استفساراتك وتنسيق المواعيد.</p>
            </div>
            <a href="https://wa.me/966{{ ltrim($settings->support_phone ?: '920000488', '0') }}?text={{ rawurlencode('مرحباً فريق راوندر، أود الاستفسار بخصوص خدماتكم.') }}" target="_blank" rel="noopener noreferrer" class="quick-channel-btn btn-channel-green">
              <i class="fa-brands fa-whatsapp fs-5"></i>
              <span>محادثة واتساب فورية</span>
            </a>
          </div>
        </div>

        {{-- كارت البريد الإلكتروني --}}
        <div class="col-12 col-md-4">
          <div class="quick-channel-card">
            <div>
              <div class="channel-icon-bubble bubble-teal">
                <i class="fa-solid fa-envelope"></i>
              </div>
              <h3>البريد الإلكتروني</h3>
              <p>للشراكات، طلبات عروض الأسعار، والملاحظات الرسمية.</p>
            </div>
            <a href="mailto:{{ $settings->support_email ?: 'support@rounder.sa' }}" class="quick-channel-btn btn-channel-teal">
              <i class="fa-regular fa-envelope"></i>
              <span>{{ $settings->support_email ?: 'support@rounder.sa' }}</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    {{-- Main Container (Form + Info) --}}
    <main class="container my-3 my-lg-4">

      {{-- Alerts --}}
      @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-3 p-3.5 rounded-4 mb-4 shadow-sm border-success-subtle">
          <i class="fa-solid fa-circle-check fs-2 text-success flex-shrink-0"></i>
          <div>
            <div class="fw-bold fs-6">{{ session('status') }}</div>
            @if(session('ticket_reference'))
              <div class="mt-1 small text-dark">
                الرقم المرجعي لتذكرتك: <strong class="badge bg-success font-monospace fs-6 px-2.5 py-1">{{ session('ticket_reference') }}</strong>
                <span class="text-muted ms-1">(احتفظ بهذا الرمز لمتابعة استفسارك).</span>
              </div>
            @endif
          </div>
        </div>
      @endif

      @if($errors->any())
        <div class="alert alert-danger rounded-4 mb-4 shadow-sm border-danger-subtle">
          <div class="fw-bold mb-1"><i class="fa-solid fa-circle-xmark me-1"></i>يرجى مراجعة وتصحيح الحقول التالية:</div>
          <ul class="mb-0 ps-3 small">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="row g-4">
        {{-- Form Column (7 Cols) --}}
        <div class="col-12 col-lg-8">
          <div class="main-form-card">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 pb-3 border-bottom">
              <div>
                <span class="section-badge"><i class="fa-solid fa-paper-plane"></i> نموذج المراسلة المباشر</span>
                <h2 class="h4 fw-bold text-dark mb-1">أرسل رسالتك مباشرة</h2>
                <p class="text-muted small mb-0">املأ النموذج وسيقوم فريق الدعم الفني بمراجعة طلبك والتواصل معك فوراً.</p>
              </div>
              <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.78rem;">
                <i class="fa-solid fa-shield-check text-success me-1"></i> بياناتك محمية ومشفرة
              </span>
            </div>

            <form method="post" action="{{ route('contact.submit') }}" enctype="multipart/form-data">
              @csrf

              <div class="row g-3">
                {{-- الاسم --}}
                <div class="col-sm-6">
                  <label class="form-label" for="contact_name">الاسم بالكامل <span class="text-danger">*</span></label>
                  <div class="form-input-wrap">
                    <input class="form-control form-control-iconic @error('name') is-invalid @enderror" type="text" id="contact_name" name="name" value="{{ old('name', auth()->user()?->name) }}" required maxlength="120" placeholder="مثال: محمد عبدالله">
                    <i class="fa-regular fa-user input-icon-prepend"></i>
                  </div>
                  @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- رقم الجوال --}}
                <div class="col-sm-6">
                  <label class="form-label" for="contact_phone">رقم الجوال <span class="text-danger">*</span></label>
                  <div class="form-input-wrap">
                    <input class="form-control form-control-iconic font-monospace @error('phone') is-invalid @enderror" type="tel" id="contact_phone" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" dir="ltr" required placeholder="05XXXXXXXX" pattern="05[0-9]{8}">
                    <i class="fa-solid fa-mobile-screen-button input-icon-prepend"></i>
                  </div>
                  @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- البريد الإلكتروني --}}
                <div class="col-sm-6">
                  <label class="form-label" for="contact_email">البريد الإلكتروني <span class="text-danger">*</span></label>
                  <div class="form-input-wrap">
                    <input class="form-control form-control-iconic font-monospace @error('email') is-invalid @enderror" type="email" id="contact_email" name="email" value="{{ old('email', auth()->user()?->email) }}" required maxlength="254" placeholder="name@example.com">
                    <i class="fa-regular fa-envelope input-icon-prepend"></i>
                  </div>
                  @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- تصنيف الاستفسار --}}
                <div class="col-sm-6">
                  <label class="form-label" for="contact_category">نوع الموضوع / الاستفسار <span class="text-danger">*</span></label>
                  <div class="form-input-wrap">
                    <select class="form-select form-control-iconic @error('category') is-invalid @enderror" id="contact_category" name="category" required>
                      <option value="">اختر التصنيف المناسب</option>
                      @foreach($categories as $category)
                        <option value="{{ $category->value }}" @selected(old('category') === $category->value)>
                          {{ $category->label() }}
                        </option>
                      @endforeach
                    </select>
                    <i class="fa-solid fa-list-check input-icon-prepend"></i>
                  </div>
                  @error('category')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- عنوان الرسالة --}}
                <div class="col-12">
                  <label class="form-label" for="contact_subject">عنوان الموضوع <span class="text-danger">*</span></label>
                  <div class="form-input-wrap">
                    <input class="form-control form-control-iconic @error('subject') is-invalid @enderror" type="text" id="contact_subject" name="subject" value="{{ old('subject') }}" required maxlength="190" placeholder="موجز يصف موضوع استفسارك أو طلبك">
                    <i class="fa-regular fa-bookmark input-icon-prepend"></i>
                  </div>
                  @error('subject')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- نص الرسالة --}}
                <div class="col-12">
                  <label class="form-label" for="contact_message">تفاصيل الرسالة أو الاستفسار <span class="text-danger">*</span></label>
                  <textarea class="form-control @error('message') is-invalid @enderror" id="contact_message" name="message" rows="4" required minlength="10" maxlength="3000" placeholder="اكتب استفسارك أو ملاحظاتك هنا بالتفصيل…" style="border-radius: 12px; border: 1.5px solid #dbe6e4; padding: 12px 16px;">{{ old('message') }}</textarea>
                  @error('message')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- مرفق اختياري --}}
                <div class="col-12">
                  <label class="form-label d-flex align-items-center justify-content-between" for="contact_attachment">
                    <span>إرفاق صورة أو ملف توضيحي <span class="text-muted fw-normal small">(اختياري)</span></span>
                    <span class="text-muted small">PDF, JPG, PNG (أقصى حد: 10 ميجا)</span>
                  </label>
                  <label for="contact_attachment" class="upload-dropzone w-100 d-block m-0">
                    <div class="upload-icon">
                      <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div class="fw-bold text-dark small mb-1">انقر لاختيار ملف من جهازك أو اسحبه هنا</div>
                    <div class="text-muted" style="font-size: 0.76rem;">يدعم صور الأعطال، الفواتير، أو ملفات PDF التوضيحية</div>
                    <input type="file" id="contact_attachment" name="attachment" accept=".jpg,.jpeg,.png,.pdf" class="d-none" onchange="document.getElementById('file-chosen-name').textContent = this.files[0]?.name || ''">
                    <div id="file-chosen-name" class="mt-2 text-success fw-bold font-monospace small"></div>
                  </label>
                  @error('attachment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- زر الإرسال --}}
                <div class="col-12 mt-4">
                  <button type="submit" class="btn btn-primary w-100 shadow py-3 fs-6 rounded-3">
                    <i class="fa-solid fa-paper-plane me-2"></i>
                    <span>إرسال الرسالة إلى فريق الدعم</span>
                    <i class="fa-solid fa-arrow-left ms-2"></i>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        {{-- Sidebar Column (4 Cols) --}}
        <div class="col-12 col-lg-4">
          <div class="side-info-card mb-4">
            <span class="section-badge"><i class="fa-regular fa-clock"></i> التواجد والخدمة</span>
            <h3 class="h5 fw-bold text-dark mb-3">أوقات العمل والدعم</h3>

            <div class="info-row-item">
              <span class="info-item-icon">
                <i class="fa-regular fa-clock"></i>
              </span>
              <div class="info-item-content">
                <h5>ساعات الدعم المباشر</h5>
                <p>{{ $settings->support_working_hours ?: 'الأحد إلى الخميس، 10 ص - 8 م' }}</p>
                <span class="badge bg-success-subtle text-success border border-success-subtle mt-1 font-monospace" style="font-size: 0.72rem;">
                  <i class="fa-solid fa-bolt me-1"></i> استجابة خلال يوم عمل واحد
                </span>
              </div>
            </div>

            <div class="info-row-item">
              <span class="info-item-icon">
                <i class="fa-solid fa-headset"></i>
              </span>
              <div class="info-item-content">
                <h5>حالات الطوارئ والبلاغات العاجلة</h5>
                <p>مباشرة الحالات الطارئة تتم عبر تطبيق الجوال أو الهاتف الموحد فورياً.</p>
              </div>
            </div>
          </div>

          {{-- كارت روابط سريعة للسياسات --}}
          <div class="side-info-card">
            <h4 class="h6 fw-bold text-dark mb-3"><i class="fa-solid fa-scale-balanced me-1 text-primary"></i> وثائق وأنظمة راوندر</h4>
            <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
              <li>
                <a href="{{ route('terms') }}" class="text-decoration-none d-flex align-items-center justify-content-between p-2 rounded-2 bg-light text-dark">
                  <span><i class="fa-solid fa-file-contract me-1.5 text-secondary"></i> الشروط والأحكام العامة</span>
                  <i class="fa-solid fa-chevron-left text-muted"></i>
                </a>
              </li>
              <li>
                <a href="{{ route('privacy') }}" class="text-decoration-none d-flex align-items-center justify-content-between p-2 rounded-2 bg-light text-dark">
                  <span><i class="fa-solid fa-shield-halved me-1.5 text-secondary"></i> سياسة الخصوصية وحماية البيانات</span>
                  <i class="fa-solid fa-chevron-left text-muted"></i>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>

    </main>

    {{-- Support FAQ Section --}}
    <section class="support-faq-section">
      <div class="container">
        <div class="text-center mb-4">
          <span class="section-badge"><i class="fa-regular fa-circle-question"></i> إجابات سريعة</span>
          <h2 class="h3 fw-bold text-dark">الأسئلة الأكثر شيوعاً عن الدعم</h2>
          <p class="text-muted small">قد تجد إجابة استفسارك هنا دون الحاجة لانتظار الرد.</p>
        </div>

        <div class="row justify-content-center">
          <div class="col-12 col-lg-9">
            <div class="accordion faq-card shadow-sm" id="contact-faq">
              <div class="accordion-item">
                <h3 class="accordion-header" id="faq-h1">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-c1" aria-expanded="false" aria-controls="faq-c1">
                    كم يستغرق الوقت للرد على تذكرة الدعم أو رسالتي؟
                  </button>
                </h3>
                <div id="faq-c1" class="accordion-collapse collapse" aria-labelledby="faq-h1" data-bs-parent="#contact-faq">
                  <div class="accordion-body">
                    يقوم فريق الدعم بمراجعة كافة الرسائل فور وصولها، وعادةً ما يتم الرد خلال ساعات العمل الرسمية في غضون 2 إلى 4 ساعات كحد أقصى، أو خلال يوم عمل واحد في حال كانت الرسالة خارج أوقات العمل.
                  </div>
                </div>
              </div>

              <div class="accordion-item">
                <h3 class="accordion-header" id="faq-h2">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-c2" aria-expanded="false" aria-controls="faq-c2">
                    كيف يمكنني متابعة حالة طلبي بعد إرسال الرسالة؟
                  </button>
                </h3>
                <div id="faq-c2" class="accordion-collapse collapse" aria-labelledby="faq-h2" data-bs-parent="#contact-faq">
                  <div class="accordion-body">
                    بمجرد إرسال النموذج، يظهر لك رقم مرجعي للتذكرة (مثل: TKT-XXXXXXXX). يمكنك استخدام هذا الرقم عند التواصل عبر الهاتف أو الواتساب لمتابعة المستجدات، كما ستصلك إشعارات عند تحديث حالة الطلب.
                  </div>
                </div>
              </div>

              <div class="accordion-item">
                <h3 class="accordion-header" id="faq-h3">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-c3" aria-expanded="false" aria-controls="faq-c3">
                    كيف يمكنني التقديم للانضمام كمزود خدمة أو فني معتمد؟
                  </button>
                </h3>
                <div id="faq-c3" class="accordion-collapse collapse" aria-labelledby="faq-h3" data-bs-parent="#contact-faq">
                  <div class="accordion-body">
                    يمكنك التسجيل مباشرة عبر بوابة مزودي الخدمة بالضغط على <a href="{{ route('provider.register') }}" class="text-primary fw-bold">الانضمام كمزود خدمة</a> وإرفاق مستندات منشأتك واعتمادها من الإدارة بخطوات ميسرة وسريعة.
                  </div>
                </div>
              </div>

              <div class="accordion-item">
                <h3 class="accordion-header" id="faq-h4">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-c4" aria-expanded="false" aria-controls="faq-c4">
                    هل يغطي راوندر جميع مناطق وأحياء الرياض؟
                  </button>
                </h3>
                <div id="faq-c4" class="accordion-collapse collapse" aria-labelledby="faq-h4" data-bs-parent="#contact-faq">
                  <div class="accordion-body">
                    نعم، تغطي شبكة مزودي وفنيي راوندر كافة أحياء مدينة الرياض شمالاً وجنوباً وشرقاً وغرباً، مع التوسع المستمر لتغطية باقي مدن ومناطق المملكة قريباً بإذن الله.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- Official Footer --}}
    <footer class="site-footer">
      <div class="container">
        <div class="row gy-4 footer-top">
          <div class="col-12 col-lg-3 footer-brand">
            <a href="{{ url('/') }}" aria-label="راوندر — الرئيسية">
              <img class="wordmark" src="{{ asset('assets/images/rounder-wordmark.png') }}" alt="rounder — راوندر" width="2172" height="724" loading="lazy">
            </a>
            <p>عناية للمنزل. راحة لك.</p>
          </div>
          <div class="col-6 col-lg-3">
            <h3>اكتشف راوندر</h3>
            <ul class="list-unstyled footer-links">
              <li><a href="{{ url('/#services') }}">الخدمات المنزلية</a></li>
              <li><a href="{{ url('/#business') }}">حلول الأعمال</a></li>
              <li><a href="{{ route('provider.register') }}">التسجيل كمزود خدمة</a></li>
              <li><a href="{{ route('provider.login') }}">دخول مزود الخدمة</a></li>
              @auth
                <li><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
              @else
                <li><a href="{{ route('admin.login') }}">دخول الإدارة</a></li>
              @endauth
            </ul>
          </div>
          <div class="col-6 col-lg-3">
            <h3>كيف نقدر نساعدك؟</h3>
            <ul class="list-unstyled footer-links">
              <li><a href="{{ url('/#how') }}">طريقة الحجز</a></li>
              <li><a href="{{ route('faq') }}">الأسئلة الشائعة</a></li>
              <li><a href="{{ route('terms') }}">الشروط والأحكام</a></li>
              <li><a href="{{ route('privacy') }}">سياسة الخصوصية</a></li>
              <li><a href="{{ route('contact') }}">تواصل معنا</a></li>
            </ul>
          </div>
          <div class="col-12 col-lg-3 footer-note">
            <span class="concept-label">راوندر لخدمات التشغيل والصيانة</span>
            <p>منصة الصيانة والخدمات المنزلية المعتمدة.<br>س.ت: {{ $settings->cr_number ?: '10102497799' }}</p>
            @if($settings->app_download_enabled ?? true)
            <div class="d-flex flex-column gap-2 mt-3">
              <span class="text-white-50 small fw-bold">حمّل تطبيق راوندر:</span>
              <div class="d-flex align-items-center gap-2">
                @if(!empty($settings->app_store_url))
                <a href="{{ $settings->app_store_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-light btn-sm px-2 py-1 d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;" aria-label="تحميل من App Store">
                  <i class="fa-brands fa-apple"></i> App Store
                </a>
                @endif
                @if(!empty($settings->play_store_url))
                <a href="{{ $settings->play_store_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-light btn-sm px-2 py-1 d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;" aria-label="تحميل من Google Play">
                  <i class="fa-brands fa-google-play"></i> Google Play
                </a>
                @endif
              </div>
            </div>
            @endif
          </div>
        </div>
        <div class="footer-bottom d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
          <span>راوندر © {{ date('Y') }} — جميع الحقوق محفوظة</span>
          <div class="d-flex align-items-center gap-2 small">
            <a href="{{ route('terms') }}" class="text-white-50 text-decoration-none">الشروط والأحكام</a>
            <span class="text-white-50">|</span>
            <a href="{{ route('privacy') }}" class="text-white-50 text-decoration-none">سياسة الخصوصية</a>
            <span class="text-white-50">|</span>
            <a href="{{ route('faq') }}" class="text-white-50 text-decoration-none">الأسئلة الشائعة</a>
            <span class="text-white-50">|</span>
            <a href="{{ route('contact') }}" class="text-white-50 text-decoration-none">تواصل معنا</a>
          </div>
          <span>صُمّم ليكون أقرب لك <span class="brand-heart" aria-hidden="true">♡</span></span>
        </div>
      </div>
    </footer>

    <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
  </body>
</html>

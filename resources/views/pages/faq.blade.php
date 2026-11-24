<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#00343e">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>الأسئلة الشائعة · راوندر لخدمات المنزل والأعمال</title>
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
      }

      body {
        background-color: #f7faf9;
        color: var(--brand-ink);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
      }

      /* Hero Banner */
      .faq-hero {
        background: radial-gradient(circle at 85% 20%, rgba(255, 123, 36, 0.15) 0%, transparent 45%),
                    radial-gradient(circle at 15% 80%, rgba(0, 84, 97, 0.25) 0%, transparent 40%),
                    linear-gradient(135deg, var(--brand-petrol-dark) 0%, var(--brand-petrol) 100%);
        color: #ffffff;
        padding: 56px 0 86px;
        position: relative;
        overflow: hidden;
      }

      .faq-hero::after {
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

      .faq-hero-title {
        font-size: 2.6rem;
        font-weight: 800;
        line-height: 1.25;
        margin-bottom: 14px;
        letter-spacing: -0.5px;
      }

      .faq-hero-desc {
        color: #b7d1ce;
        font-size: 1.05rem;
        line-height: 1.7;
        max-width: 620px;
        margin: 0 auto 28px;
      }

      .sparkle-accent {
        color: var(--brand-orange);
        display: inline-block;
        margin-inline-start: 4px;
      }

      /* Search Box in Hero */
      .faq-search-wrap {
        max-width: 580px;
        margin: 0 auto;
        position: relative;
      }

      .faq-search-input {
        width: 100%;
        padding: 16px 20px 16px 54px;
        border-radius: 16px;
        border: 2px solid rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.95);
        color: var(--brand-ink);
        font-size: 1rem;
        box-shadow: 0 12px 30px rgba(0, 37, 45, 0.25);
        transition: all 0.25s ease;
      }

      .faq-search-input:focus {
        background: #ffffff;
        border-color: var(--brand-orange);
        outline: none;
        box-shadow: 0 14px 35px rgba(255, 123, 36, 0.25);
      }

      .faq-search-icon {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        inset-inline-start: 20px;
        color: var(--brand-orange);
        font-size: 1.2rem;
        pointer-events: none;
      }

      /* Floating Category Pills */
      .faq-nav-wrap {
        margin-top: -34px;
        position: relative;
        z-index: 10;
        margin-bottom: 40px;
      }

      .category-pills-bar {
        background: #ffffff;
        border: 1px solid var(--brand-line);
        border-radius: 18px;
        padding: 8px;
        box-shadow: 0 8px 25px rgba(0, 52, 62, 0.06);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        flex-wrap: wrap;
      }

      .category-pill-btn {
        background: transparent;
        border: 1px solid transparent;
        border-radius: 12px;
        padding: 10px 18px;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--brand-muted);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
      }

      .category-pill-btn:hover {
        color: var(--brand-petrol);
        background: #f0f7f6;
      }

      .category-pill-btn.active {
        background: var(--brand-petrol);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 52, 62, 0.2);
      }

      /* Category Accordion Cards */
      .faq-category-section {
        margin-bottom: 35px;
      }

      .faq-category-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
      }

      .cat-header-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(255, 123, 36, 0.12);
        color: var(--brand-orange);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
      }

      .cat-header-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--brand-petrol);
        margin: 0;
      }

      .faq-main-accordion {
        background: #ffffff;
        border: 1px solid var(--brand-line);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 6px 20px rgba(0, 52, 62, 0.04);
      }

      .faq-main-accordion .accordion-item {
        border: 0;
        border-bottom: 1px solid var(--brand-line);
      }

      .faq-main-accordion .accordion-item:last-child {
        border-bottom: 0;
      }

      .faq-main-accordion .accordion-button {
        padding: 20px 24px;
        font-weight: 700;
        font-size: 0.98rem;
        color: var(--brand-petrol);
        background: #ffffff;
        box-shadow: none;
      }

      .faq-main-accordion .accordion-button:not(.collapsed) {
        color: var(--brand-orange);
        background: #fffcf9;
      }

      .faq-main-accordion .accordion-body {
        padding: 4px 24px 24px;
        color: #496366;
        font-size: 0.92rem;
        line-height: 1.85;
      }

      /* Still Need Help Box */
      .help-cta-card {
        background: linear-gradient(135deg, #002b33 0%, #004452 100%);
        border-radius: 22px;
        padding: 40px;
        color: #ffffff;
        position: relative;
        overflow: hidden;
        margin: 50px 0 30px;
      }

      .help-cta-card::after {
        content: "؟";
        position: absolute;
        inset-inline-end: 25px;
        bottom: -20px;
        font-size: 140px;
        font-weight: 900;
        color: rgba(255, 255, 255, 0.05);
        pointer-events: none;
        line-height: 1;
      }

      @media (max-width: 767.98px) {
        .faq-hero { padding: 40px 0 70px; }
        .faq-hero-title { font-size: 2rem; }
        .category-pills-bar { justify-content: flex-start; overflow-x: auto; flex-wrap: nowrap; padding: 6px; }
        .category-pill-btn { white-space: nowrap; font-size: 0.82rem; padding: 8px 14px; }
        .help-cta-card { padding: 26px 20px; }
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
              <li class="nav-item"><a class="nav-link active" href="{{ route('faq') }}">الأسئلة الشائعة</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">تواصل معنا</a></li>
            </ul>
          </div>
        </div>
      </nav>
    </header>

    {{-- Hero Banner --}}
    <section class="faq-hero text-center">
      <div class="container position-relative" style="z-index: 2;">
        <div class="hero-status-pill">
          <i class="fa-regular fa-circle-question text-warning"></i>
          <span>مركز المعرفة والإجابات السريعة</span>
        </div>

        <h1 class="faq-hero-title">
          الأسئلة الشائعة<span style="color: var(--brand-orange)">.</span> في بالك سؤال؟ <span class="sparkle-accent">✳</span>
        </h1>

        <p class="faq-hero-desc">
          كل ما تحتاج معرفته عن حجز الخدمات المنزلية، عقود الصيانة للشركات، وبوابة مزودي الخدمة المعتمدين في راوندر.
        </p>

        {{-- Live Search Input --}}
        <div class="faq-search-wrap">
          <input type="text" id="faq-search-input" class="faq-search-input" placeholder="ابحث عن سؤالك أو كلمة مفتاحية هنا (مثل: الدفع، الضمان، إلغاء)...">
          <i class="fa-solid fa-magnifying-glass faq-search-icon"></i>
        </div>
      </div>
    </section>

    {{-- Category Pills Filter Bar --}}
    <div class="container faq-nav-wrap">
      <div class="category-pills-bar">
        <button class="category-pill-btn active" data-target-cat="all">
          <i class="fa-solid fa-layer-group"></i>
          <span>جميع الأسئلة</span>
        </button>
        @foreach($categories as $category)
          <button class="category-pill-btn" data-target-cat="{{ $category['id'] }}">
            <i class="{{ $category['icon'] }}"></i>
            <span>{{ $category['name'] }}</span>
          </button>
        @endforeach
      </div>
    </div>

    {{-- Main FAQs Content --}}
    <main class="container my-4">
      <div class="row justify-content-center">
        <div class="col-12 col-lg-10">

          <div id="no-faq-results" class="text-center py-5 d-none">
            <div class="display-6 text-muted mb-2"><i class="fa-regular fa-face-meh"></i></div>
            <h4 class="fw-bold text-dark">لم نجد نتائج مطابقة لبحثك</h4>
            <p class="text-muted">جرب البحث بكلمات أخرى أو تواصل مباشرة مع فريق خدمة العملاء.</p>
            <a href="{{ route('contact') }}" class="btn btn-outline-primary btn-sm mt-2">
              <i class="fa-solid fa-headset me-1"></i> تواصل مع الدعم الفني
            </a>
          </div>

          @foreach($categories as $catIndex => $category)
            <div class="faq-category-section" id="cat-section-{{ $category['id'] }}" data-category-group="{{ $category['id'] }}">
              <div class="faq-category-header">
                <span class="cat-header-icon">
                  <i class="{{ $category['icon'] }}"></i>
                </span>
                <div>
                  <h2 class="cat-header-title">{{ $category['name'] }}</h2>
                  <div class="text-muted small">{{ $category['description'] }}</div>
                </div>
              </div>

              <div class="accordion faq-main-accordion" id="accordion-cat-{{ $category['id'] }}">
                @foreach($category['questions'] as $qIndex => $item)
                  @php
                    $collapseId = "faq-{$category['id']}-{$qIndex}";
                  @endphp
                  <div class="accordion-item faq-item" data-question-text="{{ mb_strtolower($item['q'] . ' ' . $item['a']) }}">
                    <h3 class="accordion-header" id="heading-{{ $collapseId }}">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $collapseId }}" aria-expanded="false" aria-controls="collapse-{{ $collapseId }}">
                        {{ $item['q'] }}
                      </button>
                    </h3>
                    <div id="collapse-{{ $collapseId }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $collapseId }}" data-bs-parent="#accordion-cat-{{ $category['id'] }}">
                      <div class="accordion-body">
                        {{ $item['a'] }}
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endforeach

          {{-- Help CTA Box --}}
          <div class="help-cta-card">
            <div class="row align-items-center gy-3 position-relative" style="z-index: 2;">
              <div class="col-12 col-md-8">
                <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill mb-2">خدمة العملاء متواجدة لأجلك</span>
                <h3 class="h4 fw-bold mb-1">لم تجد إجابة وافية لاستفسارك؟</h3>
                <p class="text-white-50 mb-0 small">فريق راوندر متواجد لمساعدتك والرد على استفسارك ومتابعة طلباتك فورياً.</p>
              </div>
              <div class="col-12 col-md-4 text-md-end d-flex flex-wrap gap-2 justify-content-md-end">
                <a href="{{ route('contact') }}" class="btn btn-primary btn-sm px-3.5 py-2 fw-bold">
                  <i class="fa-solid fa-paper-plane me-1"></i> تواصل معنا الآن
                </a>
                <a href="tel:{{ $settings->support_phone ?: '920000488' }}" class="btn btn-outline-light btn-sm px-3 py-2 fw-bold" dir="ltr">
                  <i class="fa-solid fa-phone me-1"></i> {{ $settings->support_phone ?: '920000488' }}
                </a>
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>

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
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('faq-search-input');
        const pillButtons = document.querySelectorAll('.category-pill-btn');
        const categorySections = document.querySelectorAll('.faq-category-section');
        const faqItems = document.querySelectorAll('.faq-item');
        const noResults = document.getElementById('no-faq-results');

        let currentCategory = 'all';

        // تصفية حسب التبويب
        pillButtons.forEach(btn => {
          btn.addEventListener('click', function () {
            pillButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentCategory = this.getAttribute('data-target-cat');
            applyFilter();
          });
        });

        // بحث مباشر
        searchInput.addEventListener('input', function () {
          applyFilter();
        });

        function applyFilter() {
          const query = (searchInput.value || '').trim().toLowerCase();
          let visibleCount = 0;

          categorySections.forEach(section => {
            const catGroup = section.getAttribute('data-category-group');
            const matchesCategory = (currentCategory === 'all' || currentCategory === catGroup);

            let sectionHasMatch = false;
            const items = section.querySelectorAll('.faq-item');

            items.forEach(item => {
              const text = item.getAttribute('data-question-text') || '';
              const matchesSearch = !query || text.includes(query);

              if (matchesCategory && matchesSearch) {
                item.classList.remove('d-none');
                sectionHasMatch = true;
                visibleCount++;
                // افتح الأكورديون في حال كان المستخدم يبحث بكلمة محددة
                if (query.length >= 2) {
                  const collapseEl = item.querySelector('.accordion-collapse');
                  if (collapseEl && window.bootstrap) {
                    bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false }).show();
                  }
                }
              } else {
                item.classList.add('d-none');
              }
            });

            if (matchesCategory && sectionHasMatch) {
              section.classList.remove('d-none');
            } else {
              section.classList.add('d-none');
            }
          });

          if (visibleCount === 0) {
            noResults.classList.remove('d-none');
          } else {
            noResults.classList.add('d-none');
          }
        }
      });
    </script>
  </body>
</html>


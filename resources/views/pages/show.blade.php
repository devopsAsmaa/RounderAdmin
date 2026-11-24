<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#00343e">
    <title>{{ $title }} · راوندر</title>
    <link rel="icon" href="{{ asset('assets/favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
      @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap');

      :root {
        --brand-primary: #00343e;
        --brand-orange: #fb822f;
        --brand-orange-hover: #e26a1b;
        --brand-bg: #f7f9fa;
        --text-dark: #1b2e35;
        --text-muted: #5e737c;
        --border-color: #e4ebed;
      }

      body {
        font-family: 'Tajawal', system-ui, -apple-system, sans-serif;
        background-color: var(--brand-bg);
        color: var(--text-dark);
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
      }

      .site-header {
        background: #ffffff;
        border-bottom: 1px solid var(--border-color);
        padding: 0.9rem 0;
        position: sticky;
        top: 0;
        z-index: 100;
      }

      .brand-logo {
        height: 38px;
        width: auto;
        object-fit: contain;
      }

      .nav-switch {
        display: inline-flex;
        background: #edf3f5;
        padding: 4px;
        border-radius: 999px;
        gap: 4px;
      }

      .nav-switch a {
        text-decoration: none;
        padding: 6px 16px;
        border-radius: 999px;
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--text-muted);
        transition: all 0.2s ease;
      }

      .nav-switch a.active {
        background: #ffffff;
        color: var(--brand-primary);
        box-shadow: 0 2px 6px rgba(0, 52, 62, 0.08);
      }

      .page-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 2.25rem;
        box-shadow: 0 4px 20px rgba(0, 52, 62, 0.04);
        margin-top: 1.5rem;
        margin-bottom: 2.5rem;
      }

      .page-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(251, 130, 47, 0.1);
        color: var(--brand-orange);
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 700;
        margin-bottom: 1rem;
      }

      .page-title {
        color: var(--brand-primary);
        font-size: 1.85rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
      }

      .page-meta {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 1.75rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--border-color);
      }

      .page-content {
        font-size: 1.02rem;
        line-height: 1.95;
        color: #2c3e44;
        white-space: pre-line;
        word-break: break-word;
      }

      .site-footer {
        margin-top: auto;
        background: #ffffff;
        border-top: 1px solid var(--border-color);
        padding: 1.25rem 0;
        font-size: 0.85rem;
        color: var(--text-muted);
      }

      .site-footer a {
        color: var(--text-muted);
        text-decoration: none;
        transition: color 0.15s;
      }

      .site-footer a:hover {
        color: var(--brand-orange);
      }

      /* تنسيق خاص لشاشات الموبايل والـ WebView */
      @media (max-width: 576px) {
        .page-card {
          padding: 1.35rem 1.15rem;
          border-radius: 16px;
          margin-top: 1rem;
          margin-bottom: 1.5rem;
        }
        .page-title {
          font-size: 1.45rem;
        }
        .page-content {
          font-size: 0.96rem;
          line-height: 1.85;
        }
      }
    </style>
  </head>
  <body>

    {{-- Header --}}
    <header class="site-header">
      <div class="container d-flex align-items-center justify-content-between">
        <a href="{{ url('/') }}" aria-label="راوندر — الرئيسية">
          <img class="brand-logo" src="{{ asset('assets/images/rounder-wordmark.png') }}" alt="راوندر — Rounder">
        </a>

        {{-- Switcher بين سياسة الخصوصية والشروط والأحكام وتواصل معنا --}}
        <div class="nav-switch">
          <a href="{{ route('terms') }}" class="{{ $page->slug === 'terms' ? 'active' : '' }}">
            <i class="fa-solid fa-file-contract me-1"></i> الشروط والأحكام
          </a>
          <a href="{{ route('privacy') }}" class="{{ $page->slug === 'privacy' ? 'active' : '' }}">
            <i class="fa-solid fa-shield-halved me-1"></i> سياسة الخصوصية
          </a>
          <a href="{{ route('contact') }}">
            <i class="fa-solid fa-headset me-1"></i> تواصل معنا
          </a>
        </div>
      </div>
    </header>

    {{-- Main Container --}}
    <main class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-9">
          <article class="page-card">
            <div class="page-badge">
              @if($page->slug === 'privacy')
                <i class="fa-solid fa-shield-halved"></i> سياسة الخصوصية وحماية البيانات
              @else
                <i class="fa-solid fa-file-contract"></i> الاتفاقية والشروط العامة
              @endif
            </div>

            <h1 class="page-title">{{ $title }}</h1>

            <div class="page-meta d-flex flex-wrap align-items-center gap-3">
              <span>
                <i class="fa-regular fa-clock me-1"></i>
                آخر تحديث: <strong dir="ltr">{{ $updatedAt ? $updatedAt->format('Y-m-d') : '2026-11-04' }}</strong>
              </span>
              <span>•</span>
              <span>منصة راوندر (Rounder)</span>
            </div>

            <div class="page-content">{!! nl2br(e($content)) !!}</div>
          </article>
        </div>
      </div>
    </main>

    {{-- Footer --}}
    <footer class="site-footer">
      <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 text-center text-sm-start">
        <span>راوندر © {{ date('Y') }} — جميع الحقوق محفوظة</span>
        <div class="d-flex align-items-center gap-3">
          <a href="{{ route('terms') }}" class="{{ $page->slug === 'terms' ? 'fw-bold text-dark' : '' }}">الشروط والأحكام</a>
          <span>|</span>
          <a href="{{ route('privacy') }}" class="{{ $page->slug === 'privacy' ? 'fw-bold text-dark' : '' }}">سياسة الخصوصية</a>
          <span>|</span>
          <a href="{{ route('faq') }}">الأسئلة الشائعة</a>
          <span>|</span>
          <a href="{{ route('contact') }}">تواصل معنا</a>
          <span>|</span>
          <a href="{{ url('/') }}">الرئيسية</a>
        </div>
      </div>
    </footer>

  </body>
</html>


@extends('layouts.admin')
@section('title', __('Sign in').' · Rounder')

@section('body')
<style>
.login-split-page {
    min-height: 100vh;
    background: #fff;
    overflow-x: hidden;
}
.login-brand-side {
    background: radial-gradient(circle at 50% 40%, rgba(2, 89, 104, 0.5) 0%, transparent 68%), linear-gradient(145deg, #02333E 0%, #024554 50%, #01242C 100%);
    color: #fff;
    position: relative;
    overflow: hidden;
}
.login-brand-side::before {
    content: '';
    position: absolute;
    top: -60px;
    inset-inline-end: -60px;
    width: 320px;
    height: 320px;
    background: radial-gradient(circle, rgba(251, 130, 47, 0.16) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}
.login-brand-side::after {
    content: '';
    position: absolute;
    bottom: -80px;
    inset-inline-start: -80px;
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, rgba(221, 243, 238, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}
.login-feature-card {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(221, 243, 238, 0.14);
    backdrop-filter: blur(10px);
    border-radius: 14px;
    padding: 0.95rem 1.15rem;
    transition: transform .2s ease, background-color .2s ease;
}
.login-feature-card:hover {
    background: rgba(255, 255, 255, 0.09);
    transform: translateY(-2px);
}
.login-form-side {
    background: #F8FAFB;
}
.login-input-wrap {
    position: relative;
}
.login-input-icon {
    position: absolute;
    inset-inline-start: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #7b969c;
    font-size: 0.95rem;
    pointer-events: none;
    z-index: 2;
}
.login-input {
    padding-inline-start: 2.85rem !important;
    padding-inline-end: 2.85rem !important;
    height: 48px;
    border-radius: 12px !important;
    border: 1px solid #dce6e3;
    background: #fff;
    font-size: 0.92rem;
    transition: all .18s ease;
}
.login-input:focus {
    border-color: var(--petrol-light) !important;
    box-shadow: 0 0 0 3.5px rgba(2, 51, 62, 0.12) !important;
    background: #fff;
}
.login-password-toggle {
    position: absolute;
    inset-inline-end: 0.6rem;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    color: #7b969c;
    padding: 0.35rem 0.5rem;
    cursor: pointer;
    z-index: 2;
    transition: color .15s ease;
}
.login-password-toggle:hover {
    color: var(--petrol);
}
.login-btn {
    height: 50px;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    box-shadow: 0 4px 16px rgba(2, 51, 62, 0.22);
}
</style>

<div class="login-split-page">
    <div class="row g-0 min-vh-100">
        {{-- Brand Showcase Column (Desktop Only) --}}
        <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-between p-5 p-xl-5 login-brand-side">
            <div class="d-flex align-items-center justify-content-between">
                <span class="badge px-3 py-2 fw-bold" style="background: rgba(251, 130, 47, 0.2); color: #FB822F; border: 1px solid rgba(251, 130, 47, 0.35); font-size: 0.78rem; letter-spacing: 0.08em;">
                    ROUND. LIVE. BETTER.
                </span>
                <span class="badge rounded-pill" style="background: rgba(221, 243, 238, 0.15); color: var(--mint); font-size: 0.75rem;">
                    v2.0
                </span>
            </div>

            <div class="my-auto py-5 position-relative" style="z-index: 1;">
                <div class="d-inline-flex align-items-center mb-3">
                    <span class="brand-text text-rounder-orange" style="font-size: 3.6rem; letter-spacing: -0.03em;">rounder</span>
                </div>
                <h2 class="h3 fw-bold text-white mb-3" style="line-height: 1.45;">
                    {{ __('Smart property and community management system') }}
                </h2>
                <p class="text-light opacity-75 fs-6 mb-4 col-xl-11" style="line-height: 1.7;">
                    {{ __('A unified platform connecting residents, properties, and service providers') }}
                </p>

                <div class="d-flex flex-column gap-2 mt-4 col-xl-10">
                    <div class="login-feature-card d-flex align-items-center gap-3">
                        <div class="rounded-circle d-grid place-items-center flex-shrink-0" style="width: 36px; height: 36px; background: rgba(251, 130, 47, 0.22); color: #FB822F;">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-white fs-6 mb-0">{{ __('Services and pricing') }}</div>
                            <div class="small text-light opacity-75">{{ __('Manage service categories, services, sub services and prices in one place.') }}</div>
                        </div>
                    </div>

                    <div class="login-feature-card d-flex align-items-center gap-3">
                        <div class="rounded-circle d-grid place-items-center flex-shrink-0" style="width: 36px; height: 36px; background: rgba(221, 243, 238, 0.22); color: var(--mint);">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-white fs-6 mb-0">{{ __('Orders and emergencies') }}</div>
                            <div class="small text-light opacity-75">{{ __('Real-time dispatch and provider tracking for residents.') }}</div>
                        </div>
                    </div>

                    <div class="login-feature-card d-flex align-items-center gap-3">
                        <div class="rounded-circle d-grid place-items-center flex-shrink-0" style="width: 36px; height: 36px; background: rgba(255, 255, 255, 0.15); color: #fff;">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-white fs-6 mb-0">{{ __('Providers and contracts') }}</div>
                            <div class="small text-light opacity-75">{{ __('Verified service providers and automatic commission records.') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center small text-light opacity-75 pt-3 border-top border-white border-opacity-10 position-relative" style="z-index: 1;">
                <span>© {{ date('Y') }} {{ __('Rounder admin') }}</span>
                <span>{{ __('Property services, made simple') }}</span>
            </div>
        </div>

        {{-- Form Column --}}
        <div class="col-lg-6 d-flex flex-column justify-content-between p-4 p-md-5 login-form-side">
            {{-- Top Bar in Form Side: Language Selector --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-lg-none">
                    <span class="brand-text text-rounder-orange fs-2">rounder</span>
                </div>
                <div class="ms-auto dropdown">
                    <button class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-1 bg-white shadow-sm" style="border-radius: 50rem; font-size: 0.82rem;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-globe text-secondary"></i>
                        <span>{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                        <i class="fa-solid fa-chevron-down small opacity-50"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="min-width: 130px; border-radius: 12px;">
                        <li>
                            <form method="post" action="{{ route('locale.update') }}">
                                @csrf
                                <input type="hidden" name="locale" value="ar">
                                <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ app()->getLocale() === 'ar' ? 'fw-bold text-success active' : '' }}">
                                    <span>العربية</span>
                                    @if(app()->getLocale() === 'ar')<i class="fa-solid fa-check small"></i>@endif
                                </button>
                            </form>
                        </li>
                        <li>
                            <form method="post" action="{{ route('locale.update') }}">
                                @csrf
                                <input type="hidden" name="locale" value="en">
                                <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ app()->getLocale() === 'en' ? 'fw-bold text-success active' : '' }}">
                                    <span>English</span>
                                    @if(app()->getLocale() === 'en')<i class="fa-solid fa-check small"></i>@endif
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Form Container --}}
            <div class="w-100 my-auto py-4" style="max-width: 420px; margin-inline: auto;">
                <div class="mb-4">
                    <span class="badge rounded-pill mb-2 px-3 py-2 fw-semibold" style="background: rgba(2, 51, 62, 0.08); color: var(--petrol); font-size: 0.78rem;">
                        <i class="fa-solid fa-lock-open me-1"></i> {{ __('Central administration portal') }}
                    </span>
                    <h1 class="h2 fw-bold text-dark mb-2">{{ __('Welcome back') }}</h1>
                    <p class="text-secondary small mb-0" style="line-height: 1.6;">{{ __('Enter your credentials to access the admin portal') }}</p>
                </div>

                @include('admin.partials.feedback')

                <form method="post" action="{{ route('admin.login.submit') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="email">{{ __('Email address or phone') }}</label>
                        <div class="login-input-wrap">
                            <i class="fa-solid fa-envelope login-input-icon"></i>
                            <input id="email" name="email" type="text" placeholder="example@domain.com / 05XXXXXXXX" class="form-control login-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="username">
                        </div>
                        @error('email')<div class="invalid-feedback d-block mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">{{ __('Password') }}</label>
                        <div class="login-input-wrap">
                            <i class="fa-solid fa-lock login-input-icon"></i>
                            <input id="password" name="password" type="password" class="form-control login-input" placeholder="••••••••" required autocomplete="current-password">
                            <button type="button" class="login-password-toggle" id="togglePasswordBtn" aria-label="Toggle password visibility">
                                <i class="fa-solid fa-eye-slash" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password')<div class="invalid-feedback d-block mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 pt-1">
                        <div class="form-check m-0">
                            <input class="form-check-input" id="remember" name="remember" value="1" type="checkbox">
                            <label class="form-check-label small text-secondary" for="remember">{{ __('Remember me') }}</label>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 login-btn">
                        <span>{{ __('Sign in') }}</span>
                        <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-left' : 'fa-arrow-right' }} ms-1"></i>
                    </button>
                </form>

                <div class="text-center mt-4 pt-2">
                    <span class="small text-muted d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-success"></i>
                        <span>{{ __('Secure connection') }}</span>
                    </span>
                </div>
            </div>

            {{-- Footer for Mobile --}}
            <div class="d-lg-none text-center text-muted small mt-4">
                © {{ date('Y') }} {{ __('Rounder admin') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const pwdInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const toggleIcon = document.getElementById('togglePasswordIcon');
    if (toggleBtn && pwdInput && toggleIcon) {
        toggleBtn.addEventListener('click', () => {
            const isPassword = pwdInput.type === 'password';
            pwdInput.type = isPassword ? 'text' : 'password';
            toggleIcon.classList.toggle('fa-eye', isPassword);
            toggleIcon.classList.toggle('fa-eye-slash', !isPassword);
        });
    }
})();
</script>
@endpush

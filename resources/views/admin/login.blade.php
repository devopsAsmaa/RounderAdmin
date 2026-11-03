@extends('layouts.admin')
@section('title',__('Sign in').' · Rounder')
@section('body')
<div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="panel p-4 p-md-5 w-100" style="max-width:440px; box-shadow: 0 10px 40px rgba(2, 51, 62, 0.08); border-radius: 22px;">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center gap-2 mb-2">
                <span class="brand-text text-rounder-orange fs-1">rounder</span>
            </div>
            <div>
                <span class="badge rounded-pill brand-badge-mint" style="background: rgba(2, 51, 62, 0.06); color: var(--petrol); border-color: rgba(2, 51, 62, 0.1);">{{ __('Rounder admin') }}</span>
            </div>
            <h1 class="h4 fw-bold mt-3 mb-1 text-dark">{{ __('Welcome back') }}</h1>
            <p class="text-secondary small mb-0">{{ __('Authentication is required to open the admin dashboard.') }}</p>
        </div>

        <form method="post" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="email">{{ __('Email address or phone') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-secondary" style="border-radius: 10px 0 0 10px;"><i class="fa-solid fa-envelope"></i></span>
                    <input id="email" name="email" type="text" placeholder="example@domain.com / 05XXXXXXXX" class="form-control border-start-0 @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="username" style="border-radius: 0 10px 10px 0;">
                </div>
                @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">{{ __('Password') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-secondary" style="border-radius: 10px 0 0 10px;"><i class="fa-solid fa-lock"></i></span>
                    <input id="password" name="password" type="password" class="form-control border-start-0" required autocomplete="current-password" style="border-radius: 0 10px 10px 0;">
                </div>
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" id="remember" name="remember" value="1" type="checkbox">
                <label class="form-check-label small" for="remember">{{ __('Remember me') }}</label>
            </div>
            <button class="btn btn-primary w-100 py-2 fs-6 fw-bold">
                <i class="fa-solid fa-arrow-right-to-bracket me-1"></i>
                {{ __('Sign in') }}
            </button>
        </form>
    </div>
</div>
@endsection

<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Provider portal')) · Rounder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--petrol:#02333e;--orange:#fb822f;--sand:#f8fafb}
        body{background:var(--sand);color:#17343a;font-family:"Tajawal",system-ui,sans-serif}
        .portal-nav{background:linear-gradient(110deg,#02333e,#025968)}
        .brand{color:#fff;text-decoration:none;font-size:1.35rem;font-weight:800}
        .brand span{color:var(--orange)}
        .portal-link{color:#e4f2ef;text-decoration:none;border-radius:.6rem;padding:.45rem .7rem}
        .portal-link:hover,.portal-link.active{background:rgba(255,255,255,.16);color:#fff}
        .panel,.metric{background:#fff;border:1px solid #e2ece9;border-radius:16px;box-shadow:0 3px 16px rgba(2,51,62,.04)}
        .metric{padding:1.1rem;height:100%}.metric .value{font-size:1.8rem;font-weight:800;color:var(--petrol)}
        .btn-primary{background:var(--petrol);border-color:var(--petrol)}.btn-primary:hover{background:#025968;border-color:#025968}
        .btn-orange{background:var(--orange);border-color:var(--orange);color:white}.btn-orange:hover{background:#e67120;border-color:#e67120;color:white}
        .form-control,.form-select{border-radius:9px}
        .table>:not(caption)>*>*{padding:.75rem}
    </style>
    @stack('styles')
</head>
<body>
@auth('provider')
    @php
        $portalUser = auth('provider')->user();
        $portal = app(\App\Modules\Providers\Services\ProviderPortalService::class);
        $providerProfile = $portal->profileFor($portalUser);
        $portalHome = $portal->allows($portalUser, $providerProfile, 'dashboard.view')
            ? 'provider.dashboard'
            : ($portal->allows($portalUser, $providerProfile, 'orders.view')
                ? 'provider.orders.index'
                : ($portal->allows($portalUser, $providerProfile, 'prices.view')
                    ? 'provider.prices.index'
                    : ($portal->allows($portalUser, $providerProfile, 'emergencies.view')
                        ? 'provider.emergencies.index'
                        : ($portal->allows($portalUser, $providerProfile, 'users.manage') ? 'provider.team.index' : null))));
    @endphp
    <nav class="portal-nav py-3">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
            <a class="brand" href="{{ $portalHome ? route($portalHome) : '#' }}">rounder <span>{{ __('Provider portal') }}</span></a>
            <div class="d-flex flex-wrap align-items-center gap-2">
                @if($portal->allows($portalUser, $providerProfile, 'dashboard.view'))
                    <a class="portal-link {{ request()->routeIs('provider.dashboard') ? 'active' : '' }}" href="{{ route('provider.dashboard') }}">{{ __('Dashboard') }}</a>
                @endif
                @if($portal->allows($portalUser, $providerProfile, 'orders.view'))
                    <a class="portal-link {{ request()->routeIs('provider.orders.*') ? 'active' : '' }}" href="{{ route('provider.orders.index') }}">{{ __('Service orders') }}</a>
                @endif
                @if($portal->allows($portalUser, $providerProfile, 'emergencies.view'))
                    <a class="portal-link {{ request()->routeIs('provider.emergencies.*') ? 'active' : '' }}" href="{{ route('provider.emergencies.index') }}">{{ __('Emergency requests') }}</a>
                @endif
                @if($portal->allows($portalUser, $providerProfile, 'prices.view'))
                    <a class="portal-link {{ request()->routeIs('provider.prices.*') ? 'active' : '' }}" href="{{ route('provider.prices.index') }}">{{ __('Prices') }}</a>
                @endif
                @if($portal->allows($portalUser, $providerProfile, 'users.manage'))
                    <a class="portal-link {{ request()->routeIs('provider.team.*') ? 'active' : '' }}" href="{{ route('provider.team.index') }}">{{ __('Team and permissions') }}</a>
                @endif
                <form method="post" action="{{ route('provider.logout') }}" class="m-0 ms-2">
                    @csrf
                    <button class="btn btn-sm btn-outline-light" type="submit">{{ __('Sign out') }}</button>
                </form>
            </div>
        </div>
    </nav>
@endauth
<main class="container py-4">
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('body')
</main>
@stack('scripts')
</body>
</html>

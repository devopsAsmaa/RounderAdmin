<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Provider portal')) · Rounder</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=3">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=3">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=3">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=3">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=3">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --petrol: #02333E;
            --petrol-dark: #01242C;
            --petrol-light: #025968;
            --orange: #FB822F;
            --orange-hover: #e67120;
            --mint: #DDF3EE;
            --sand: #F8FAFB;
        }
        html, body {
            overflow-x: hidden;
            max-width: 100vw;
            -webkit-text-size-adjust: 100%;
        }
        body {
            margin: 0;
            background: #F8FAFB;
            color: #17343a;
            font-family: "Tajawal", system-ui, sans-serif;
        }
        .brand { font-weight: 800; letter-spacing: -.02em; }
        .text-rounder-orange { color: var(--orange) !important; }
        .brand-text { font-size: 1.65rem; font-weight: 800; letter-spacing: -.02em; color: var(--orange) !important; }
        .brand-badge-mint { background: rgba(221, 243, 238, 0.15); color: var(--mint); font-size: .72rem; font-weight: 600; padding: .25rem .6rem; border: 1px solid rgba(221, 243, 238, 0.2); }

        /* Provider Sidebar - Identical to Admin Sidebar */
        .provider-sidebar {
            --bs-offcanvas-width: 260px;
            background: linear-gradient(180deg, #02333E 0%, #023945 50%, #012831 100%) !important;
            color: white;
            border-radius: 0 !important;
            border: none !important;
        }
        .provider-sidebar::-webkit-scrollbar { width: 5px; }
        .provider-sidebar::-webkit-scrollbar-track { background: transparent; }
        .provider-sidebar::-webkit-scrollbar-thumb { background: rgba(221, 243, 238, .2); border-radius: 10px; }
        .provider-sidebar::-webkit-scrollbar-thumb:hover { background: rgba(221, 243, 238, .4); }

        .sidebar-brand-header {
            height: 68px;
            min-height: 68px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            flex-shrink: 0;
        }

        .provider-sidebar-link {
            display: flex;
            align-items: center;
            padding: .68rem .92rem;
            margin: .2rem 0;
            border-radius: 10px;
            color: #c0ded8;
            text-decoration: none;
            font-size: .92rem;
            font-weight: 600;
            transition: all .18s ease;
        }
        .provider-sidebar-link i {
            width: 1.35rem;
            text-align: center;
            margin-inline-end: .55rem;
            font-size: .95rem;
            color: #a6c9c2;
            transition: all .18s ease;
        }
        .provider-sidebar-link:hover {
            background: rgba(255, 255, 255, .08);
            color: #fff;
        }
        .provider-sidebar-link:hover i {
            color: #fff;
        }
        .provider-sidebar-link.active {
            background: var(--orange) !important;
            color: #fff !important;
            font-weight: 700;
            box-shadow: 0 3px 12px rgba(251, 130, 47, .35);
        }
        .provider-sidebar-link.active i {
            color: #fff !important;
        }

        /* Topbar styling identical to Admin */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            height: 64px;
            min-height: 64px;
            background: #fff;
            border-bottom: 1px solid #eef2f1;
            box-shadow: 0 1px 3px rgba(2, 51, 62, 0.03);
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            justify-content: space-between !important;
        }
        .topbar-actions-group {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }
        .topbar-date-pill {
            height: 40px;
            padding: 0 .9rem;
            border-radius: 11px;
            background: #f6f9f8;
            border: 1px solid #dce7e4;
            color: var(--petrol);
            font-size: .84rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            white-space: nowrap;
            transition: all .18s ease;
        }
        .topbar-date-pill:hover { background: #edf6f3; border-color: #b4dad0; }
        .topbar-circle-btn {
            width: 40px;
            height: 40px;
            padding: 0;
            display: inline-grid;
            place-items: center;
            border-radius: 11px;
            background: #f6f9f8;
            border: 1px solid #dce7e4;
            color: var(--petrol);
            font-size: .9rem;
            transition: all .18s ease;
            cursor: pointer;
            text-decoration: none;
        }
        .topbar-circle-btn:hover, .topbar-circle-btn:focus {
            background: #edf6f3;
            border-color: #b4dad0;
            color: var(--petrol);
            transform: translateY(-1px);
        }
        .topbar-pill-btn {
            height: 40px;
            padding: 0 .85rem;
            border-radius: 11px;
            background: #f6f9f8;
            border: 1px solid #dce7e4;
            color: var(--petrol);
            font-size: .84rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            transition: all .18s ease;
            cursor: pointer;
        }
        .topbar-pill-btn:hover, .topbar-pill-btn:focus {
            background: #edf6f3;
            border-color: #b4dad0;
            color: var(--petrol);
            transform: translateY(-1px);
        }
        .topbar-user-pill {
            height: 42px;
            padding: 0;
            padding-inline-start: .3rem;
            padding-inline-end: .75rem;
            border-radius: 12px;
            background: #f6f9f8;
            border: 1px solid #dce7e4;
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            transition: all .18s ease;
            cursor: pointer;
            text-decoration: none;
        }
        .topbar-user-pill:hover, .topbar-user-pill:focus {
            background: #edf6f3;
            border-color: #b4dad0;
            box-shadow: 0 2px 8px rgba(2, 51, 62, 0.05);
            transform: translateY(-1px);
        }
        .topbar-user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: linear-gradient(135deg, var(--petrol), #035163);
            color: #fff;
            font-weight: 800;
            font-size: .88rem;
            display: grid;
            place-items: center;
            text-transform: uppercase;
            box-shadow: 0 2px 5px rgba(2, 51, 62, 0.18);
        }
        .topbar-user-name { font-size: .82rem; font-weight: 700; color: var(--petrol); line-height: 1.2; white-space: nowrap; }
        .topbar-user-role { font-size: .66rem; font-weight: 500; color: #78949a; line-height: 1.1; white-space: nowrap; }
        .topbar-user-chevron { font-size: .66rem; color: #8ea3a8; transition: transform .18s ease; }
        .topbar-divider { width: 1px; height: 22px; background: #e2ece9; margin: 0 .2rem; }
        .topbar-user-dropdown { min-width: 230px; border-radius: 14px; box-shadow: 0 14px 34px rgba(2, 51, 62, 0.12) !important; }
        .topbar-user-dropdown .dropdown-item { font-size: .88rem; border-radius: 8px; margin: 2px 6px; width: calc(100% - 12px); transition: all .15s ease; }
        .topbar-user-dropdown .dropdown-item:hover { background: #f0f7f5; color: var(--petrol); }
        .topbar-user-dropdown .dropdown-item.text-danger:hover { background: #fff0f0; color: #dc3545 !important; }
        .lang-dropdown-menu { min-width: 165px; border-radius: 14px; padding: 6px; box-shadow: 0 12px 32px rgba(2, 51, 62, 0.12) !important; border: 1px solid #e3ece9 !important; }
        .lang-dropdown-item { border-radius: 9px; padding: .55rem .85rem; font-size: .86rem; color: var(--petrol); transition: all .15s ease; display: flex; align-items: center; justify-content: space-between; width: 100%; border: none; background: transparent; text-decoration: none; font-weight: 500; cursor: pointer; }
        .lang-dropdown-item:hover { background: #f0f6f4; color: var(--petrol); }
        .lang-dropdown-item.is-active { background: #e8f4f0; color: var(--petrol); font-weight: 700; }
        .lang-dropdown-item.is-active .lang-check { color: var(--orange); font-size: .8rem; }

        /* Panels, Cards & UI Elements */
        .panel, .metric {
            background: #fff;
            border: 1px solid #e2ece9;
            border-radius: 18px;
            box-shadow: 0 3px 16px rgba(2, 51, 62, 0.035);
        }
        .metric { padding: 1.15rem; height: 100%; }
        .metric .value { font-size: 1.8rem; font-weight: 800; color: var(--petrol); }
        .metric .mark { color: var(--orange); }

        /* Modern Luxury Buttons */
        .btn {
            border-radius: 10px;
            font-weight: 600;
            font-size: .88rem;
            padding: .52rem 1.05rem;
            transition: all .18s cubic-bezier(0.16, 1, 0.3, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
        }
        .btn-sm { border-radius: 8px; font-size: .82rem; padding: .36rem .75rem; gap: .3rem; }
        .btn-primary {
            background: linear-gradient(135deg, #02333E 0%, #034553 100%) !important;
            border-color: #02333E !important;
            color: #fff !important;
            box-shadow: 0 2px 6px rgba(2, 51, 62, .18);
        }
        .btn-primary:hover, .btn-primary:focus {
            background: #025968 !important;
            border-color: #025968 !important;
            box-shadow: 0 4px 14px rgba(2, 51, 62, .28);
            transform: translateY(-1px);
            color: #fff !important;
        }
        .btn-orange, .btn-brand {
            background: var(--orange) !important;
            border-color: var(--orange) !important;
            color: #fff !important;
            box-shadow: 0 2px 8px rgba(251, 130, 47, .25);
        }
        .btn-orange:hover, .btn-orange:focus, .btn-brand:hover, .btn-brand:focus {
            background: var(--orange-hover) !important;
            border-color: var(--orange-hover) !important;
            box-shadow: 0 4px 14px rgba(251, 130, 47, .38);
            transform: translateY(-1px);
            color: #fff !important;
        }
        .btn-outline-primary {
            color: var(--petrol) !important;
            border: 1.5px solid var(--petrol) !important;
            background: transparent !important;
        }
        .btn-outline-primary:hover {
            background: var(--petrol) !important;
            color: #fff !important;
            box-shadow: 0 2px 8px rgba(2, 51, 62, .2);
        }
        .btn-outline-secondary {
            color: #456066 !important;
            border: 1px solid #d4e0dd !important;
            background: #fff !important;
        }
        .btn-outline-secondary:hover {
            background: #f1f7f5 !important;
            border-color: #b5d2cb !important;
            color: var(--petrol) !important;
        }
        .btn-outline-danger {
            color: #c53030 !important;
            border: 1px solid #f2c7c7 !important;
            background: #fff !important;
        }
        .btn-outline-danger:hover {
            background: #fdf2f2 !important;
            border-color: #c53030 !important;
            color: #c53030 !important;
        }

        /* Modern Form Controls */
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #dce6e3;
            padding: .58rem .88rem;
            font-size: .88rem;
            color: #17343a;
            background-color: #fff;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--petrol-light) !important;
            box-shadow: 0 0 0 3.5px rgba(2, 51, 62, .12) !important;
            outline: none !important;
        }
        .form-control-sm, .form-select-sm { border-radius: 8px; padding: .38rem .68rem; font-size: .82rem; }
        .form-label { font-weight: 600; font-size: .86rem; color: #233d43; margin-bottom: .35rem; }
        .form-check-input { border-radius: 6px; border-color: #cedbd7; cursor: pointer; }
        .form-check-input:checked { background-color: var(--orange); border-color: var(--orange); }
        .form-check-input:focus { box-shadow: 0 0 0 3px rgba(251, 130, 47, .2); border-color: var(--orange); }

        /* Modern Tables */
        .table-responsive { border-radius: 14px; background: #fff; -webkit-overflow-scrolling: touch; overflow-x: auto; }
        .table { margin-bottom: 0; --bs-table-bg: transparent; }
        .table > thead { background: #f4f8f7; border-bottom: 1.5px solid #dce7e4; }
        .table > thead th { font-size: .82rem; font-weight: 700; color: var(--petrol); padding: .95rem 1rem; border-bottom: 1.5px solid #dce7e4; white-space: nowrap; letter-spacing: .01em; }
        .table > tbody td { padding: .92rem 1rem; vertical-align: middle; border-bottom: 1px solid #edf4f1; font-size: .88rem; color: #243f45; white-space: nowrap; }
        .table > tbody tr { transition: background-color .15s ease; }
        .table > tbody tr:hover { background-color: #f8fcfa; }
        .table > tbody tr:last-child td { border-bottom: none; }

        /* Badges & Alerts */
        .badge { border-radius: 8px; font-weight: 600; letter-spacing: .01em; }
        .alert { border-radius: 14px; font-size: .9rem; padding: .85rem 1.15rem; border: 1px solid transparent; }
        .alert-success { background: #eef8f4; border-color: #bde4d5; color: #0e6c51; }
        .alert-danger { background: #fdf2f2; border-color: #f4c7c7; color: #b32a2a; }
        .alert-warning { background: #fff9ee; border-color: #f7e0b2; color: #a3610a; }
        .alert-info { background: #edf6f8; border-color: #c0dfe6; color: #025968; }

        /* Desktop Layout (Desktop with fixed sidebar) */
        @media(min-width: 992px) {
            .provider-sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                inset-inline-start: 0;
                width: 260px;
                height: 100vh;
                z-index: 1045;
                overflow-y: auto;
            }
            .provider-sidebar .offcanvas-body {
                display: flex !important;
                flex-direction: column !important;
                padding: 1rem !important;
            }
            .topbar.topbar-with-sidebar {
                margin-inline-start: 260px;
                width: calc(100% - 260px);
            }
            .topbar.topbar-fullwidth {
                margin-inline-start: 0 !important;
                width: 100% !important;
            }
            .provider-main {
                min-height: calc(100vh - 64px);
                padding: 1.5rem;
            }
            .provider-main.main-with-sidebar {
                margin-inline-start: 260px;
            }
            .provider-main.main-fullwidth {
                margin-inline-start: 0 !important;
                width: 100% !important;
            }
        }

        /* Mobile Layout */
        @media(max-width: 991.98px) {
            .provider-sidebar { min-height: auto; --bs-offcanvas-width: 280px; }
            .provider-main { margin: 0 !important; width: 100% !important; overflow-x: hidden; padding: 1rem !important; }
            .topbar { width: 100% !important; margin: 0 !important; }
        }

        @media(max-width: 575.98px) {
            .topbar { height: 58px !important; min-height: 58px !important; padding-left: .75rem !important; padding-right: .75rem !important; }
            .brand-text { font-size: 1.35rem !important; }
            .topbar-circle-btn { width: 38px !important; height: 38px !important; font-size: .9rem; }
            .topbar-pill-btn { height: 38px !important; padding: 0 .65rem !important; font-size: .82rem !important; gap: .35rem !important; }
            .topbar-user-pill { height: 38px !important; padding: 0 !important; background: transparent !important; border: none !important; }
            .topbar-user-avatar { width: 36px !important; height: 36px !important; font-size: .88rem; border-radius: 11px !important; }
            .topbar-divider { display: none !important; }
        }

        @media print {
            *, *::before, *::after {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .topbar, .provider-sidebar, #provider-menu, .modal, .d-print-none, .btn {
                display: none !important;
            }
            .provider-main {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            body {
                background: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
@auth('provider')
    @php
        $portalUser = auth('provider')->user();
        $portal = app(\App\Modules\Providers\Services\ProviderPortalService::class);
        $providerProfile = $portal->profileFor($portalUser);
        $providerLinks = [
            ['dashboard.view', 'provider.dashboard', 'provider.dashboard', 'fa-chart-line', __('Dashboard')],
            ['orders.view', 'provider.orders.index', 'provider.orders.*', 'fa-calendar-check', __('Bookings')],
            ['emergencies.view', 'provider.emergencies.index', 'provider.emergencies.*', 'fa-triangle-exclamation', __('Emergency requests')],
            ['prices.view', 'provider.prices.index', 'provider.prices.*', 'fa-tags', __('Prices and approvals')],
            ['finance.view', 'provider.finance.index', 'provider.finance.*', 'fa-coins', __('Financial accounts')],
            ['ratings.view', 'provider.ratings.index', 'provider.ratings.*', 'fa-star', __('Ratings')],
            ['users.manage', 'provider.team.index', 'provider.team.*', 'fa-users-gear', __('Users and permissions')],
            ['users.manage', 'provider.technicians.index', 'provider.technicians.*', 'fa-screwdriver-wrench', __('Technicians')],
            ['profile.manage', 'provider.settings.index', 'provider.settings.*', 'fa-gear', __('Settings / Profile')],
            ['contract.view', 'provider.contract', 'provider.contract*', 'fa-file-contract', __('Provider contract')],
        ];
        $hasSidebar = (bool) ($providerProfile && $providerProfile->is_approved);
        $portalHome = null;
        if ($hasSidebar) {
            if (!$providerProfile->contract_accepted_at) {
                $portalHome = $portal->allows($portalUser, $providerProfile, 'contract.view')
                    ? 'provider.contract'
                    : null;
            } else {
                foreach ($providerLinks as [$permission, $route]) {
                    if ($portal->allows($portalUser, $providerProfile, $permission)) {
                        $portalHome = $route;
                        break;
                    }
                }
            }
        }

        $rawTitle = View::yieldContent('title', __('Provider portal'));
        $cleanTitle = trim(str_replace(['· Rounder', '· Rounder', 'Rounder -', '· راوندر', '- راوندر', 'Rounder'], '', $rawTitle));
        if (empty($cleanTitle)) {
            $cleanTitle = __('Dashboard');
        }
    @endphp

    {{-- Topbar identical to Admin --}}
    <nav class="navbar topbar px-3 px-lg-4 {{ $hasSidebar ? 'topbar-with-sidebar' : 'topbar-fullwidth' }} d-print-none">
        <div class="d-flex align-items-center gap-2 gap-md-3 flex-shrink-0">
            @if($hasSidebar)
                <button class="topbar-circle-btn d-lg-none me-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#provider-menu" aria-controls="provider-menu" aria-label="{{ __('Open menu') }}">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>
            @endif
            <a class="navbar-brand brand {{ $hasSidebar ? 'd-lg-none' : '' }} d-flex align-items-center gap-2 m-0 text-decoration-none" href="{{ $portalHome ? route($portalHome) : '#' }}">
                <span class="brand-text text-rounder-orange">rounder</span>
                <span class="badge rounded-pill brand-badge-mint d-none d-sm-inline-flex">{{ __('Provider portal') }}</span>
            </a>
            <div class="{{ $hasSidebar ? 'd-none d-lg-flex' : 'd-none d-md-flex' }} align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;border-radius:8px;background:rgba(2,51,62,0.06);color:var(--petrol);">
                    <i class="fa-solid fa-layer-group" style="font-size:0.8rem;"></i>
                </span>
                <span class="fw-bold text-dark fs-6">{{ $cleanTitle }}</span>
            </div>
        </div>

        {{-- Left Actions: Date Pill, Divider, Language, Divider, User Pill --}}
        <div class="topbar-actions-group">
            <div class="topbar-date-pill d-none d-md-inline-flex">
                <i class="fa-regular fa-calendar-days text-rounder-orange"></i>
                <span>{{ \Carbon\Carbon::now()->locale(app()->getLocale())->translatedFormat('d F Y') }}</span>
            </div>

            <div class="topbar-divider d-none d-md-block"></div>

            <div class="dropdown">
                <button class="topbar-pill-btn px-2 px-sm-3" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ __('Language') }}" title="{{ __('Language') }}">
                    <i class="fa-solid fa-globe opacity-75"></i>
                    <span class="d-none d-sm-inline">{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                    <span class="d-sm-none fw-bold small">{{ strtoupper(app()->getLocale()) }}</span>
                    <i class="fa-solid fa-chevron-down small opacity-50 d-none d-sm-inline" style="font-size:0.7rem;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-1.5 lang-dropdown-menu">
                    <li>
                        <form method="post" action="{{ route('locale.update') }}">
                            @csrf
                            <input type="hidden" name="locale" value="ar">
                            <button type="submit" class="lang-dropdown-item {{ app()->getLocale() === 'ar' ? 'is-active' : '' }}">
                                <span class="d-flex align-items-center gap-2"><span>🇸🇦</span> <span>العربية</span></span>
                                @if(app()->getLocale() === 'ar')<i class="fa-solid fa-check lang-check"></i>@endif
                            </button>
                        </form>
                    </li>
                    <li>
                        <form method="post" action="{{ route('locale.update') }}">
                            @csrf
                            <input type="hidden" name="locale" value="en">
                            <button type="submit" class="lang-dropdown-item {{ app()->getLocale() === 'en' ? 'is-active' : '' }}">
                                <span class="d-flex align-items-center gap-2"><span>🇬🇧</span> <span>English</span></span>
                                @if(app()->getLocale() === 'en')<i class="fa-solid fa-check lang-check"></i>@endif
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

            @if($portalUser)
            @php
                $initialLetter = mb_substr($portalUser->name, 0, 1);
            @endphp
            <div class="topbar-divider d-none d-sm-block"></div>

            <div class="dropdown">
                <button class="topbar-user-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ $portalUser->name }}" title="{{ $portalUser->name }}">
                    <div class="topbar-user-avatar">{{ $initialLetter }}</div>
                    <div class="d-none d-md-block" style="text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};">
                        <div class="topbar-user-name">{{ $portalUser->name }}</div>
                        <div class="topbar-user-role">{{ $providerProfile?->company_name ?? __('Service provider') }}</div>
                    </div>
                    <i class="fa-solid fa-chevron-down topbar-user-chevron ms-1 d-none d-md-inline"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2 topbar-user-dropdown">
                    <li class="px-3 py-2 border-bottom mb-1">
                        <div class="fw-bold text-dark">{{ $portalUser->name }}</div>
                        <div class="small text-secondary">{{ $providerProfile?->company_name ?? '' }}</div>
                        <span class="badge mt-1" style="background: rgba(2,51,62,0.08); color: var(--petrol); font-size: 0.72rem; font-weight: 600;">{{ __('Service provider') }}</span>
                    </li>
                    @if($providerProfile && $providerProfile->contract_accepted_at && $portal->allows($portalUser, $providerProfile, 'profile.manage'))
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('provider.settings.index') }}">
                            <i class="fa-solid fa-gear text-secondary" style="width:1.2rem;"></i>
                            <span>{{ __('Settings / Profile') }}</span>
                        </a>
                    </li>
                    @endif
                    <li>
                        <form method="post" action="{{ route('provider.logout') }}" class="m-0">
                            @csrf
                            <button class="dropdown-item text-danger d-flex align-items-center gap-2 py-2" type="submit">
                                <i class="fa-solid fa-right-from-bracket" style="width:1.2rem;"></i>
                                <span>{{ __('Sign out') }}</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
            @endif
        </div>
    </nav>

    {{-- Provider Sidebar identical to Admin --}}
    @if($providerProfile && $providerProfile->is_approved)
        <aside class="offcanvas-lg offcanvas-start provider-sidebar d-print-none" tabindex="-1" id="provider-menu" aria-label="{{ __('Provider navigation') }}">
            <div class="sidebar-brand-header d-flex align-items-center justify-content-between px-3 px-lg-4">
                <a class="navbar-brand brand d-flex align-items-center gap-2 m-0 text-decoration-none" href="{{ $portalHome ? route($portalHome) : '#' }}">
                    <span class="brand-text text-rounder-orange">rounder</span>
                    <span class="badge rounded-pill brand-badge-mint">{{ __('Provider portal') }}</span>
                </a>
                <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#provider-menu" aria-label="{{ __('Close menu') }}"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column p-3">
                <nav aria-label="{{ __('Provider navigation') }}" class="w-100 flex-grow-1 d-flex flex-column">
                    @if($providerProfile->contract_accepted_at)
                        @foreach($providerLinks as [$permission, $route, $activeRoute, $icon, $label])
                            @if($portal->allows($portalUser, $providerProfile, $permission))
                                <a class="provider-sidebar-link {{ request()->routeIs($activeRoute) ? 'active' : '' }}" href="{{ route($route) }}">
                                    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                                    <span>{{ $label }}</span>
                                </a>
                            @endif
                        @endforeach
                    @elseif($portal->allows($portalUser, $providerProfile, 'contract.view'))
                        <a class="provider-sidebar-link active" href="{{ route('provider.contract') }}">
                            <i class="fa-solid fa-file-contract" aria-hidden="true"></i>
                            <span>{{ __('Provider contract') }}</span>
                        </a>
                    @endif
                </nav>
            </div>
        </aside>
    @endif
@endauth

<main class="provider-main {{ ($hasSidebar ?? false) ? 'main-with-sidebar' : 'main-fullwidth' }}">
    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <div>{{ session('status') }}</div>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger mb-3">
            <div class="fw-bold mb-1"><i class="fa-solid fa-circle-xmark me-1"></i>{{ __('Please correct the errors below:') }}</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @yield('body')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>

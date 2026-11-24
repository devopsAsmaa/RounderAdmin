<?php

namespace App\Modules\Identity\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Requests\ProviderLoginRequest;
use App\Modules\Providers\Services\ProviderPortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function show(): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        if (Auth::guard('provider')->check()) {
            $user = Auth::guard('provider')->user();
            $profile = app(ProviderPortalService::class)->profileFor($user);

            return $this->destination($profile);
        }

        return view('provider.login');
    }

    public function login(ProviderLoginRequest $request, ProviderPortalService $portal): \Illuminate\Http\RedirectResponse
    {
        if (Auth::guard('provider')->check()) {
            return $this->destination($portal->profileFor(Auth::guard('provider')->user()));
        }

        $data = $request->validated();
        $user = $portal->authenticateProvider($data['login'], $data['password']);

        $request->session()->regenerate();

        return $this->destination($portal->profileFor($user));
    }

    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        Auth::guard('provider')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('provider.login');
    }

    private function destination(?\App\Modules\Providers\Models\ProviderProfile $profile): \Illuminate\Http\RedirectResponse
    {
        abort_unless($profile, 403);

        if (!$profile->is_approved) {
            return redirect()->route('provider.pending');
        }

        if (!$profile->contract_accepted_at) {
            return redirect()->route('provider.contract');
        }

        return redirect()->route('provider.dashboard');
    }
}


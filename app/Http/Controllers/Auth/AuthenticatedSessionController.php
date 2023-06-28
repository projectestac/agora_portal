<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Access;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller {
    /**
     * Display the login view.
     */
    public function create(): View {
        return view('auth.login');
    }

    public function chooseLogin(): View {
        return view('auth.choose');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        if (is_object($user)) {
            $user->last_login_at = now();
            $user->save();
        }

        if (Access::isAdmin($user)) {
            return redirect()->intended(RouteServiceProvider::ADMIN);
        }

        if (Access::isClient($user) || Access::isManager($user)) {
            return redirect()->intended(RouteServiceProvider::MY_AGORA);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

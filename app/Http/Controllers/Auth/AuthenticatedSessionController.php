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
use Spatie\Permission\Models\Role;

/**
 * Controller used when the user logs in with local credentials. For Google login, see GoogleController.
 */
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
        if (is_null($user)) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        if (is_object($user)) {
            $user->last_login_at = now();
            $user->save();
        }

        $result = Access::completeLogin($user);

        return redirect()->intended($result['route'])->with('error', $result['error']);

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

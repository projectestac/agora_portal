<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Access;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

/**
 * Controller used when the user logs in with Google. For local login, see AuthenticatedSessionController.
 */
class GoogleController extends Controller {
    public function redirectToGoogle() {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse {

        $userGoogle = Socialite::driver('google')->stateless()->user();

        // The username is the email without the domain.
        [$username, $domain] = explode('@', $userGoogle->email);

        if ($domain !== 'xtec.cat') {
            return redirect('/')->with('error', __('login.only_xtec'));
        }

        $user = User::UpdateOrCreate(
            ['email' => $userGoogle->email],
            ['name' => $username]
        );

        $user->last_login_at = now();
        $user->save();

        $result = Access::completeLogin($user);

        return redirect()->intended($result['route'])->with('error', $result['error']);

    }

}

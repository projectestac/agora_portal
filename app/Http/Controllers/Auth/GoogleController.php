<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Access;
use App\Helpers\Util;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller {
    public function redirectToGoogle() {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback() {

        // The Google credentials are stored in the database, so the values defined in
        // config/services.php are overwritten.
        Config::set('services.google.client_id', Util::getConfigParam('google_client_id'));
        Config::set('services.google.client_secret', Util::getConfigParam('google_client_secret'));
        Config::set('services.google.redirect', Util::getConfigParam('google_redirect_uri'));

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

        Auth::login($user, true);

        if (Access::isAdmin($user)) {
            return redirect('/instances');
        }

        if (Access::isClient($user) || Access::isManager($user)) {
            return redirect()->route('myagora');
        }

        return redirect()->route('home');
    }
}

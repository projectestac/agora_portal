<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Access;
use App\Helpers\Util;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller {
    public function redirectToGoogle() {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback() {

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

        $util = new Util();
        $clientController = new ClientController();
        $clientExists = $clientController->existsClient($username);
        $error = '';

        // If the user corresponds to a client, try to create the client if it doesn't exist.
        if ($util->isValidCode($username)) {
            // Get school data from WS.
            $data = $util->getSchoolFromWS($username);

            // Test data:
            // $data['error'] = 0;
            // $data['message'] = 'a8000001$$esc-tramuntana$$Escola Tramuntana$$c. Rosa dels Vents, 8$$Valldevent$$09999';

            if ($data['error'] === 0 && !$clientExists) {
                $clientController->createClientFromWS($data['message']);
                $clientController->setClientPermissions($username);
            } else {
                $error = $data['message'];
            }
        }

        Auth::login($user, true);

        if (Access::isAdmin($user)) {
            return redirect('/instances');
        }

        if (Access::isClient($user) || Access::isManager($user)) {
            if ($clientExists) {
                return redirect()->route('myagora.instances')->with('error', $error);
            }
        }

        return redirect()->route('home')->with('error', $error);

    }

}

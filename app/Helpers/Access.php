<?php

namespace App\Helpers;

use App\Http\Controllers\ClientController;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class Access {

    public static function completeLogin(User|Authenticatable $user): array {

        $util = new Util();
        $clientController = new ClientController();
        $clientExists = $clientController->existsClient($user['name']);
        $error = '';

        // If the user corresponds to a client, try to create the client if it doesn't exist.
        if ($util->isValidCode($user['name'])) {
            // Get school data from WS.
            $data = $util->getSchoolFromWS($user['name']);

            // Test data:
            // $data['error'] = 0;
            // $data['message'] = 'a8000001$$esc-tramuntana$$Escola Tramuntana$$c. Rosa dels Vents, 8$$Valldevent$$09999';

            if ($data['error'] === 0 && !$clientExists) {
                $clientController->createClientFromWS($data['message']);
                $clientController->setClientPermissions($user['name']);
            } else {
                $error = $data['message'];
            }
        }

        Auth::login($user, true);

        if (self::isAdmin($user)) {
            return [
                'route' => RouteServiceProvider::ADMIN,
                'error' => $error,
            ];
        }

        if (self::isClient($user) || self::isManager($user)) {
            if ($clientExists) {
                return [
                    'route' => RouteServiceProvider::MY_AGORA,
                    'error' => $error,
                ];
            }
        }

        // If user has logged in and is not an admin, a client or a manager, it must have the role User.
        if (self::isUser($user)) {
            // Check if user has role User.
            $userRole = Role::findByName('user');
            if (!$user->hasRole($userRole)) {
                $user->assignRole($userRole);
            }
        }

        return [
            'route' => RouteServiceProvider::HOME,
            'error' => $error,
        ];

    }

    public static function isAdmin(User|Authenticatable $user): bool {
        return $user->hasPermissionTo('Administrate site');
    }

    public static function isClient(User|Authenticatable $user): bool {
        return $user->hasPermissionTo('Manage own managers');
    }

    public static function isManager(User|Authenticatable $user): bool {
        return $user->hasPermissionTo('Manage clients');
    }

    public static function isUser(User|Authenticatable $user): bool {
        return !(self::isManager($user) || self::isClient($user) || self::isAdmin($user));
    }
}

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

        // If the username has the format of a client code, create the client if it doesn't exist.
        if ($util->isValidCode($user['name'])) {
            // Get school data from WS.
            $data = $util->getSchoolFromWS($user['name']);

            if ($data['error'] === 1) {
                $error = $data['message'];
            }

            // If client is present in WS but doesn't exist in clients table, create it and give it the permissions.
            if (!$clientExists && $data['error'] === 0) {
                // Create the client using the data from WS.
                $clientController->createClientFromWS($data['message']);
                $clientController->setClientPermissions($user['name']);
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

<?php

namespace App\Helpers;

use App\Http\Controllers\ClientController;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Location;
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

            // Test data:
            // $data['error'] = 0;
            // $data['message'] = 'a8000001$$esc-tramuntana$$Escola Tramuntana$$c. Rosa dels Vents, 8$$Valldevent$$09999';

            if ($data['error'] === 1) {
                $error = $data['message'];
            }

            // If client doesn't exist, create it in any case and give it the permissions.
            if (!$clientExists) {
                if ($data['error'] === 1) {
                    // If there is an error, create the client with minimal data.
                    $client = new Client([
                        'code' => $user['name'],
                        'name' => $user['name'],
                        'dns' => $user['name'],
                        'location_id' => Location::UNDEFINED,
                        'type_id' => ClientType::UNDEFINED,
                        'status' => Client::STATUS_ACTIVE,
                        'visible' => 'yes',
                    ]);
                    $client->save();
                } else {
                    // Create the client using the data from WS.
                    $clientController->createClientFromWS($data['message']);
                }

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

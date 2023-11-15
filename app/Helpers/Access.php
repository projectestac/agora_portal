<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class Access {
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

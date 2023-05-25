<?php

namespace App\Helpers;

use App\Models\User;

class Access {
    public static function isAdmin(User $user): bool {
        return $user->hasPermissionTo('Administrate site');
    }

    public static function isClient(User $user): bool {
        return $user->hasPermissionTo('Manage own managers');
    }

    public static function isManager(User $user): bool {
        return $user->hasPermissionTo('Manage clients');
    }
}

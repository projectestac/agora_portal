<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The Laravel session will be used as a cache to store several data.
 */
class Cache {
    public static function get_clients(Request $request): mixed {
        $clients = $request->session()->get('clients');

        // If the clients are not in the session, get them from the database.
        if (is_null($clients)) {
            $user = Auth::user();
            $managers = $user->manager->all();

            foreach ($managers as $manager) {
                $client = $manager->client;
                $clients[] = [
                    'id' => $client->id,
                    'name' => $client->name,
                    'code' => $client->code,
                ];
            }

            $request->session()->put('clients', $clients);
        }

        return is_null($clients) ? [] : $clients;
    }
}

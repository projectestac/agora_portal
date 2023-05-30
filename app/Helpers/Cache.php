<?php

namespace App\Helpers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The Laravel session will be used as a cache to store several data.
 */
class Cache {
    public static function get_clients(Request $request): mixed {
        $clients = $request->session()->get('clients');

        $user = Auth::user();

        // If the clients are not in the session, get them from the database.
        if (is_null($clients) && Access::isManager($user)) {

            // Get a record for each client where the user is a manager.
            $managers = $user->manager->all();

            if (empty($managers)) {
                return [];
            }

            // $clients will be an array of arrays, where each array will correspond to a client.
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

        if (is_null($clients) && Access::isClient($user)) {
            $client = Client::where('code', $user->name)->first();
            $clients[] = [
                'id' => $client->id,
                'name' => $client->name,
                'code' => $client->code,
            ];

            $request->session()->put('clients', $clients);
        }

        return is_null($clients) ? [] : $clients;
    }

    public static function get_current_client(Request $request) {

        $clients = self::get_clients($request);
        $current_client = $request->session()->get('current_client');

        // If there is no current client, get the first client from the list of clients if there is any.
        if (is_null($current_client) && count($clients) > 0) {
            $current_client = $clients[0];
            $request->session()->put('current_client', $current_client);
        } elseif (is_null($current_client)) {
            $current_client = [];
        }

        return $current_client;
    }
}

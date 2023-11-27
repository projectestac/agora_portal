<?php

namespace App\Helpers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The Laravel session will be used as a cache to store several data.
 */
class Cache {

    public static function getClients(Request $request): mixed {

        $user = Auth::user();

        // If the clients are not in the session, get them from the database.
        if (Access::isManager($user)) {
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
                    'dns' => $client->dns,
                ];
            }
        }

        if (!isset($clients) && Access::isClient($user)) {
            $client = Client::where('code', $user->name)->first();

            if (is_null($client)) {
                return [];
            }

            $clients[] = [
                'id' => $client->id,
                'name' => $client->name,
                'code' => $client->code,
                'dns' => $client->dns,
            ];
        }

        return $clients ?? [];
    }

    public static function getCurrentClient(Request $request) {

        $clients = self::getClients($request);
        $currentClient = $request->session()->get('currentClient');

        // If there is no current client, get the first client from the list of clients if there is any.
        if (is_null($currentClient) && count($clients) > 0) {
            $currentClient = $clients[0];
            $request->session()->put('currentClient', $currentClient);
        } elseif (is_null($currentClient)) {
            $currentClient = [];
        }

        return $currentClient;
    }

}

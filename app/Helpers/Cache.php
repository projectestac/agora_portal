<?php

namespace App\Helpers;

use App\Models\Client;
use App\Models\Instance;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

/**
 * The Laravel session will be used as a cache to store several data.
 */
class Cache {

    public static function getClients(Request $request): mixed {
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

    public static function getCurrentInstance(Request $request, $clientId = 0, $serviceName = 'Moodle') {

        $currentInstance = $request->session()->get('currentInstance');

        if (!is_null($currentInstance)) {
            return $currentInstance;
        }

        if (empty($clientId)) {
            $currentClient = self::getCurrentClient($request);
            $clientId = $currentClient['id'];
        }

        $serviceId = Service::where('name', $serviceName)
            ->pluck('id')
            ->first();

        $currentInstance = Instance::where('client_id', $clientId)
            ->where('service_id', $serviceId)
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->first();

        // Protect against the case where there is no instance for the specified client and service.
        if ($currentInstance) {
            $currentInstanceArray = $currentInstance->toArray();
        }

        $request->session()->put('currentInstance', $currentInstanceArray);

        return $currentInstanceArray;

    }

    /**
     * Get the database name for the specified service. It checks if it was already stored in the session. If not,
     * it will be calculated and stored in the session. In that case, the client used to calculate the database name
     * would be the current client (the one stored in the session).
     *
     * @param Request $request
     * @param string $serviceName
     * @return string
     */
    public static function getDBName(Request $request, string $serviceName = 'Moodle'): string {

        $dbName = $request->session()->get('dbName_' . $serviceName);

        if (!is_null($dbName)) {
            return $dbName;
        }

        if (is_null($request)) {
            return '';
        }

        $currentClient = self::getCurrentClient($request);

        $serviceId = Service::where('name', $serviceName)
            ->where('status', 'active')
            ->pluck('id')
            ->first();

        $instanceId = Instance::where('client_id', $currentClient['id'])
            ->where('service_id', $serviceId)
            ->where('status', 'active')
            ->pluck('id')
            ->first();

        $dbName = Config::get('app.agora.moodle2.userprefix') . $instanceId;
        $request->session()->put('dbName_' . $serviceName, $dbName);

        return $dbName;
    }

}

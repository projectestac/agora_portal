<?php

namespace App\Http\Controllers;

use App\Helpers\Util;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Location;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;
use Illuminate\Contracts\View\View;

class ClientController extends Controller {

    public function __construct() {
        $this->middleware('auth')->except('getActiveClients');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View {
        return view('admin.client.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View {
        $locations = Location::all()->sortBy('name');
        $clientTypes = ClientType::all()->sortBy('name');

        return view('admin.client.create')
            ->with('locations', $locations)
            ->with('client_types', $clientTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request): RedirectResponse {
        $name = $request->input('name');
        $code = $request->input('code');
        $dns = $request->input('dns');
        $oldDns = $request->input('old_dns');
        $status = $request->input('status');
        $locationId = $request->input('location');
        $clientTypeId = $request->input('client_type');
        $visible = $request->input('visible');

        $client = new Client([
            'name' => $name,
            'code' => $code,
            'dns' => $dns,
            'old_dns' => $oldDns,
            'status' => $status,
            'location_id' => $locationId,
            'type_id' => $clientTypeId,
            'visible' => $visible,
        ]);

        try {
            $client->save();
        } catch (\Exception $e) {
            $locations = Location::all()->sortBy('name');
            $clientTypes = ClientType::all()->sortBy('name');

            return redirect()->route('clients.create')
                ->with('locations', $locations)
                ->with('client_types', $clientTypes)
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }

        Log::insert([
            'client_id' => $client->id,
            'user_id' => Auth::user()->id,
            'action_type' => Log::ACTION_TYPE_ADD,
            'action_description' => __('client.created_client', [
                'user' => Auth::user()->name,
                'name' => $client->name,
                'code' => $client->code,
                'dns' => $client->dns,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('clients.index')
            ->with('success', __('client.created_client_short'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client): View {
        $locations = Location::all()->sortBy('name');
        $clientTypes = ClientType::all()->sortBy('name');

        return view('admin.client.edit')
            ->with('client', $client)
            ->with('locations', $locations)
            ->with('types', $clientTypes);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client) {

        $client->name = $request->input('name');
        $client->code = $request->input('code');
        $client->dns = $request->input('dns');
        $client->old_dns = $request->input('old_dns');
        $client->status = $request->input('status');
        $client->location_id = $request->input('location');
        $client->type_id = $request->input('client_type');
        $client->visible = $request->input('visible');

        try {
            $client->save();
        } catch (\Exception $e) {
            $locations = Location::all()->sortBy('name');
            $clientTypes = ClientType::all()->sortBy('name');

            return redirect()->route('clients.index')
                ->with('client', $client)
                ->with('locations', $locations)
                ->with('client_types', $clientTypes)
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }

        Log::insert([
            'client_id' => $client->id,
            'user_id' => Auth::user()->id,
            'action_type' => Log::ACTION_TYPE_EDIT,
            'action_description' => __('client.updated_client', [
                'user' => Auth::user()->name,
                'name' => $client->name,
                'code' => $client->code,
                'dns' => $client->dns,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('clients.index')
            ->with('success', __('client.updated_client_short'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client) {
        //
    }

    public function getClients(Request $request): JsonResponse {

        $search = $request->validate(['search.value' => 'string|max:50|nullable']);
        $searchValue = $search['search']['value'] ?? '';

        $columns = $request->input('columns');
        $order = $request->input('order')[0];
        $orderColumn = 'clients.' . $columns[$order['column']]['data'] ?? 'clients.updated_at';
        $orderDirection = $order['dir'] ?? 'desc';

        if ($orderColumn === 'clients.services') {
             $orderColumn = 'services.name';
        }

        if ($orderColumn === 'clients.dates') {
            $orderColumn = 'clients.updated_at';
        }

        $clients = Client::select('clients.*')
            ->selectRaw('GROUP_CONCAT(services.name) as service_names')
            ->leftJoin('instances', 'clients.id', '=', 'instances.client_id')
            ->leftJoin('services', 'instances.service_id', '=', 'services.id')
            ->groupBy('clients.id')
            ->orderBy($orderColumn, $orderDirection);

        if (!empty($searchValue)) {
            $clients = $clients->where('clients.code', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('clients.name', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('clients.dns', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('clients.old_dns', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('clients.city', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('services.name', 'LIKE', '%' . $searchValue . '%');
        }

        $clients = $clients->get();

        return Datatables::make($clients)
            ->addColumn('name', function ($client) {
                return new HtmlString('<a href="' . route('myagora.instances', ['code' => $client->code]) . '">' .
                    $client->name . '</a>');
            })
            ->addColumn('services', function ($client) {
                $html = '';
                foreach ($client->instances as $instance) {
                    $url = Util::getInstanceUrl($instance);
                    $html .= view('admin.client.service', [
                        'url' => $url,
                        'serviceName' => $instance->service->name,
                        'clientName' => $instance->client->name,
                    ])->render();
                }
                return new HtmlString($html);
            })
            ->addColumn('dates', function ($client) {
                return new HtmlString('<strong>C:</strong> ' . $client->created_at->format('d/m/Y') . '<br/>' .
                    '<strong>E:</strong> ' . $client->updated_at->format('d/m/Y'));
            })
            ->addColumn('actions', static function ($client) {
                return view('admin.client.action', ['client' => $client]);
            })
            ->make();

    }

    // for public portal
    public function getActiveClients(Request $request): JsonResponse {
        $clients = Client::select([
            'clients.id',
            'clients.name as client_name', // Avoid conflict with services.name
            'clients.city',
        ])
            ->where('clients.status', 'active')
            ->where('clients.visible', 'yes');

        if ($request->filled('location_id')) {
            $locationData = $request->validate([
                'location_id' => 'string|exists:locations,id',
            ]);
            $clients->where('clients.location_id', $locationData['location_id']);
        }

        if ($request->filled('type_id')) {
            $clientTypeData = $request->validate([
                'type_id' => 'string|exists:client_types,id',
            ]);
            $clients->where('clients.type_id', $clientTypeData['type_id']);
        }

        if ($request->filled('service_id')) {
            $serviceData = $request->validate([
                'service_id' => 'string|exists:services,id',
            ]);
            $clients->where('services.id', $serviceData['service_id']);
        }

        $clients->leftJoin('instances', 'clients.id', '=', 'instances.client_id');
        $clients->leftJoin('services', 'instances.service_id', '=', 'services.id');
        $clients->groupBy('clients.id')->get();

        return Datatables::make($clients)
            ->addColumn('instances_links', function ($client) {
                $links = '';

                foreach ($client->instances as $instance) {
                    $instanceUrl = Util::getInstanceUrl($instance);
                    $instanceLogo = secure_asset('images/' . mb_strtolower($instance->service->name) . '.gif');
                    $links .= '<a href="' . $instanceUrl . '" target="_blank"><img src="' . $instanceLogo . '" alt=""></a>&nbsp;&nbsp;&nbsp;';
                }

                return new HtmlString($links);
            })->make();
    }

    public function search(Request $request) {
        $keyword = $request->input('keyword');

        $clients = Client::where('name', 'like', '%' . $keyword . '%')
                         ->get(['code', 'name']);

        return response()->json($clients);
    }


    public function createClientFromWS(mixed $data): void {
        // a8000001$$esc-tramuntana$$Escola Tramuntana$$c. Rosa dels Vents, 8$$Valldevent$$09999
        $data = explode('$$', $data);

        $client = new Client([
            'code' => $data[0],
            'name' => $data[2],
            'dns' => $data[1],
            'address' => $data[3],
            'city' => $data[4],
            'postal_code' => $data[5],
            'location_id' => Location::UNDEFINED,
            'type_id' => ClientType::UNDEFINED,
            'status' => Client::STATUS_ACTIVE,
            'visible' => 'yes',
        ]);

        $client->save();
    }

    public function existsClient(string $code): bool {
        $client = Client::where('code', $code)->first();

        if ($client) {
            return true;
        }
        return false;
    }

    public function setClientPermissions(string $username): void {
        $user = User::where('name', $username)->first();
        $clientRole = Role::findByName('client');
        $user->assignRole($clientRole);
    }

    /**
     * Update client information from the import_clients table, which must be created and populated manually. This function
     * is intended to be used locally, not in production.
     */
    public function import(): void {

        // Get all clients from the clients table with the information about location.
        $clients = Client::with('location')->get();

        $util = new Util();

        foreach ($clients as $client) {
            // The table import_clients must contain the public information about the clients. It can be created from
            // the public spreadsheet of school centers.
            $code = $util->transformClientCode($client->code);
            $newData = DB::select('SELECT * FROM import_clients WHERE code = ?', [$code]);

            // If the client is not found in the import_clients table, it means that it is not a real school. As long as
            // there is no information about it, it cannot be updated.
            if (empty($newData)) {
                continue;
            }

            $client->name = $newData[0]->name;
            $client->address = $newData[0]->address;
            $client->city = $newData[0]->city;
            $client->postal_code = $newData[0]->postal_code;

            // Update the location. It is important that the location name in the import_clients table is the same as in the locations
            // table. Otherwise, the location will be set to UNDEFINED.
            $newLocation = Location::where('name', $newData[0]->location_name)->first();
            if (!empty($newLocation)) {
                $client->location_id = $newLocation->id;
            } else {
                $client->location_id = Location::UNDEFINED;
            }

            // If there has been any change, show the original data and the new data.
            if ($client->isDirty()) {
                $clientOriginal = $client->getOriginal();
                echo '<br/><strong>' . $clientOriginal['name'] . ' (' . $clientOriginal['code'] . ')</strong><br/>';
                foreach ($client->getDirty() as $key => $value) {
                    echo $key . ': ' . $clientOriginal[$key] . ' => ' . $value . '<br/>';
                }
            }

            // Update the client.
            $client->save();

        }

        echo '<br/>End of import.';
    }

}

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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;
use Illuminate\Contracts\View\View;

class ClientController extends Controller {

    public function __construct() {
        $this->middleware('auth');
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

            return redirect()->route('clients.update')
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

    public function getClients(): JsonResponse {

        $clients = Client::orderBy('id', 'asc');

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

}

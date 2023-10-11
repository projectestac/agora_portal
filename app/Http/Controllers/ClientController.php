<?php

namespace App\Http\Controllers;

use App\Helpers\Util;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request) {
        //
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
    public function edit(Client $client) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client) {
        //
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

    public function createClientFromWS(mixed $data) {
        // a8000001$$esc-tramuntana$$Escola Tramuntana$$c. Rosa dels Vents, 8$$Valldevent$$09999
        $data = explode('$$', $data);

        $client = new Client();
        $client->code = $data[0];
        $client->dns = $data[1];
        $client->name = $data[2];
        $client->address = $data[3];
        $client->city = $data[4];
        $client->postal_code = $data[5];
        $client->status = Client::STATUS_ACTIVE;
        $client->visible = 'yes';

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

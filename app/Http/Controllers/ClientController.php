<?php

namespace App\Http\Controllers;

use App\Helpers\Util;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\HtmlString;
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
        return Datatables::make(Client::all())
            ->addColumn('services', static function ($client) {
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
            ->addColumn('actions', static function ($client) {
                return view('admin.client.action', ['client' => $client]);
            })
            ->make();
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Service;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;

class InstanceController extends Controller {
    public function index(): View|Application|Factory|ApplicationContract {
        $instances = DB::table('client_service')
            ->join('clients', 'client_service.client_id', '=', 'clients.id')
            ->join('locations', 'clients.location_id', '=', 'locations.id')
            ->join('client_types', 'clients.type_id', '=', 'client_types.id')
            ->join('services', 'client_service.service_id', '=', 'services.id')
            ->select('client_service.*', 'clients.name as client_name', 'services.name as service_name', 'locations.name as location_name', 'client_types.name as client_type_name')
            ->paginate(10);

        return view('admin.instance.index')->with('instances', $instances);
    }

}

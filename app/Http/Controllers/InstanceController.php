<?php

namespace App\Http\Controllers;

use App\Helpers\Cache;
use App\Models\Instance;
use App\Models\ModelType;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstanceController extends Controller {
    public function index(): View {
        $instances = Instance::select('instances.*', 'clients.name as client_name', 'services.name as service_name')
            ->with('modelType')
            ->join('clients', 'instances.client_id', '=', 'clients.id')
            ->join('services', 'instances.service_id', '=', 'services.id')
            ->paginate(10);

        return view('admin.instance.index')->with('instances', $instances);
    }

    public function create(Request $request): View {
        $service_id = $request->input('service_id');

        $service = Service::find($service_id)->toArray();
        $models = ModelType::where('service_id', $service_id)->get()->toArray();
        $username = Auth::user()->name;
        $currentClient = Cache::getCurrentClient($request);

        return view('myagora.instance_create')
            ->with('service', $service)
            ->with('models', $models)
            ->with('username', $username)
            ->with('client_id', $currentClient['id']);
    }

    public function store(Request $request): RedirectResponse {
        $clientId = $request->input('client_id');
        $serviceId = $request->input('service_id');
        $quota = Service::find($serviceId)->quota;
        $modelTypeId = $request->input('model_type_id');
        $contactProfile = $request->input('contact_profile');

        $instance = new Instance([
            'client_id' => $clientId,
            'service_id' => $serviceId,
            'status' => 'pending',
            'db_id' => 0,
            'quota' => $quota,
            'model_type_id' => $modelTypeId,
            'contact_profile' => $contactProfile,
        ]);
        $instance->save();

        return redirect()->route('myagora.instances');
    }

}

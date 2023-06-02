<?php

namespace App\Http\Controllers;

use App\Helpers\Access;
use App\Helpers\Cache;
use App\Helpers\Util;
use App\Models\Client;
use App\Models\Instance;
use App\Models\Log;
use App\Models\Manager;
use App\Models\RequestType;
use App\Models\Service;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MyAgoraController extends Controller {

    public function myagora(): RedirectResponse {
        return redirect()->route('myagora.instances');
    }

    public function instances(Request $request): View|Application|Factory|ApplicationContract {

        // If the current user is admin, try to get a client code from the URL. Otherwise, get the
        // current client from the session.
        if (Access::isAdmin(Auth::user())) {
            $currentClient = Util::getClientFromUrl($request);
        }

        if (empty($currentClient)) {
            $currentClient = Cache::getCurrentClient($request);
        }

        if (empty($currentClient)) {
            return view('myagora.instance')->with('instances', []);
        }

        $instances = Instance::where('client_id', $currentClient['id'])
            ->with('service')
            ->with('client')
            ->get();

        $activeInstancesClient = Instance::where('client_id', $currentClient['id'])
            ->whereIn('status', ['active', 'pending'])
            ->pluck('service_id')
            ->toArray();

        $availableServices = Service::where('status', 'active')
            ->whereNotIn('id', $activeInstancesClient)
            ->get()
            ->toArray();

        return view('myagora.instance')
            ->with('instances', $instances)
            ->with('currentClient', $currentClient)
            ->with('availableServices', $availableServices);

    }

    public function files(): View|Application|Factory|ApplicationContract {
        if (Access::isClient(Auth::user())) {
            return view('myagora.no_access')->with('message', __('myagora.no_client_access'));
        }

        return view('myagora.file');
    }

    public function requests(Request $request): View|Application|Factory|ApplicationContract {
        if (Access::isClient(Auth::user())) {
            return view('myagora.no_access')->with('message', __('myagora.no_client_access'));
        }

        if (Access::isAdmin(Auth::user())) {
            $currentClient = Util::getClientFromUrl($request);
        }

        if (empty($currentClient)) {
            $currentClient = Cache::getCurrentClient($request);
        }

        if (empty($currentClient)) {
            return view('myagora.request')->with('requests', []);
        }

        $availableRequests = [];
        if (!Access::isClient(Auth::user())) {
            $instances = Instance::where('client_id', $currentClient['id'])
                ->with('service')
                ->get();
            foreach ($instances as $instance) {
                if ($instance->status === 'active') {
                    $availableRequests[$instance->service->name] = DB::table('request_type_service')
                        ->where('service_id', $instance->service->id)
                        ->join('request_types', 'request_type_service.request_type_id', '=', 'request_types.id')
                        ->get()
                        ->toArray();
                }
            }
        }

        // Must declare the namespace of the model to avoid conflicts with the Request class.
        $requests = \App\Models\Request::where('client_id', $currentClient['id'])
            ->latest()
            ->with('user')
            ->with('service')
            ->with('requestType')
            ->paginate(25);

        return view('myagora.request')
            ->with('requests', $requests)
            ->with('currentClient', $currentClient)
            ->with('availableRequests', $availableRequests);
    }

    public function managers(Request $request): View|Application|Factory|ApplicationContract {

        if (Access::isAdmin(Auth::user())) {
            $currentClient = Util::getClientFromUrl($request);
        }

        if (empty($currentClient)) {
            $currentClient = Cache::getCurrentClient($request);
        }

        // Get an array of objects of type Manager.
        $client = Client::find($currentClient['id']);
        $managers = $client->managers->all();

        foreach ($managers as $manager) {
            $manager->name = $manager->user->name;
            $manager->email = $manager->user->email;
        }

        return view('myagora.manager')
            ->with('managers', $managers)
            ->with('currentClient', $currentClient)
            ->with('max_managers', Manager::MAX_MANAGERS_PER_CLIENT);
    }

    public function logs(Request $request): View|Application|Factory|ApplicationContract {

        if (Access::isAdmin(Auth::user())) {
            $currentClient = Util::getClientFromUrl($request);
        }

        if (empty($currentClient)) {
            $currentClient = Cache::getCurrentClient($request);
        }

        if (empty($currentClient)) {
            return view('myagora.log')->with('log', []);
        }

        $log = Log::where('client_id', $currentClient['id'])
            ->latest()
            ->with('user')
            ->paginate(25);

        return view('myagora.log')
            ->with('log', $log)
            ->with('currentClient', $currentClient);

    }

    /**
     * Endpoint for AJAX call used when creating a new request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRequestDetails(Request $request): JsonResponse {

        $option = $request->validate([
            'option' => 'regex:/^\d+:\d+$/',
        ]);

        $requestTypeId = explode(':', $option['option'])[1];
        $requestDetails = RequestType::find($requestTypeId)->first()->toArray();

        $content = view('myagora.components.request_content')
            ->with('requestDetails', $requestDetails)
            ->render();

        return response()->json(['html' => $content]);
    }

}

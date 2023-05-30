<?php

namespace App\Http\Controllers;

use App\Helpers\Access;
use App\Helpers\Cache;
use App\Helpers\Util;
use App\Models\Client;
use App\Models\Log;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyAgoraController extends Controller {

    public const MAX_MANAGERS = 4;

    public function myagora(): RedirectResponse{
        return redirect()->route('myagora.instances');
    }

    public function instances(Request $request): View|Application|Factory|ApplicationContract {

        // If the current user is admin, try to get a client code from the URL. Otherwise, get the
        // current client from the session.
        if (Access::isAdmin(Auth::user())) {
            $current_client = Util::get_client_from_url($request);
        } else {
            $current_client = Cache::get_current_client($request);
        }

        if (empty($current_client)) {
            return view('myagora.instance')->with('instances', []);
        }

        $instances = Client::find($current_client['id'])->services;

        return view('myagora.instance', compact('instances', 'current_client'));

    }

    public function files(): View|Application|Factory|ApplicationContract {
        if (Access::isClient(Auth::user())) {
            return view('myagora.no_access')->with('message', __('myagora.no_client_access'));
        }

        return view('myagora.file');
    }

    public function requests(): View|Application|Factory|ApplicationContract {
        if (Access::isClient(Auth::user())) {
            return view('myagora.no_access')->with('message', __('myagora.no_client_access'));
        }

        return view('myagora.request');
    }

    public function managers(Request $request): View|Application|Factory|ApplicationContract {

        if (Access::isAdmin(Auth::user())) {
            $current_client = Util::get_client_from_url($request);
        } else {
            $current_client = Cache::get_current_client($request);
        }

        // Get an array of objects of type Manager.
        $client = Client::find($current_client['id']);
        $managers = $client->managers->all();

        foreach ($managers as $manager) {
            $manager->name = $manager->user->name;
            $manager->email = $manager->user->email;
        }

        return view('myagora.manager')
            ->with('managers', $managers)
            ->with('current_client', $current_client)
            ->with('max_managers', self::MAX_MANAGERS);
    }

    public function logs(Request $request): View|Application|Factory|ApplicationContract {

        if (Access::isAdmin(Auth::user())) {
            $current_client = Util::get_client_from_url($request);
        } else {
            $current_client = Cache::get_current_client($request);
        }

        if (empty($current_client)) {
            return view('myagora.log')->with('log', []);
        }

        $log = Log::where('client_id', $current_client['id'])
            ->latest()
            ->with('user')
            ->paginate(25);

        return view('myagora.log')->with('log', $log);

    }
}

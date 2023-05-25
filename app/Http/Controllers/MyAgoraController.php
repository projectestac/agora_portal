<?php

namespace App\Http\Controllers;

use App\Helpers\Access;
use App\Helpers\Cache;
use App\Helpers\Util;
use App\Models\Client;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyAgoraController extends Controller {
    public function myagora(): View|Application|Factory|ApplicationContract {
        return view('myagora.index');
    }

    public function instances(Request $request): View|Application|Factory|ApplicationContract {

        if (Access::isClient(Auth::user())) {
            return view('myagora.no_access')->with('message', __('myagora.no_client_access'));
        }

        // Get the clients from the session or the database. In case the clients are not in the
        // session, get them from the database and store them in the session.
        $clients = Cache::get_clients($request);

        // If the current user is admin, try to get a client code from the URL. Otherwise, get the
        // current client from the session.
        if (Access::isAdmin(Auth::user())) {
            $current_client = Util::get_client_from_url($request);
        } else {
            $current_client = $request->session()->get('current_client');
        }

        // If there is no current client, get the first client from the list of clients if there is any.
        if (is_null($current_client) && count($clients) > 0) {
            $current_client = $clients[0];
            $request->session()->put('current_client', $current_client);
        }

        if (empty($current_client)) {
            return view('myagora.instance')->with('instances', []);
        }

        $instances = Client::find($current_client['id'])->services;

        return view('myagora.instance', compact('instances', 'current_client'));

    }

    public function files(): View|Application|Factory|ApplicationContract {
        return view('myagora.file');
    }

    public function requests(): View|Application|Factory|ApplicationContract {
        return view('myagora.request');
    }

    public function managers(): View|Application|Factory|ApplicationContract {
        return view('myagora.manager');
    }

    public function logs(): View|Application|Factory|ApplicationContract {
        return view('myagora.log');
    }
}

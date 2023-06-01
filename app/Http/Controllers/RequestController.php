<?php

namespace App\Http\Controllers;

use App\Helpers\Cache;
use App\Http\Requests\StoreRequestRequest;
use App\Http\Requests\UpdateRequestRequest;
use App\Models\Request;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(): View|Application|Factory|ApplicationContract {
        $requests = Request::with('requestType', 'service', 'client', 'user')->get();
        return view('admin.request.index')->with('requests', $requests);
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
    public function store(StoreRequestRequest $request): RedirectResponse {
        [$serviceId, $requestTypeId] = explode(':', $request->get('request-select-request'));
        $userComment = $request->get('user-comment');

        $requestData = new Request([
            'service_id' => $serviceId,
            'request_type_id' => $requestTypeId,
            'client_id' => Cache::getCurrentClient($request)['id'],
            'user_id' => Auth::user()->id,
            'user_comment' => $userComment ?? '',
        ]);
        $requestData->save();

        return redirect()->back()->with('success', __('request.request_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequestRequest $request, Request $portal_request) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request) {
        //
    }
}

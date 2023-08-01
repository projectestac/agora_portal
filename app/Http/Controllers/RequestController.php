<?php

namespace App\Http\Controllers;

use App\Helpers\Cache;
use App\Helpers\Util;
use App\Http\Requests\StoreRequestRequest;
use App\Http\Requests\UpdateRequestRequest;
use App\Models\Log;
use App\Models\Client;
use App\Models\Request;
use App\Models\RequestType;
use App\Models\Service;
use App\Mail\UpdateRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View {
        $requests = Request::with('requestType', 'service', 'client', 'user')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.request.index')
            ->with('requests', $requests);
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

        Log::insert([
            'client_id' => Cache::getCurrentClient($request)['id'],
            'user_id' => Auth::user()->id,
            'action_type' => Log::ACTION_TYPE_ADD,
            'action_description' => __('request.request_created_detail', [
                'request_name' => RequestType::find($requestTypeId)->name,
                'service_name' => Service::find($serviceId)->name,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
    public function edit(Request $request): View {
        return view('admin.request.edit')
            ->with('request', $request)
            ->with('statusList', $this->getStatusList());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequestRequest $request) {

        $requestId = $request->validated('request_id');
        $status = $request->validated('status');
        $sendEmail = (bool)$request->validated('send_email');
        $adminComment = $request->validated('admin_comment') ?? '';
        $privateNote = $request->validated('private_note') ?? '';

        $requestOriginal = Request::find($requestId);

        $notified = false;
        if ($sendEmail && ($status !== $requestOriginal->status)) {
            $notified = $this->notifyByEmail($status, $adminComment, $requestOriginal->client_id);
        }

        $requestOriginal->status = $status;
        $requestOriginal->admin_comment = $adminComment;
        $requestOriginal->private_note = $privateNote;
        $requestOriginal->save();

        Log::insert([
            'client_id' => $requestOriginal->client_id,
            'user_id' => Auth::user()->id,
            'action_type' => Log::ACTION_TYPE_EDIT,
            'action_description' => __('request.request_status_changed', [
                'request_name' => RequestType::find($requestOriginal->request_type_id)->name,
                'service_name' => Service::find($requestOriginal->service_id)->name,
                'old_status' => __('request.status_' . $requestOriginal->status),
                'new_status' => __('request.status_' . $status),
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $message = $notified ? __('request.request_updated_and_notified') : __('request.request_updated');

        return redirect()->route('requests.index')->with('success', $message);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request) {
        //
    }

    public function getStatusList(): array {
        return [
            Request::STATUS_PENDING => __('request.status_pending'),
            Request::STATUS_UNDER_STUDY => __('request.status_under_study'),
            Request::STATUS_SOLVED => __('request.status_solved'),
            Request::STATUS_DENIED => __('request.status_denied'),
        ];
    }

    private function notifyByEmail(string $status, string $adminComment, int $clientId): bool {

        $adminEmail = Util::getConfigParam('notify_address_request');
        $to = Util::getManagersEmail(Client::find($clientId));

        Mail::to($to)
            ->bcc($adminEmail)
            ->send(new UpdateRequest($status, $adminComment));

        return true;

    }

}

<?php

namespace App\Http\Controllers;

use App\Helpers\Cache;
use App\Helpers\Util;
use App\Http\Requests\StoreRequestRequest;
use App\Http\Requests\UpdateRequestRequest;
use App\Models\Client;
use App\Models\Instance;
use App\Models\Log;
use App\Models\Request;
use Illuminate\Http\Request as LaravelRequest;
use App\Models\RequestType;
use App\Models\Service;
use App\Mail\UpdateRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(LaravelRequest $request): View {
        $query = Request::with('requestType', 'service', 'client', 'user')
            ->orderByRaw("FIELD(status, \"" . Request::STATUS_PENDING . "\") DESC")
            ->orderBy('updated_at', 'desc');

        if ($request->filled('request_type_id')) {
            $query->where('request_type_id', $request->input('request_type_id'));
        }

        if ($request->filled('client_name')) {
            $client_name = $request->input('client_name');

            // Working with client code here, because autocomplete is used by stats as well, which works
            // with client code and not ID...
            $client_code = (!empty($client_name)) ? explode(' - ', $client_name)[1] : null;
            $client_id = Client::where('code', $client_code)->value('id');

            $query->where('client_id', $client_id);
        }

        $requests = $query->paginate(50);

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
        $instance = Instance::where([
            'client_id' => $request->client_id,
            'service_id' => $request->service_id,
        ])->first();

        $instanceUrl = Util::getInstanceUrl($instance);

        return view('admin.request.edit')
            ->with('request', $request)
            ->with('instanceUrl', $instanceUrl)
            ->with('instanceId', $instance->id)
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

        $messages = [];
        $error = '';

        if ($sendEmail && ($status !== $requestOriginal->status)) {
            $emailResult = $this->notifyByEmail($status, $adminComment, $requestOriginal->client_id);
            if (isset($emailResult['success'])) {
                $messages[] = $emailResult['success'];
            }
            if (isset($emailResult['error'])) {
                $error = $emailResult['error'];
            }
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

        $messages[] = __('request.request_updated');
        $messagesString = implode('<br>', $messages);

        return redirect()->route('requests.index')
            ->with('success', $messagesString)
            ->with('error', $error);

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

    public function getStatusColor(string $status): string {
        return match ($status) {
            Request::STATUS_PENDING => 'warning',
            Request::STATUS_UNDER_STUDY => 'info',
            Request::STATUS_SOLVED => 'success',
            Request::STATUS_DENIED => 'danger',
        };
    }

    private function notifyByEmail(string $status, string $adminComment, int $clientId): array {

        $adminEmail = Util::getConfigParam('notify_address_request');
        $to = Util::getManagersEmail(Client::find($clientId));

        try {
            Mail::to($to)
                ->bcc($adminEmail)
                ->send(new UpdateRequest($status, $adminComment));

            $message = __('email.email_sent', ['to' => implode(', ', $to), 'bcc' => $adminEmail]);
            return ['success' => $message];

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

    }

}

<?php

namespace App\Http\Controllers;

use App\Helpers\Util;
use App\Http\Requests\StoreBatchInstanceRequest;
use App\Models\Client;
use App\Models\Instance;
use App\Models\ModelType;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JsonException;

class BatchController extends Controller {
    public function __construct() {
        $this->middleware('auth');
    }

    public function batch(): RedirectResponse {
        return redirect()->route('batch.query');
    }

    public function query(Request $request): View {
        $selector = new SelectorController();
        $viewData = $selector->getSelector($request, 'Moodle');
        $query = $request->session()->get('query');

        return view('admin.batch.query')
            ->with('viewData', $viewData)
            ->with('query', $query)
            ->with('queryTypes', QueryController::getTypeList());
    }

    /**
     * @throws JsonException
     */
    public function operation(Request $request): View {
        $selector = new SelectorController();
        $viewData = $selector->getSelector($request, 'Moodle', false);

        $operationController = new OperationController();
        $operations = $operationController->get_operations_list($viewData['selectedService']);

        $action = (is_array($operations)) ? current($operations) : '';

        $priority = [
            'low' => __('batch.low'),
            'medium' => __('batch.medium'),
            'high' => __('batch.high'),
            'highest' => __('batch.highest'),
        ];

        return view('admin.batch.operation')
            ->with('viewData', $viewData)
            ->with('operations', $operations)
            ->with('action', $action)
            ->with('priority', $priority);
    }

    public function queue(): RedirectResponse {
        return redirect()->route('queue.pending');
    }

    public function instanceCreate(): View {
        $services = Service::where('status', 'active')->get()->toArray();
        $modelTypes = ModelType::get();

        return view('admin.batch.create')
            ->with('services', $services)
            ->with('modelTypes', $modelTypes);
    }

    public function instanceStore(StoreBatchInstanceRequest $request): RedirectResponse {
        $codeAndServer = $request->get('codeAndServer');
        $serviceId = $request->get('serviceId');
        $modelTypeId = $request->get('modelTypeId');
        $util = new Util();
        $messages = [];
        $errors = [];

        $service = Service::where('id', $serviceId)->where('status', 'active')->first();
        $modelType = ModelType::where('id', $modelTypeId)->where('service_id', $serviceId)->first();

        $items = explode(',', $codeAndServer);
        if (empty($items)) {
            return redirect()->back()->withErrors(__('batch.no_instances_to_create'));
        }

        foreach ($items as $item) {
            if (empty($item)) {
                continue;
            }

            [$code, $server] = explode(':', $item);
            $code = trim($code);
            $server = trim($server);

            if ($util->isValidCode($code) === false) {
                $errors[] = __('batch.invalid_code', ['code' => $code]);
                continue;
            }

            $client = Client::where('code', $code)->first();
            if (empty($client)) {
                $errors[] = __('batch.client_not_found', ['code' => $code]);
                continue;
            }

            $instance = Instance::where('client_id', $client->id)
                ->where('service_id', $service->id)
                ->where('status', 'active')
                ->first();

            if (!empty($instance)) {
                $errors[] = __('batch.instance_already_exists', ['code' => $code]);
                continue;
            }

            $instance = new Instance();
            $instance->client_id = $client->id;
            $instance->service_id = $service->id;
            $instance->status = Instance::STATUS_PENDING;
            $instance->db_id = 0;
            $instance->db_host = $server;
            $instance->quota = $service->quota;
            $instance->used_quota = 0;
            $instance->model_type_id = $modelType->id;
            $instance->contact_name = $client->name;
            $instance->observations = __('batch.automatic_signup');
            $instance->requested_at = now();
            $instance->save();

            $instanceController = new InstanceController();
            $newDbId = $instanceController->getNewOrUpdatedDbId($instance, Instance::STATUS_ACTIVE);
            $log = $instanceController->activateInstance($instance, $newDbId, Instance::STATUS_ACTIVE);

            if (isset($log['errors'])) {
                $errors[] = $log['errors'];
                $instance->delete();
                continue;
            }

            $messages[] = __('batch.instance_created', [
                'code' => $client->code,
                'password' => $log['password'],
            ]);
        }

        $messagesString = implode("\n", $messages);

        return redirect()->route('batch.instance.create')
            ->with('success', $messagesString)
            ->withErrors($errors);
    }

}

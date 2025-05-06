<?php

namespace App\Http\Controllers;

use App\Helpers\Util;
use App\Http\Requests\StoreBatchInstanceRequest;
use App\Http\Controllers\OperationController;
use App\Models\Client;
use App\Models\Instance;
use App\Models\ModelType;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $serviceSel = $request->session()->get('serviceSel');

        return view('admin.batch.query')
            ->with('viewData', $viewData)
            ->with('query', $query)
            ->with('serviceSel', $serviceSel)
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
            // $log = $instanceController->activateInstance($instance, $newDbId, Instance::STATUS_ACTIVE);

            // if (isset($log['errors'])) {
            //     $errors[] = $log['errors'];
            //     $instance->delete();
            //     continue;
            // }

            $operationController = new OperationController();

            $form = [
                'action' => 'script_activate_instance',
                'priority' => 'default',
                'params' => [
                    'instance_client_id'     => $instance->client_id,
                    'instance_service_id'    => $instance->service_id,
                    'instance_status'        => $instance->status,
                    'instance_db_id'         => $instance->db_id,
                    'instance_db_host'       => $instance->db_host,
                    'instance_quota'         => $instance->quota,
                    'instance_used_quota'    => $instance->used_quota,
                    'instance_model_type_id' => $instance->model_type_id,
                    'instance_contact_name'  => $instance->contact_name,
                    'instance_observations'  => $instance->observations,
                    // 'instance_requested_at'  => $instance->requested_at,
                    // 'instance_updated_at'    => $instance->updated_at,
                    // 'instance_created_at'    => $instance->created_at,
                    'instance_id'            => $instance->id,
                    'newDbId' => $newDbId,
                ],
                'service_name' => 'agora',
                'instance_id' => $instance->id,
                'instance_name' => $instance->name,
                'instance_dns' => $instance->dns,
            ];

            $operationController->enqueueFromArray($form);

            error_log('Enqueued operation ' . __FILE__ . ' ' . __LINE__);

            $messages[] = __('batch.instance_created', [
                'code' => $client->code,
                'password' => ''//$log['password'],
            ]);

            // Save the password in batch_log table.
            DB::table('batch_logs')->insert([
                'instance_id' => $instance->id,
                'password' => '',//$log['password'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $messagesString = implode('<br />', $messages);

        return redirect()->route('batch.instance.create')
            ->with('success', $messagesString)
            ->withErrors($errors);
    }

    public function instancesList(): View {

        $instanceController = new InstanceController();
        $statusList = $instanceController->getStatusList();

        return view('admin.batch.instances')
            ->with('statusList', $statusList);

    }

    public function getInstancesData(): JsonResponse {

        return datatables()
            ->of(Instance::query())
            ->addColumn('checkbox', function ($instance) {
                return '<input type="checkbox" data-id="' . $instance->id . '">';
            })
            ->rawColumns(['checkbox'])
            ->make(true);

    }

    public function updateStatus(Request $request): JsonResponse {

        $ids = $request->input('ids');
        $status = $request->input('status');

        Instance::whereIn('id', $ids)->update(['status' => $status]);

        return response()->json(['message' => __('status.updated_successfully')]);

    }

}

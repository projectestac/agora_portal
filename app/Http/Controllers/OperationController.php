<?php

namespace App\Http\Controllers;

use App\Helpers\Util;
use App\Jobs\ProcessOperation;
use App\Models\Client;
use App\Models\Instance;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use JsonException;

class OperationController extends Controller {

    public const MOODLE_OPERATIONS_LIST_FILE = 'local/agora/scripts/list.php';
    public const NODES_OPERATIONS_LIST_FILE = 'wp-includes/xtec/scripts/list.php';
    public const MOODLE_OPERATIONS_EXEC_FILE = '/dades/html/moodle2/local/agora/scripts/cli.php';
    public const NODES_OPERATIONS_EXEC_FILE = '/dades/html/wordpress/wp-includes/xtec/scripts/cli.php';

    public static function get_operations_file(string $service): string {
        return match ($service) {
            'Moodle' => self::MOODLE_OPERATIONS_LIST_FILE,
            'Nodes' => self::NODES_OPERATIONS_LIST_FILE,
            default => '',
        };
    }

    public static function get_operations_exec_file(string $service): string {
        return match ($service) {
            'Moodle' => self::MOODLE_OPERATIONS_EXEC_FILE,
            'Nodes' => self::NODES_OPERATIONS_EXEC_FILE,
            default => '',
        };
    }

    /**
     * @throws JsonException
     */
    public function get_operations_list(array $service): array|string {

        // Get the URL of the first instance of the service.
        $instance = Instance::select('clients.dns')
            ->join('clients', 'instances.client_id', '=', 'clients.id')
            ->where('instances.service_id', $service['id'])
            ->where('instances.status', 'active')
            ->orderBy('instances.db_id')
            ->first()
            ->toArray();

        $dns = $instance['dns'];
        $slug = $service['slug'];
        $domain = Util::getAgoraVar(mb_strtolower($service['name']) . '_domain');
        $operationsFile = self::get_operations_file($service['name']);

        $operationsUrl = $domain . '/' . $dns . '/';
        $operationsUrl .= empty($slug) ? '' : $slug . '/';
        $operationsUrl .= $operationsFile;

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $operationsUrl);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
        $actions = curl_exec($curl_handle);
        curl_close($curl_handle);

        try {
            $decodedActions = json_decode($actions, true, 512, JSON_THROW_ON_ERROR);
            if ($decodedActions) {
                return $decodedActions;
            }
        } catch (JsonException) {
            return __('batch.url_has_not_returned_actions', ['url' => $operationsUrl]);
        }

        return __('batch.url_has_not_returned_actions', ['url' => $operationsUrl]);
    }

    /**
     * @throws JsonException
     */
    public function getOperationHtml(Request $request, string $actionName = ''): JsonResponse {

        $serviceId = $request->get('serviceId');
        $service = Service::find($serviceId)->toArray();
        $operations = $this->get_operations_list($service);

        if (!empty($actionName)) {
            // $actionName may contain the name of the operation. Should that happen, the operation is copied from the array.
            $action = array_filter($operations, static function ($element) use ($actionName) {
                return $element['action'] === $actionName;
            });
            $action = current($action);
        }

        if (empty($action)) {
            // $action is empty, so we get the first operation. This is only supposed to happen when the user selects another service.
            $action = current($operations);
        }

        $content = view('admin.batch.operation-selector')
            ->with('operations', $operations)
            ->with('action', $action)
            ->render();

        return response()->json(['html' => $content]);

    }

    public function confirmOperation(Request $request): View {
        $action = $request->input('action');
        $priority = $request->input('priority');
        $serviceId = $request->input('serviceSel');
        $serviceSelector = $request->input('serviceSelector');
        $clientsIds = $request->input('clientsSel');

        $requestParams = $request->all();

        $operationParams = [];
        foreach ($requestParams as $key => $value) {
            if (str_starts_with($key, 'param_')) {
                $operationParams[substr($key, strlen('param_'))] = $value;
            }
        }

        $serviceName = Service::find($serviceId)->name;

        if ($serviceSelector === 'all') {
            $instances = Instance::select('clients.id', 'clients.name', 'clients.dns')
                ->join('clients', 'instances.client_id', '=', 'clients.id')
                ->where('instances.service_id', $serviceId)
                ->where('instances.status', 'active')
                ->orderBy('clients.name')
                ->get()
                ->toArray();
        } else {
            $instances = [];
            if (!empty($clientsIds)) {
                foreach ($clientsIds as $clientId) {
                    $client = Client::find($clientId);
                    $instances[] = [
                        'id' => $clientId,
                        'name' => $client->name,
                        'dns' => $client->dns,
                    ];
                }
            }
        }

        // Save the variables to the session.
        $request->session()->put('batch', [
            'action' => $action,
            'priority' => $priority,
            'params' => $operationParams,
            'service_id' => $serviceId,
            'service_name' => $serviceName,
            'instances' => $instances,
        ]);

        return view('admin.batch.operation-confirm')
            ->with('action', $action)
            ->with('priority', $priority)
            ->with('params', $operationParams)
            ->with('serviceId', $serviceId)
            ->with('serviceName', $serviceName)
            ->with('image', mb_strtolower($serviceName))
            ->with('instances', $instances);

    }

    /**
     * Create a new job ProcessOperation and dispatch it. Recover the data from the session.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function enqueue(Request $request): RedirectResponse {
        $data = $request->session()->get('batch');
        $instances = $data['instances'];

        foreach ($instances as $instance) {
            ProcessOperation::dispatch([
                'action' => $data['action'],
                'priority' => $data['priority'],
                'params' => $data['params'],
                'service_name' => $data['service_name'],
                'instance_id' => $instance['id'],
                'instance_name' => $instance['name'],
                'instance_dns' => $instance['dns'],
            ])
                ->onQueue($data['priority']);
        }

        $request->session()->forget('batch');

        return redirect()->route('operation')
            ->with('success', __('batch.operation_queued'));
    }

    /**
     * Create a new job ProcessOperation and dispatch it. Recover the data from the inputs.
     *
     * @param Request $request
     * @throws JsonException
     * @return RedirectResponse
     */
    public function enqueueFromInputs(Request $request): RedirectResponse {
        $form = $request->all();

        ProcessOperation::dispatch([
            'action' => $form['action'],
            'priority' => $form['priority'],
            'params' => json_decode($form['params'], true, 512, JSON_THROW_ON_ERROR),
            'service_name' => $form['service_name'],
            'instance_id' => $form['instance_id'],
            'instance_name' => $form['instance_name'],
            'instance_dns' => $form['instance_dns'],
        ])
            ->onQueue($form['priority']);

        return redirect()->route('queue.success')
            ->with('success', __('batch.operation_queued'));
    }

    /**
     * Create and dispatch a new ProcessOperation job from a given array.
     *
     * @param array $form
     * @throws JsonException
     * @return RedirectResponse
     */
    public function enqueueFromArray(array $form): RedirectResponse {
        ProcessOperation::dispatch([
            'action' => $form['action'],
            'priority' => $form['priority'],
            'params' => json_decode($form['params'], true, 512, JSON_THROW_ON_ERROR),
            'service_name' => $form['service_name'],
            'instance_id' => $form['instance_id'],
            'instance_name' => $form['instance_name'],
            'instance_dns' => $form['instance_dns'],
        ])
            ->onQueue($form['priority']);

        return redirect()->route('queue.success')
            ->with('success', __('batch.operation_queued'));
    }

}

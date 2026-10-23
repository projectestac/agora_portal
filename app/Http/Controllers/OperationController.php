<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOperation;
use App\Models\Client;
use App\Models\Instance;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\View\View;
use JsonException;

class OperationController extends Controller {

    // Paths relative to $agora['server']['root'].
    public const MOODLE_OPERATIONS_EXEC_FILE = 'html/moodle2/local/agora/scripts/cli.php';
    public const NODES_OPERATIONS_EXEC_FILE = 'html/wordpress/wp-includes/xtec/scripts/cli.php';

    // Maximum time, in seconds, to get the list of operations.
    public const OPERATIONS_LIST_TIMEOUT = 60;

    // Environment variables passed to the script that gets the list of operations.
    public const OPERATIONS_LIST_ENVIRONMENT = ['PATH', 'HOME', 'USER', 'LANG', 'LC_ALL', 'TZ', 'TMPDIR'];

    public static function get_operations_exec_file(string $service): string {

        global $agora;

        $file = match ($service) {
            'Moodle' => self::MOODLE_OPERATIONS_EXEC_FILE,
            'Nodes' => self::NODES_OPERATIONS_EXEC_FILE,
            default => '',
        };

        return empty($file) ? '' : $agora['server']['root'] . $file;
    }

    /**
     * Get the list of operations of the service executing locally its cli.php script with the option --list. On
     * failure, the details are logged and an error message is returned instead of the list.
     */
    public function get_operations_list(array $service): array|string {

        // The script needs an active instance of the service to bootstrap the platform.
        $instance = Instance::select('clients.dns')
            ->join('clients', 'instances.client_id', '=', 'clients.id')
            ->where('instances.service_id', $service['id'])
            ->where('instances.status', 'active')
            ->orderBy('instances.db_id')
            ->first();

        if (is_null($instance)) {
            return __('batch.no_active_instance', ['service' => $service['name']]);
        }

        $command = ['php', self::get_operations_exec_file($service['name']), '--list', '--ccentre=' . $instance->dns];

        // Under php-fpm, the variables of the HTTP request (REMOTE_ADDR, HTTP_HOST...) are part of the environment and
        // would be inherited by the script. Moodle refuses to run CLI scripts when REMOTE_ADDR is set, so all of them
        // are removed except the basic ones. A value of false removes the variable.
        $environment = array_map(
            static fn() => false,
            array_diff_key(getenv(), array_flip(self::OPERATIONS_LIST_ENVIRONMENT))
        );

        // The working directory must be neutral. Otherwise, relative includes in the scripts may load wrong files.
        try {
            $result = Process::path(sys_get_temp_dir())
                ->env($environment)
                ->timeout(self::OPERATIONS_LIST_TIMEOUT)
                ->run($command);
        } catch (ProcessTimedOutException) {
            $detail = __('batch.actions_timeout', ['seconds' => self::OPERATIONS_LIST_TIMEOUT]);
            return $this->operations_list_error($command, $detail);
        }

        $output = $result->output();

        if (!$result->successful()) {
            $detail = __('batch.actions_exec_error', ['code' => $result->exitCode()]);
            return $this->operations_list_error($command, $detail, $result->exitCode(), $output, $result->errorOutput());
        }

        try {
            $decodedActions = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $detail = __('batch.actions_invalid_json', ['error' => $e->getMessage()]);
            return $this->operations_list_error($command, $detail, $result->exitCode(), $output, $result->errorOutput());
        }

        if (empty($decodedActions) || !is_array($decodedActions)) {
            $detail = __('batch.actions_empty_list');
            return $this->operations_list_error($command, $detail, $result->exitCode(), $output, $result->errorOutput());
        }

        return $decodedActions;
    }

    /**
     * Log the details of a failed execution of the operations list and build the message to show to the user.
     */
    private function operations_list_error(array $command, string $detail, ?int $exitCode = null, string $output = '',
                                           string $errorOutput = ''): string {
        $commandLine = implode(' ', $command);

        Log::error('Could not retrieve the operations list', [
            'command' => $commandLine,
            'exit_code' => $exitCode,
            'output' => mb_substr($output, 0, 500),
            'error_output' => mb_substr($errorOutput, 0, 500),
        ]);

        return __('batch.command_has_not_returned_actions', ['command' => $commandLine, 'detail' => $detail]);
    }

    public function getOperationHtml(Request $request, string $actionName = ''): JsonResponse {

        $serviceId = $request->get('serviceId');
        $service = Service::find($serviceId)->toArray();
        $operations = $this->get_operations_list($service);

        // The list could not be retrieved. Show the reason instead of the selector.
        if (!is_array($operations)) {
            return response()->json(['html' => '<div class="alert alert-danger" role="alert">' . e($operations) . '</div>']);
        }

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

}

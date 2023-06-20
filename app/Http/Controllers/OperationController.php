<?php

namespace App\Http\Controllers;

use App\Helpers\Util;
use App\Models\Instance;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JsonException;

class OperationController extends Controller {
    public function get_operations_file(string $service): string {
        return match ($service) {
            'Moodle' => 'local/agora/scripts/list.php',
            'Nodes' => 'wp-includes/xtec/scripts/list.php',
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
            ->first()
            ->toArray();

        $dns = $instance['dns'];
        $slug = $service['slug'];
        $domain = Util::getAgoraVar(mb_strtolower($service['name']) . '_domain');
        $operationsFile = $this->get_operations_file($service['name']);

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

        $decodedActions = json_decode($actions, true, 512, JSON_THROW_ON_ERROR);
        if ($decodedActions) {
            return $decodedActions;
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

    public function store(Request $request): JsonResponse {
        $request->validate([
            'serviceId' => 'required|integer',
            'action' => 'required|string',
            'priority' => 'required|integer',
            'params' => 'nullable|string',
        ]);

        $serviceId = $request->get('serviceId');
        $action = $request->get('action');
        $priority = $request->get('priority');
        $params = $request->get('params');

        $service = Service::find($serviceId)->toArray();
        $operations = $this->get_operations_list($service);

        $action = array_filter($operations, static function ($element) use ($action) {
            return $element['action'] === $action;
        });
        $action = current($action);

        $operation = [
            'serviceId' => $serviceId,
            'action' => $action['action'],
            'priority' => $priority,
            'params' => $params,
        ];

        $request->session()->push('operations', $operation);

        return response()->json(['success' => true]);
    }

}

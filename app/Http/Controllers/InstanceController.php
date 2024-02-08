<?php

namespace App\Http\Controllers;

use App\Helpers\Access;
use App\Helpers\Cache;
use App\Helpers\Util;
use App\Http\Requests\StoreInstanceRequest;
use App\Http\Requests\UpdateInstanceRequest;
use App\Jobs\ProcessOperation;
use App\Mail\UpdateInstance;
use App\Models\Client;
use App\Models\Instance;
use App\Models\Log;
use App\Models\ModelType;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;
use Throwable;
use Yajra\DataTables\Facades\DataTables;
use ZipArchive;

class InstanceController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(): View {
        return view('admin.instance.index');
    }

    public function create(Request $request): View {

        $service_id = $request->input('service_id');
        $service = Service::find($service_id)->toArray();

        if (!(Access::isAdmin(Auth::user()) || Access::isManager(Auth::user()))) {
            return view('myagora.instance_create')
                ->with('service', $service)
                ->with('error', __('instance.only_managers_can_create_instances'));
        }

        $models = ModelType::where('service_id', $service_id)->get()->toArray();
        $username = Auth::user()->name;
        $currentClient = Cache::getCurrentClient($request);

        return view('myagora.instance_create')
            ->with('service', $service)
            ->with('models', $models)
            ->with('username', $username)
            ->with('client_id', $currentClient['id']);

    }

    public function store(StoreInstanceRequest $request): RedirectResponse {
        $clientId = $request->input('client_id');
        $serviceId = $request->input('service_id');
        $quota = Service::find($serviceId)->quota;
        $modelTypeId = $request->input('model_type_id');
        $contactProfile = $request->input('contact_profile');

        $instance = new Instance([
            'client_id' => $clientId,
            'service_id' => $serviceId,
            'status' => 'pending',
            'db_id' => 0,
            'quota' => $quota,
            'model_type_id' => $modelTypeId,
            'contact_profile' => $contactProfile,
        ]);
        $instance->save();

        Log::insert([
            'client_id' => $instance->client_id,
            'user_id' => Auth::user()->id,
            'action_type' => Log::ACTION_TYPE_ADD,
            'action_description' => __('instance.requested_instance', [
                'user' => Auth::user()->name,
                'service' => Service::find($instance->service_id)->name,
                'client' => Client::find($instance->client_id)->name,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('myagora.instances');
    }

    public function edit(Instance $instance): View {
        return view('admin.instance.edit')
            ->with('instance', $instance)
            ->with('statusList', $this->getStatusList())
            ->with('modelTypeList', $this->getModelTypeList($instance->service_id));
    }

    /**
     * The instance update may mean a simple change in some of the record fields of that instance or may imply a status change.
     * In case of an activation, the database must be created and populated and, also, the data dir must be created and populated.
     * If the instance has a database id, it means that there is a database and a data dir in the system for that instance.
     *
     * @param Request $request
     * @param Instance $instance
     * @return RedirectResponse
     */
    public function update(UpdateInstanceRequest $request, Instance $instance): RedirectResponse {
        $statusFinal = $request->validated('status');
        $sendEmail = (bool)$request->validated('send_email');

        $instance->model_type_id = $request->validated('model_type_id');
        $instance->db_host = $request->validated('db_host') ?? '';
        $instance->quota = $request->validated('quota') * 1024 * 1024 * 1024; // GB to Bytes
        $instance->observations = $request->validated('observations');
        $instance->annotations = $request->validated('annotations');

        try {
            $instance->save();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $messages = [];
        $error = '';

        // If the status has changed, is possible that the db_id needs to be updated. This call gets what database id should be
        // used according to the status change.
        if ($statusFinal !== $instance->status) {
            $newDbId = $this->getNewOrUpdatedDbId($instance, $statusFinal);
        } else {
            // When the status is the same, there is nothing else to do.
            return redirect()->back()->with('success', __('instance.instance_updated'));
        }

        // When the db_id goes from 0 to a new value, it is an activation, which means that the database and the data dir need
        // to be created and populated.
        if ($instance->db_id === 0 && $newDbId > 0) {
            $log = $this->activateInstance($instance, $newDbId, $statusFinal);
            if (isset($log['errors'])) {
                return redirect()->route('instances.index')->with('error', $log['errors']);
            }
            $messages = $log['messages'];

            Log::insert([
                'client_id' => $instance->client->id,
                'user_id' => Auth::user()->id,
                'action_type' => Log::ACTION_TYPE_EDIT,
                'action_description' => __('instance.actived_instance', [
                    'service' => $instance->service->name,
                    'client' => $instance->client->name,
                    'password' => $log['password'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            if ($newDbId === 0) {
                $instance->db_id = 0;
            }
            $oldStatus = $instance->status;
            $instance->status = $statusFinal;
            $instance->save();

            Log::insert([
                'client_id' => $instance->client_id,
                'user_id' => Auth::user()->id,
                'action_type' => Log::ACTION_TYPE_EDIT,
                'action_description' => __('instance.status_changed', [
                    'old_status' => $oldStatus,
                    'new_status' => $instance->status,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Email the client. If it is an activation, send the password and welcome information.
        if ($sendEmail) {
            $emailResult = $this->notifyByEmail($instance, $log['password'] ?? '');
            if (isset($emailResult['success'])) {
                $messages[] = $emailResult['success'];
            }
            if (isset($emailResult['error'])) {
                $error = $emailResult['error'];
            }
        }

        $messages[] = __('instance.instance_updated');
        $messagesString = implode('<br>', $messages);

        return redirect()->route('instances.index')
            ->with('success', $messagesString)
            ->with('error', $error);
    }

    public function getStatusColor(string $status): string {
        return match ($status) {
            Instance::STATUS_PENDING => 'warning',
            Instance::STATUS_ACTIVE => 'success',
            Instance::STATUS_INACTIVE => 'primary',
            Instance::STATUS_DENIED => 'danger',
            Instance::STATUS_WITHDRAWN => 'dark',
            Instance::STATUS_BLOCKED => 'danger',
            default => 'primary',
        };
    }

    public function getStatusList(): array {
        return [
            Instance::STATUS_PENDING => __('instance.status_pending'),
            Instance::STATUS_ACTIVE => __('instance.status_active'),
            Instance::STATUS_INACTIVE => __('instance.status_inactive'),
            Instance::STATUS_DENIED => __('instance.status_denied'),
            Instance::STATUS_WITHDRAWN => __('instance.status_withdrawn'),
            Instance::STATUS_BLOCKED => __('instance.status_blocked'),
        ];
    }

    public function getModelTypeList(int $serviceId = 0): array {

        if ($serviceId) {
            $modelTypes = ModelType::select('id', 'description')
                ->where('service_id', $serviceId)
                ->get();
        } else {
            $modelTypes = ModelType::select('id', 'description')->get();
        }

        $modelTypeList = [];

        foreach ($modelTypes as $modelType) {
            $modelTypeList[$modelType->id] = $modelType->description;
        }

        return $modelTypeList;
    }

    public function getInstances(Request $request): JsonResponse {

        $search = $request->validate(['search.value' => 'string|max:50|nullable']);
        $searchValue = $search['search']['value'] ?? '';

        $columns = $request->input('columns');
        $order = $request->input('order')[0];
        $orderColumn = 'instances.' . $columns[$order['column']]['data'] ?? 'instances.updated_at';
        $orderDirection = $order['dir'] ?? 'desc';

        if ($orderColumn === 'instances.client_name') {
            $orderColumn = 'clients.name';
        }
        if ($orderColumn === 'instances.type') {
            $orderColumn = 'client_types.name';
        }
        if ($orderColumn === 'instances.location') {
            $orderColumn = 'clients.city';
        }
        if ($orderColumn === 'instances.quota') {
            $orderColumn = 'instances.used_quota';
        }
        if ($orderColumn === 'dates') {
            $orderColumn = 'instances.updated_at';
        }

        $instances = Instance::select(['instances.*', 'clients.name as client_name', 'client_types.name as type'])
            ->orderBy($orderColumn, $orderDirection)
            ->join('clients', 'instances.client_id', '=', 'clients.id')
            ->join('locations', 'clients.location_id', '=', 'locations.id')
            ->join('client_types', 'clients.type_id', '=', 'client_types.id')
            ->join('services', 'instances.service_id', '=', 'services.id');

        if (!empty($searchValue)) {
            $instances = $instances->where('clients.name', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('clients.code', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('clients.dns', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('clients.old_dns', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('clients.city', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('locations.name', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('client_types.name', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('services.name', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('instances.status', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('instances.db_id', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('instances.observations', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('instances.annotations', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('instances.updated_at', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('instances.created_at', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('instances.requested_at', 'LIKE', '%' . $searchValue . '%');
        }

        return DataTables::make($instances)
            ->rawColumns(['id'])
            ->addColumn('client_name', function ($instance) {
                return new HtmlString('<a href="' . route('myagora.instances', ['code' => $instance->client->code]) . '">' .
                    $instance->client->name . '</a><br/>' . $instance->client->dns . ' - ' . $instance->client->code);
            })
            ->addColumn('type', function ($instance) {
                return new HtmlString($instance->client->type->name . '<br/>' .
                    $instance->modelType->description);
            })
            ->addColumn('status', function ($instance) {
                $statusColor = $this->getStatusColor($instance->status);
                return new HtmlString("<span class=\"btn btn-$statusColor\">$instance->status</span>");
            })
            ->addColumn('service_id', function ($instance) {
                $url = Util::getInstanceUrl($instance);
                return new HtmlString(view('admin.client.service', [
                    'url' => $url,
                    'serviceName' => $instance->service->name,
                    'clientName' => $instance->client->name,
                ])->render());
            })
            ->addColumn('location', function ($instance) {
                return new HtmlString($instance->client->city . '<br/>(<em>' . $instance->client->location->name . '</em>)');
            })
            ->addColumn('quota', function ($instance) {
                return new HtmlString(Util::getFormattedDiskUsage($instance->used_quota, $instance->quota));
            })
            ->addColumn('updated_at', function ($instance) {
                return new HtmlString('<strong>E:</strong> ' . $instance->updated_at->format('d/m/Y') . '<br/>' .
                    '<strong>C:</strong> ' . $instance->created_at->format('d/m/Y') . '<br/>' .
                    '<strong>S:</strong> ' . \Carbon\Carbon::parse($instance->requested_at)->format('d/m/Y'));
            })
            ->addColumn('actions', static function ($instance) {
                return view('admin.instance.action', ['instance' => $instance]);
            })
            ->make();

    }

    public function activateInstance(Instance $instance, int $newDbId, mixed $statusFinal): array|RedirectResponse {
        // First of all, ensure that the required files are available.
        $checkFiles = $this->checkFiles($instance);
        if (!empty($checkFiles['errors'])) {
            return ['error' => $checkFiles['errors']];
        }

        $messages = [];
        if (isset($checkFiles['success'])) {
            $messages[] = __('instance.dump_files_found');
        }

        $dumpDatabase = $this->dumpDatabase($instance, $newDbId, $checkFiles['success']['dbFile']);

        if (!empty($dumpDatabase['errors'])) {
            return ['errors' => $dumpDatabase['errors']];
        }

        if (isset($dumpDatabase['success'])) {
            $messages[] = __('instance.dump_success');
        }

        $unzipFiles = $this->unzipFiles($instance, $newDbId, $checkFiles['success']['dataFile']);

        if (!empty($unzipFiles['errors'])) {
            return ['errors' => $unzipFiles['errors']];
        }

        if (isset($unzipFiles['success'])) {
            $messages[] = __('instance.unzip_success_short');
        }

        $password = Util::createRandomPass();
        $programOperation = $this->programOperationEnable($instance, $newDbId, $password);

        if (!empty($programOperation['errors'])) {
            return ['errors' => $programOperation['errors']];
        }

        if (isset($programOperation['success'])) {
            $messages[] = $programOperation['success'];
        }

        $instance->status = $statusFinal;
        $instance->db_id = $newDbId;
        $instance->save();

        return [
            'messages' => $messages,
            'password' => $password,
        ];
    }

    /**
     * The change of status may imply the creation or deletion of the database. These operations are not developed yet, but the
     * db_id field must be updated in the instances table when needed. The changes of status that imply a db_id update are:
     * - pending -> active (assign an available db)
     * - active -> withdrawn (remove db assignation)
     *
     * @param Instance $instance
     * @param string $statusFinal
     * @return int
     */
    public function getNewOrUpdatedDbId(Instance $instance, string $statusFinal): int {

        if (($instance->status === Instance::STATUS_PENDING || $instance->status === Instance::STATUS_WITHDRAWN)
            && $statusFinal === Instance::STATUS_ACTIVE && $instance->db_id === 0) {
            return $this->getNewDbId($instance->service_id);
        }

        if ($instance->db_id !== 0 && $statusFinal === Instance::STATUS_WITHDRAWN) {
            return 0;
        }

        return $instance->db_id;
    }

    private function getNewDbId(mixed $service_id): int {
        $dataBaseIds = Instance::select('db_id')
            ->where('service_id', $service_id)
            ->where('db_id', '!=', 0)
            ->orderBy('db_id', 'asc')
            ->get()
            ->toArray();

        $firstID = Util::getConfigParam('first_db_id');
        $firstID = (empty($firstID)) ? 1 : (int)$firstID;

        $i = $firstID;
        $free = 0;

        if (empty($dataBaseIds)) {
            return $firstID;
        }

        // First, look for a free database (a gap in the list).
        foreach ($dataBaseIds as $item) {
            $dbId = (int)$item['db_id'];

            // Discard activeId's that are lower than firstID.
            if ($dbId >= $firstID) {
                if ($dbId !== $i) {
                    $free = $i;
                    break;
                }
                $i++;
            }
        }

        // No luck, so let's try the following ID
        if (!$free) {
            $free = $i;
        }

        return $free;
    }

    private function checkFiles(Instance $instance): array {

        $portalData = Util::getAgoraVar('portaldata') . 'data/';
        $serviceName = mb_strtolower($instance->service->name);
        $shortCode = $instance->modelType->short_code;

        $dbFile = $portalData . $serviceName . '/master' . $serviceName . $shortCode . '.sql';
        $dataFile = $portalData . $serviceName . '/master' . $serviceName . $shortCode . '.zip';

        $errors = [];

        if (!file_exists($dbFile)) {
            $errors[] = __('instance.file_not_found', ['name' => $dbFile]);
        }

        if (!file_exists($dataFile)) {
            $errors[] = __('instance.file_not_found', ['name' => $dataFile]);
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return ['success' => [
            'dbFile' => $dbFile,
            'dataFile' => $dataFile,
        ]];
    }

    private function dumpDatabase(Instance $instance, int $instanceId, string $dbFile): array {

        $serviceName = $instance->service->name;
        $serviceNameLower = mb_strtolower($serviceName);

        switch ($serviceName) {
            case 'Moodle':
                $serviceKey = 'moodle2';
                $userPassword = config('app.agora.moodle2.userpwd');
                break;
            case 'Nodes':
                $serviceKey = 'nodes';
                $userName = config("app.agora.nodes.username");
                $userPassword = config("app.agora.nodes.userpwd");
                break;
        }

        $dbName = config("app.agora.$serviceKey.userprefix") . $instanceId;
        $userName = ($serviceName === 'Nodes') ? $userName : $dbName;

        config([
            "database.connections.$serviceNameLower.host" => $instance->db_host,
            "database.connections.$serviceNameLower.database" => $dbName,
            "database.connections.$serviceNameLower.username" => $userName,
            "database.connections.$serviceNameLower.password" => $userPassword,
        ]);

        // Force the change of the database connection.
        DB::connection($serviceNameLower)->reconnect();

        // Test the database connection. If the database is missing and the parameter 'nodes_create_db' is checked,
        // the database will be created.
        $test = $this->testConnectionAndCreateDb($serviceNameLower, $dbName, $userName, $instance);
        if (!empty($test['errors'])) {
            return ['errors' => $test['errors']];
        }

        // Temporary variable, used to store current query.
        $currentSQL = '';

        // Read in entire file.
        $lines = file($dbFile);

        // Loop through each line.
        foreach ($lines as $line) {
            // Skip it if it's a comment or an empty line.
            if ($line === '' || $line === "\n" || str_starts_with($line, '--') || str_starts_with($line, '/*!') || str_starts_with($line, '#')) {
                continue;
            }

            // Add this line to the current segment.
            $currentSQL .= $line;

            // Detection of sentences.
            $executeQuery = false;

            if ($serviceName === 'Nodes') {
                // If it has a semicolon at the end, it's the end of the query.
                $executeQuery = str_ends_with(trim($line), ';');
            }

            if ($serviceName === 'Moodle') {
                // If is not an insert, and it has a semicolon at the end, it's the end of the query.
                // If it is an insert, the end of the query is ');', but there is an exception for '});', which is the end
                // of line in H5P definitions.
                $executeQuery = ((!str_starts_with($currentSQL, 'INSERT')) && (str_ends_with(trim($line), ';')))
                    || ((str_starts_with($currentSQL, 'INSERT')) && (str_ends_with(trim($line), ');'))
                        && (!str_ends_with(trim($line), '});')));
            }

            // Note: this script is not able to create the database. It must previously exist.
            if ($executeQuery) {
                try {
                    DB::connection($serviceNameLower)->statement($currentSQL);
                } catch (Throwable $e) {
                    return ['error' => __('instance.query_failed', ['query' => $currentSQL, 'error' => $e->getMessage()])];
                }
                // Reset temp variable to empty
                $currentSQL = '';
            }
        }

        return ['success' => __('instance.dump_success')];
    }

    private function unzipFiles(Instance $instance, int $instanceId, string $dataFile): array {

        switch ($instance->service->name) {
            case 'Moodle':
                $serviceKey = 'moodle2';
                $dataDir = Util::getAgoraVar('moodledata');
                break;
            case 'Nodes':
                $serviceKey = 'nodes';
                $dataDir = Util::getAgoraVar('nodesdata');
                break;
            default:
                return ['error' => __('service.incorrect_service')];
        }

        $messages = [];

        // Directory for the new site files
        $dbName = config("app.agora.$serviceKey.userprefix") . $instanceId;
        $targetDir = $dataDir . $dbName . '/';

        // If the directory doesn't exist, create it.
        if (!is_dir($targetDir)) {
            if (mkdir($targetDir, 0777, true) || is_dir($targetDir)) {
                $messages[] = __('instances.dir_created', ['dir' => $targetDir]);
            } else {
                return ['error' => __('instances.dir_not_created', ['dir' => $targetDir])];
            }
        }

        // Extract the files.
        $zip = new ZipArchive();

        $resource = $zip->open($dataFile);
        if (!$resource) {
            return ['error' => __('instances.file_not_opened', ['file' => $dataFile])];
        }

        // Try to extract the file.
        if (!$zip->extractTo($targetDir)) {
            $zip->close();
            return ['error' => __('instances.unzip_error', ['file' => $dataFile, 'dir' => $targetDir])];
        }

        $zip->close();

        $messages[] = __('instances.unzip_success', ['file' => $dataFile, 'dir' => $targetDir]);

        return ['success' => $messages];

    }

    private function programOperationEnable(Instance $instance, int $instanceId, string $password): array {

        ProcessOperation::dispatch([
            'action' => 'script_enable_service',
            'priority' => 'high',
            'params' => [
                'password' => md5($password),
                'xtecadminPassword' => Util::getConfigParam('xtecadmin_hash'),
                'clientName' => $instance->client->name,
                'clientCode' => $instance->client->code,
                'clientAddress' => $instance->client->address,
                'clientCity' => $instance->client->city,
                'clientDNS' => $instance->client->dns,
                'clientPC' => $instance->client->postal_code,
                'origin_url' => $instance->modelType->url,
                'origin_bd' => $instance->modelType->db,
            ],
            'service_id' => $instance->service->id,
            'service_name' => $instance->service->name,
            'instance_id' => $instanceId,
            'instance_name' => $instance->client->name,
            'instance_dns' => $instance->client->dns,
        ]);

        return ['success' => __('instance.operation_programmed')];
    }

    private function testConnectionAndCreateDb(string $serviceNameLower, string $dbName, string $userName, Instance $instance): array {

        try {
            $pdo = DB::connection($serviceNameLower)->getPdo();
        } catch (\Exception $e) {
            // Check if automatic creation of databases is allowed.
            if ($serviceNameLower === 'nodes' && !Util::getConfigParam('nodes_create_db')) {
                return [
                    'errors' => __('instance.db_creation_failed', [
                        'error' => $e->getMessage(),
                        'host' => $instance->db_host,
                        'db' => $dbName,
                        'user' => $userName,
                    ])];
            }

            if (str_contains($e->getMessage(), 'Unknown database')) {
                // The database doesn't exist, so we try to create it.
                try {
                    /*
                     * To execute a query, the database configured must exist. As we are creating a database, it doesn't exist
                     * yet, so it can't be configured in the connection. For that reason, we configure the connection with no
                     * selected database, execute the CREATE DATABASE query and, finally, select the created database.
                     */
                    config(["database.connections.$serviceNameLower.database" => '']);
                    DB::connection($serviceNameLower)->reconnect();

                    $result = DB::connection($serviceNameLower)
                        ->statement("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                    config(["database.connections.$serviceNameLower.database" => $dbName]);
                    DB::connection($serviceNameLower)->reconnect();

                    if ($result) {
                        $pdo = DB::connection($serviceNameLower)->getPdo();
                    }
                } catch (\Exception $e) {
                    return [
                        'errors' => __('instance.db_connection_failed_db_not_created', [
                            'error' => $e->getMessage(),
                            'host' => $instance->db_host,
                            'db' => $dbName,
                            'user' => $userName,
                        ])];
                }
            } else {
                return [
                    'errors' => __('instance.db_connection_failed', [
                        'error' => $e->getMessage(),
                        'host' => $instance->db_host,
                        'db' => $dbName,
                        'user' => $userName,
                    ])];
            }
        }

        return ['success' => true];
    }

    private function notifyByEmail(Instance $instance, string $password = ''): array {

        $adminEmail = Util::getConfigParam('notify_address_user_cco');
        $to = Util::getManagersEmail(Client::find($instance->client_id));

        try {
            Mail::to($to)
                ->bcc($adminEmail)
                ->send(new UpdateInstance($instance, $password));

            $message = __('email.email_sent', ['to' => implode(', ', $to), 'bcc' => $adminEmail]);
            return ['success' => $message];

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

    }

    public function updateQuotas(): RedirectResponse {

        // Get the list of services and execute the function to update the quota for each one.
        $serviceNames = Service::get()->where('status', 'active')->pluck('name')->toArray();

        $result = [];

        foreach ($serviceNames as $serviceName) {
            $result[] = $this->updateQuotaByService($serviceName);
        }

        return redirect()
            ->back()
            ->with('message', implode('<br>', $result));
    }

    private function updateQuotaByService($serviceName): string {

        $quotasFile = Util::getAgoraVar(mb_strtolower($serviceName) . '_quotas_file');
        $serviceId = Service::where('name', $serviceName)->first()->id;

        try {
            $fileContent = file_get_contents($quotasFile);
        } catch (\Exception $e) {
            return $serviceName . ': ' . $e->getMessage();
        }

        $lines = explode("\n", $fileContent);

        foreach ($lines as $line) {
            if (preg_match('/(\d+)\s+usu(\d+)/', $line, $matches)) {
                Instance::where('client_id', (int)$matches[2])
                    ->where('service_id', $serviceId)
                    ->update(['used_quota' => (int)$matches[1]]);
            }
        }

        return __('instance.update_quotas_service_ended', ['service' => $serviceName]);
    }

}

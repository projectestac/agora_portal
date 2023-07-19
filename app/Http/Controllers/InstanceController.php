<?php

namespace App\Http\Controllers;

use App\Helpers\Cache;
use App\Helpers\Util;
use App\Models\Instance;
use App\Models\ModelType;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use ZipArchive;

class InstanceController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(): View {
        $instances = Instance::select('instances.*', 'clients.name as client_name', 'services.name as service_name')
            ->with('modelType')
            ->join('clients', 'instances.client_id', '=', 'clients.id')
            ->join('services', 'instances.service_id', '=', 'services.id')
            ->paginate(10);

        return view('admin.instance.index')->with('instances', $instances);
    }

    public function create(Request $request): View {
        $service_id = $request->input('service_id');

        $service = Service::find($service_id)->toArray();
        $models = ModelType::where('service_id', $service_id)->get()->toArray();
        $username = Auth::user()->name;
        $currentClient = Cache::getCurrentClient($request);

        return view('myagora.instance_create')
            ->with('service', $service)
            ->with('models', $models)
            ->with('username', $username)
            ->with('client_id', $currentClient['id']);
    }

    public function store(Request $request): RedirectResponse {
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

        return redirect()->route('myagora.instances');
    }

    public function edit(Instance $instance): View {
        dump(config('app.agora'));
        return view('admin.instance.edit')
            ->with('instance', $instance)
            ->with('statusList', $this->getStatusList());
    }

    public function update(Request $request, Instance $instance): RedirectResponse {

        dump($instance);
        $statusInitial = $instance->status;
        $statusFinal = $request->input('status');

        $instance->status = $statusFinal;
        $instance->db_host = $request->input('db_host');
        $instance->quota = $request->input('quota') * 1024 * 1024 * 1024; // GB to Bytes
        $instance->observations = $request->input('observations');
        $instance->annotations = $request->input('annotations');
        $instance->save();

        $changes = $instance->getChanges();
        $newDbId = 0;
        $error = [];

        // If the status has changed, is possible that the db_id needs to be updated.
        if (array_key_exists('status', $changes)) {
            $newDbId = $this->getNewOrUpdatedDbId($instance, $statusInitial);
        }

        // When the db_id goes from 0 to a new value, it means that the database needs to be populated and the files created.
        if ($newDbId !== 0 && $instance->db_id > 0) {
            // First of all, ensure that the required files are available.
            $checkFiles = $this->checkFiles($instance);
            if (!empty($checkFiles['errors'])) {
                return redirect()
                    ->route('instances.index')
                    ->with('error', $checkFiles['errors']);
            }

            $dumpDatabase = $this->dumpDatabase($instance, $checkFiles['success']['dbFile']);
            if (!empty($dumpDatabase['errors'])) {
                return redirect()
                    ->route('instances.index')
                    ->with('error', $dumpDatabase['errors']);
            }

            $unzipFiles = $this->unzipFiles($instance, $checkFiles['success']['dataFile']);
            if (!empty($unzipFiles['errors'])) {
                return redirect()
                    ->route('instances.index')
                    ->with('error', $unzipFiles['errors']);
            }

            if (!$this->programOperationEnable($instance)) {
                $error[] = __('instance.error_program_operations');
            }
        }

        dd($instance->getChanges());

        if (!empty($error)) {
            return redirect()
                ->route('instances.index')
                ->with('error', $error);
        }

        return redirect()
            ->route('instances.index')
            ->with('success', __('instance.instance_updated'));
    }

    public function getStatusColor(string $status): string {
        return match ($status) {
            Instance::STATUS_PENDING => 'warning',
            Instance::STATUS_ACTIVE => 'success',
            Instance::STATUS_INACTIVE => 'secondary',
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

    /**
     * The change of status may imply the creation or deletion of the database. These operations are not developed yet, but the
     * db_id field must be updated in the instances table when needed. The changes of status that imply a db_id update are:
     * - pending -> active (assign an available db)
     * - active -> withdrawn (remove db assignation)
     *
     * @param Instance $instance
     * @param string $statusInitial
     * @return int
     */
    private function getNewOrUpdatedDbId(Instance $instance, string $statusInitial): int {

        if ($statusInitial === Instance::STATUS_PENDING && $instance->status === Instance::STATUS_ACTIVE && $instance->db_id === 0) {
            return $this->getNewDbId($instance->service_id);
        }

        if ($instance->db_id !== 0 && $instance->status === Instance::STATUS_WITHDRAWN) {
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

        // TODO: Get this value from site config.
        $firstID = 1;

        $i = $firstID;
        $free = 0;

        if (empty($dataBaseIds)) {
            return $firstID;
        }

        // First, look for a free database (a gap in the list).
        foreach ($dataBaseIds as $activeId) {
            $activeId = (int)$activeId;

            // Discard activeId's that are lower than firstID.
            if ($activeId >= $firstID) {
                if ($activeId !== $i) {
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
        $serviceName = mb_strtolower($instance->service()->name);
        $shortCode = $instance->modelType()->short_code;

        $dbFile = $portalData . '/moodle/master' . $serviceName . $shortCode . '.sql';
        $dataFile = $portalData . '/moodle/master' . $serviceName . $shortCode . '.zip';

        $errors = [];

        if (!file_exists($dbFile)) {
            $errors[] = __('instance.error_dbfile_not_found', $dbFile);
        }

        if (!file_exists($dataFile)) {
            $errors[] = __('instance.error_dbfile_not_found', $dataFile);
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return ['success' => [
            'dbFile' => $dbFile,
            'dataFile' => $dataFile,
        ]];
    }

    private function dumpDatabase(Instance $instance, string $dbFile): array {

        $serviceName = mb_strtolower($instance->service()->name);
        $serviceNameLower = Str::lower($serviceName);

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

        $dbName = config("app.agora.$serviceKey.userprefix") . $instance->db_id;
        $userName = ($serviceName === 'Nodes') ? $userName : $dbName;

        config(["database.connections.$serviceKey.host" => $instance['db_host']]);
        config(["database.connections.$serviceKey.database" => $dbName]);
        config(["database.connections.$serviceKey.username" => $userName]);
        config(["database.connections.$serviceKey.userpwd" => $userPassword]);

        DB::connection($serviceKey)->reconnect();

        // Temporary variable, used to store current query.
        $currentSQL = '';

        // Read in entire file.
        $lines = file($dbFile);

        // Loop through each line.
        foreach ($lines as $line) {
            // Skip it if it's a comment or an empty line.
            if ($line === '' || str_starts_with($line, '--') || str_starts_with($line, '/*!') || str_starts_with($line, '#')) {
                continue;
            }

            // Add this line to the current segment.
            $currentSQL .= $line;

            // TODO: La comparació és diferent al Nodes!!!

            // Detection of sentences: If is not an insert, and it has a semicolon at the end, it's the end of the query.
            // If it is an insert, the end of the query is ');', but there is an exception for '});', which is the end
            // of line in H5P definitions.
            // Note: this script is not able to create the database. It must previously exist.
            if (((!str_starts_with($currentSQL, 'INSERT')) && (str_ends_with(trim($line), ';')))
                || ((str_starts_with($currentSQL, 'INSERT')) && (str_ends_with(trim($line), ');')) && (!str_ends_with(trim($line), '});')))) {
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

    private function unzipFiles(Instance $instance, string $dataFile): array {

        // Directory for the new site files
        $dbName = config("app.agora.$serviceKey.userprefix") . $instance->db_id;
        $targetDir = Util::getAgoraVar('moodledata') . $dbName . '/';

        // If the directory doesn't exist, create it.
        if (!file_exists($targetDir)) {
            $newDir = mkdir($targetDir, 0777, true);
            if ($newDir) {
                LogUtil::registerStatus(__f("S'ha creat el directori %s", $targetDir));
            } else {
                LogUtil::registerError(__f("El directori %s no existia i no s'ha pogut crear", $targetDir));
                return false;
            }
        }

        // Uncompress the files
        $zip = new ZipArchive();

        $resource = $zip->open($dataFile);
        if (!$resource) {
            LogUtil::registerError(__f("No s'ha pogut obrir el fitxer de base de %s", $dataFile));
            return false;
        }

        // Try to extract the file
        if (!$zip->extractTo($targetDir)) {
            LogUtil::registerError(__f("S'ha produït un error en descomprimir el fitxer %s al directori %s", array($dataFile, $targetDir)));
            $zip->close();
            return false;
        }

        $zip->close();

        return [];
    }

    private function programOperationEnable(Instance $instance) {
        return true;
    }

}

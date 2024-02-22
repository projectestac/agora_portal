<?php

namespace App\Http\Controllers;

use App\Helpers\Access;
use App\Helpers\Cache;
use App\Helpers\Quota;
use App\Helpers\Util;
use App\Jobs\ProcessOperation;
use App\Models\Client;
use App\Models\Instance;
use App\Models\Log;
use App\Models\Manager;
use App\Models\RequestType;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MyAgoraController extends Controller {

    public function myagora(): RedirectResponse {
        return redirect()->route('myagora.instances');
    }

    public function instances(Request $request): View {

        $currentClient = $this->getCurrentClient($request);

        if (empty($currentClient)) {
            return view('myagora.instance')->with('instances', []);
        }

        $instances = Instance::where('client_id', $currentClient['id'])
            ->with('service')
            ->with('client')
            ->get();

        $activeInstancesClient = Instance::where('client_id', $currentClient['id'])
            ->whereIn('status', ['active', 'pending'])
            ->pluck('service_id')
            ->toArray();

        $availableServices = Service::where('status', 'active')
            ->whereNotIn('id', $activeInstancesClient)
            ->get()
            ->toArray();

        $currentDNS = $currentClient['dns'];
        $newDNS = '';

        $data = (new Util())->getSchoolFromWS($currentClient['code']);

        if ($data['error'] !== 0) {
            $error = $data['message'];
        } else {
            $newDNS = explode('$$', $data['message'])[1];
        }

        return view('myagora.instance')
            ->with('instances', $instances)
            ->with('currentClient', $currentClient)
            ->with('availableServices', $availableServices)
            ->with('error', $error ?? '')
            ->with('currentDNS', $currentDNS)
            ->with('newDNS', $newDNS ?? '');

    }

    public function files(Request $request): View {

        if (Access::isClient(Auth::user())) {
            return view('myagora.no_access')->with('message', __('myagora.no_client_access'));
        }

        $currentClient = Cache::getCurrentClient($request);
        $currentInstance = Instance::where('client_id', $currentClient['id'])
            ->where('service_id', Service::select('id')->where('name', 'Moodle')->get()->toArray())
            ->where('status', 'active')
            ->first();

        if (is_null($currentInstance)) {
            return view('myagora.file')->with('instanceId', null);
        }

        $currentInstance = $currentInstance->toArray();

        if ($request->has('file')) {
            $file = $request->input('file');
            Session::flash('message', __('file.uploaded_to_moodle', ['filename' => $file]));
        }

        // The object provided by the LaravelPlupload package won't be used because of the
        // limitations of the package. Instead, the Plupload JavaScript library will be used
        // directly.

        // Admin users have no limit on the file size.
        $maxFileSize = Access::isAdmin(Auth::user()) ? 0 : 800;
        $extensions = 'zip,mbz,xml';

        // The quota information in the cache can be out of date. Using getQuota() it is ensured that is updated.
        $quota = Quota::getQuota($currentInstance['id']);
        $percent = round($quota['used_quota'] / $quota['quota'] * 100);

        $files = Util::getFiles(Util::getAgoraVar('moodledata') .
            Config::get('app.agora.moodle2.userprefix') . $currentInstance['db_id'] .
            Config::get('app.agora.moodle2.repository_files'));

        return view('myagora.file')
            ->with('maxFileSize', $maxFileSize)
            ->with('extensions', $extensions)
            ->with('usedQuota', Util::formatBytes($quota['used_quota'], 2))
            ->with('quota', Util::formatBytes($quota['quota'], 2))
            ->with('percent', $percent)
            ->with('instanceId', $currentInstance['id'])
            ->with('files', $files);

    }

    public function uploadFile(Request $request): JsonResponse {

        if (Access::isAdmin(Auth::user()) || Access::isManager(Auth::user())) {

            // Make sure file is not cached (as it happens for example on iOS devices)
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");

            // Maximum execution time: 15 minutes.
            @set_time_limit(15 * 60);

            $targetDir = Util::getAgoraVar('portaldata') . 'tmp/uploads/';

            $currentClient = Cache::getCurrentClient($request);
            $currentInstance = Instance::where('client_id', $currentClient['id'])
                ->where('service_id', Service::select('id')->where('name', 'Moodle')->get()->toArray())
                ->where('status', 'active')
                ->first()
                ->toArray();

            $moodleDataDir = Util::getAgoraVar('moodledata') .
                Config::get('app.agora.moodle2.userprefix') . $currentInstance['db_id'] .
                Config::get('app.agora.moodle2.repository_files'); // /dades/data/moodledata/usu1/repository/files/

            $cleanupTargetDir = true; // Remove old files
            $maxFileAge = 24 * 3600; // Temp file age in seconds

            // Check target dir.
            if (!file_exists($targetDir) && !mkdir($targetDir) && !is_dir($targetDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $targetDir));
            }

            // Get a file name.
            $fileName = $request->input('name') ?? ($request->hasFile('file') ? $request->file('file')->getClientOriginalName() : uniqid('file_', true));
            $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

            // Chunking might be enabled. Chunk contains the number of part and chunks contains the total number of parts. They
            // must be integers to be able to strictly compare them.
            $chunk = (int)$request->input('chunk', 0);
            $chunks = (int)$request->input('chunks', 0);

            // Remove old temp files
            if ($cleanupTargetDir) {
                if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
                }

                while (($file = readdir($dir)) !== false) {
                    $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                    // If temp file is current file proceed to the next
                    if ($tmpfilePath === "{$filePath}.part") {
                        continue;
                    }

                    // Remove temp file if it is older than the max age and is not the current file.
                    if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
                        @unlink($tmpfilePath);
                    }
                }

                closedir($dir);
            }

            // Open temp file
            if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            }

            if (!empty($_FILES)) {
                if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES['file']['tmp_name'])) {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
                }
                // Read binary input stream and append it to temp file.
                if (!$in = @fopen($_FILES['file']['tmp_name'], "rb")) {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                }
            } else {
                if (!$in = @fopen("php://input", "rb")) {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                }
            }

            while ($buff = fread($in, 4096)) {
                fwrite($out, $buff);
            }

            @fclose($out);
            @fclose($in);

            // Check if file has been uploaded.
            if (!$chunks || $chunk === $chunks - 1) {
                // Strip the temp .part suffix off.
                rename("{$filePath}.part", $moodleDataDir . DIRECTORY_SEPARATOR . $fileName);
                Log::insert([
                    'client_id' => Cache::getCurrentClient($request)['id'],
                    'user_id' => Auth::user()->id,
                    'action_type' => Log::ACTION_TYPE_ADD,
                    'action_description' => __('file.uploaded_to_moodle_short', ['filename' => $fileName]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Quota::addToQuota($request, filesize($moodleDataDir . DIRECTORY_SEPARATOR . $fileName));
            }

            // Return Success JSON-RPC response
            die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
        }

        // If the user is not admin or manager, abort the request.
        abort(403, 'No tens permís per executar aquest fitxer.');

    }

    public function downloadFile(Request $request): BinaryFileResponse {

        $fileName = $request->input('file');

        if (empty($fileName)) {
            abort(404);
        }

        $currentClient = Cache::getCurrentClient($request);
        $currentInstance = Instance::where('client_id', $currentClient['id'])
            ->where('service_id', Service::select('id')->where('name', 'Moodle')->get()->toArray())
            ->where('status', 'active')
            ->first()
            ->toArray();

        $dir = Util::getAgoraVar('moodledata') .
            Config::get('app.agora.moodle2.userprefix') . $currentInstance['db_id'] .
            Config::get('app.agora.moodle2.repository_files');
        $path = $dir . DIRECTORY_SEPARATOR . $fileName;

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path);

    }

    public function deleteFile(Request $request): RedirectResponse {

        $fileName = $request->input('file');

        if (empty($fileName)) {
            abort(404);
        }

        $currentClient = Cache::getCurrentClient($request);
        $currentInstance = Instance::where('client_id', $currentClient['id'])
            ->where('service_id', Service::select('id')->where('name', 'Moodle')->get()->toArray())
            ->where('status', 'active')
            ->first()
            ->toArray();

        $dir = Util::getAgoraVar('moodledata') .
            Config::get('app.agora.moodle2.userprefix') . $currentInstance['db_id'] .
            Config::get('app.agora.moodle2.repository_files');;
        $path = $dir . DIRECTORY_SEPARATOR . $fileName;
        $fileSize = filesize($path);

        if (!file_exists($path)) {
            abort(404);
        }

        unlink($path);

        Log::insert([
            'client_id' => Cache::getCurrentClient($request)['id'],
            'user_id' => Auth::user()->id,
            'action_type' => Log::ACTION_TYPE_DELETE,
            'action_description' => __('file.deleted_from_moodle', ['filename' => $fileName]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Quota::subtractFromQuota($request, $fileSize);

        return redirect()->route('myagora.files');

    }

    public function requests(Request $request): View {
        if (Access::isClient(Auth::user())) {
            return view('myagora.no_access')->with('message', __('myagora.no_client_access'));
        }

        if (Access::isAdmin(Auth::user())) {
            $currentClient = Util::getClientFromUrl($request);
        }

        if (empty($currentClient)) {
            $currentClient = Cache::getCurrentClient($request);
        }

        if (empty($currentClient)) {
            return view('myagora.request')->with('requests', []);
        }

        $availableRequests = [];
        if (!Access::isClient(Auth::user())) {
            $instances = Instance::where('client_id', $currentClient['id'])
                ->with('service')
                ->get();
            foreach ($instances as $instance) {
                if ($instance->status === 'active') {
                    $availableRequests[$instance->service->name] = DB::table('request_type_service')
                        ->where('service_id', $instance->service->id)
                        ->join('request_types', 'request_type_service.request_type_id', '=', 'request_types.id')
                        ->get()
                        ->toArray();
                }
            }
        }

        // Must declare the namespace of the model to avoid conflicts with the Request class.
        $requests = \App\Models\Request::where('client_id', $currentClient['id'])
            ->latest()
            ->with('user')
            ->with('service')
            ->with('requestType')
            ->paginate(25);

        return view('myagora.request')
            ->with('requests', $requests)
            ->with('currentClient', $currentClient)
            ->with('availableRequests', $availableRequests);
    }

    public function managers(Request $request): View {

        $currentClient = $this->getCurrentClient($request);

        if (empty($currentClient)) {
            return view('myagora.manager')
                ->with('managers', [])
                ->with('max_managers', Manager::MAX_MANAGERS_PER_CLIENT);
        }

        // Get an array of objects of type Manager.
        $client = Client::find($currentClient['id']);
        $managers = $client->managers->all();

        foreach ($managers as $manager) {
            $manager->name = $manager->user->name;
            $manager->email = $manager->user->email;
        }

        return view('myagora.manager')
            ->with('managers', $managers)
            ->with('currentClient', $currentClient)
            ->with('max_managers', Manager::MAX_MANAGERS_PER_CLIENT);
    }

    public function logs(Request $request): View {

        $currentClient = $this->getCurrentClient($request);

        if (empty($currentClient)) {
            return view('myagora.log')->with('log', []);
        }

        $log = Log::where('client_id', $currentClient['id'])
            ->latest()
            ->with('user')
            ->paginate(25);

        return view('myagora.log')
            ->with('log', $log)
            ->with('currentClient', $currentClient);

    }

    /**
     * Endpoint for AJAX call used when creating a new request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRequestDetails(Request $request): JsonResponse {

        $option = $request->validate([
            'option' => 'regex:/^\d+:\d+$/',
        ]);

        $requestIds = explode(':', $option['option']);

        $requestDetails = RequestType::select('request_types.name', 'request_types.description', 'request_types.prompt')
            ->join('request_type_service', 'request_type_service.request_type_id', '=', 'request_types.id')
            ->where('request_type_service.request_type_id', $requestIds[1])
            ->where('request_type_service.service_id', $requestIds[0])
            ->first()
            ->toArray();

        $content = view('myagora.components.request_content')
            ->with('requestDetails', $requestDetails)
            ->render();

        if ($option['option'] === "4:1") {
            $client_id = intval($request->get('clientID'));
            $configQuota = floatval(Util::getConfigParam('quota_usage_to_request')); // 0.75
            $myInstance = Instance::where('service_id', $requestIds[0])->where('client_id', $client_id)->first();
            $ratio = round($myInstance->used_quota / $myInstance->quota, 4);

            if ($ratio < $configQuota) {
                $content = '<div class="alert alert-danger">'.__('request.underQuota', ['quotaLimit'=>$configQuota*100]).'</div>';
            }
        }

        return response()->json(['html' => $content]);
    }

    public function recalcQuota(Request $request): RedirectResponse {
        $instanceId = $request->input('id');

        $instance = Instance::where('id', $instanceId)->first();
        $service = Service::find($instance->service_id);

        if ($service->name === 'Moodle') {
            $dataDir = Util::getAgoraVar(mb_strtolower($service->name) . 'data') .
                Config::get('app.agora.moodle2.userprefix') . $instance->db_id;
        } else {
            $dataDir = Util::getAgoraVar(mb_strtolower($service->name) . 'data') .
                Config::get('app.agora.' . mb_strtolower($service->name) . '.userprefix') . $instance->db_id;
        }

        $instance->used_quota = Quota::getDiskUsage($dataDir);
        $instance->save();

        return redirect()->route('myagora.instances')->with('success', __('file.quota_updated'));
    }

    private function getCurrentClient(Request $request) {
        // If the current user is admin, try to get a client code from the URL. Otherwise, get the
        // current client from the session.
        if (Access::isAdmin(Auth::user())) {
            $currentClient = Util::getClientFromUrl($request);
        }

        if (empty($currentClient)) {
            $currentClient = Cache::getCurrentClient($request);
        }

        if (empty($currentClient)) {
            $data = (new Util())->getSchoolFromWS(Auth::user()['name']);

            if ($data['error'] === 1) {
                $request->session()->flash('error', $data['message']);
            }

            return [];
        }

        return $currentClient;
    }

    public function changeDNS(Request $request): RedirectResponse {

        $user = Auth::user();
        if (!(Access::isAdmin($user) || Access::isClient($user) || Access::isManager($user))) {
            return redirect()->route('myagora.instances')
                ->with('error', __('myagora.not_enough_permissions'));
        }

        // Check the client ID
        $values = $request->validate([
            'clientId' => 'required|integer',
        ]);
        $clientId = $values['clientId'];

        // Check the DNS, current and new.
        $currentDNS = $request->input('currentDNS');
        $newDNS = $request->input('newDNS');

        $util = new Util();

        if (!$util->isValidDNS($currentDNS)) {
            return redirect()->route('myagora.instances')
                ->with('error', __('myagora.invalid_dns'));
        }

        if (!$util->isValidDNS($newDNS)) {
            return redirect()->route('myagora.instances')
                ->with('error', __('myagora.invalid_dns'));
        }

        $clientUpdated = Client::where('id', $clientId)
            ->update(['dns' => $newDNS, 'old_dns' => $currentDNS]);

        // Edit the client in the database. Move dns to old_dns and set the new dns.
        if ($clientUpdated === 0) {
            return redirect()->route('myagora.instances')
                ->with('error', __('client.nompropi_not_updated'));
        }

        // Get the active instances of the client.
        $instances = Instance::where('client_id', $clientId)
            ->where('status', 'active')
            ->get();

        if ($instances->isEmpty()) {
            return redirect()->route('myagora.instances')
                ->with('error', __('client.nompropi_no_active_instances'));
        }

        // Program the change of DNS by calling the proper function. Because the 'nom propi' is already changed in
        // the client's table, the program functions need only the current 'nom propi', which is, in fact, the old one.
        foreach ($instances as $instance) {
            if ($instance->service->name === 'Moodle') {
                $this->programChangeMoodleDNS($instance, $currentDNS);
            }
            if ($instance->service->name === 'Nodes') {
                $this->programChangeNodesDNS($instance, $currentDNS);
            }
        }

        // Remove the client information from the cache to get the dns updated.
        $request->session()->forget('currentClient');

        return redirect()->route('myagora.instances')
            ->with('success', __('client.change_of_nompropi_programmed'));

    }

    private function programChangeMoodleDNS(Instance $instance, string $currentDNS): void {

        $newURL = Util::getInstanceUrl($instance);
        $newURL = str_replace(['http://', 'https://'], '://', $newURL);
        $currentURL = str_replace($instance->client->dns, $currentDNS, $newURL);

        ProcessOperation::dispatch([
            'action' => 'script_replace_database_text',
            'priority' => 'high',
            'params' => [
                'origintext' => $currentURL,
                'targettext' => $newURL,
            ],
            'service_name' => $instance->service->name,
            'instance_id' => $instance['id'],
            'instance_name' => $instance->client->name,
            'instance_dns' => $instance->client->dns,
        ]);

    }

    private function programChangeNodesDNS(Instance $instance, string $currentDNS): void {

        $newURL = Util::getInstanceUrl($instance);
        $newURL = str_replace(['http://', 'https://'], '://', $newURL);
        $currentURL = str_replace($instance->client->dns, $currentDNS, $newURL);

        ProcessOperation::dispatch([
            'action' => 'script_replace_url',
            'priority' => 'high',
            'params' => [
                'origin_url' => $currentURL,
            ],
            'service_name' => $instance->service->name,
            'instance_id' => $instance['id'],
            'instance_name' => $instance->client->name,
            'instance_dns' => $instance->client->dns,
        ]);

    }

}

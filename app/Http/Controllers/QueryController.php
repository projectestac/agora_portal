<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQueryRequest;
use App\Http\Requests\UpdateQueryRequest;
use App\Models\Client;
use App\Models\Instance;
use App\Models\Query;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QueryController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse {
        $allowedValues = ['all', 'select', 'insert', 'update', 'delete', 'alter', 'drop'];
        $type = $request->input('filter');
        $serviceId = $request->input('serviceId');

        if (!in_array($type, $allowedValues, true)) {
            $type = 'all';
        }

        if ($type === 'all') {
            $queries = Query::where('service_id', $serviceId)->get();
        } else {
            $queries = Query::where('type', $type)
                ->where('service_id', $serviceId)
                ->get();
        }

        $content = view('admin.batch.query-list-item')
            ->with('queries', $queries)
            ->render();

        return response()->json(['html' => $content]);
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
    public function store(StoreQueryRequest $request): RedirectResponse {
        $query = new Query([
            'service_id' => $request->input('serviceSelModal'),
            'query' => urldecode($request->input('sqlQueryModalAddEncoded')),
            'description' => $request->input('descriptionModalAdd'),
            'type' => $request->input('queryTypeModalAdd'),
        ]);

        $query->save();

        return redirect()->route('batch.query')->with('success', __('batch.query_added'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Query $query) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Query $query): JsonResponse {
        $services = Service::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->toArray();

        array_unshift($services, ['id' => 0, 'name' => 'Portal']);

        $content = view('admin.batch.query-modal-edit')
            ->with('services', $services)
            ->with('query', $query)
            ->with('type', self::getTypeList())
            ->render();

        return response()->json(['html' => $content]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQueryRequest $request, Query $query): RedirectResponse {
        $query->service_id = $request->input('serviceSelModalEdit');
        $query->query = urldecode($request->input('sqlQueryModalEditEncoded'));
        $query->description = $request->input('descriptionModalEdit');
        $query->type = $request->input('queryTypeModalEdit');

        $query->save();

        return redirect()->route('batch.query')->with('success', __('batch.query_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Query $query): JsonResponse {
        $queryInstance = Query::find($query->id);
        $queryInstance->delete();

        return response()->json([
            'html' => '<span class="alert alert-danger">' . __('batch.query_deleted') . '</span>',
        ]);
    }

    public function confirmQuery(Request $request): View {
        $validatedData = $request->validate([
            'sqlQueryEncoded' => 'required|string',
            'serviceSel' => 'required|int',
            'serviceSelector' => 'string|max:10', // 'all' or 'selected'
            'clientsSel' => 'array',
        ]);

        $sqlQueryEncoded = $validatedData['sqlQueryEncoded'];
        $serviceSel = $validatedData['serviceSel'];
        $serviceSelector = $validatedData['serviceSelector'];
        $clientsSel = $validatedData['clientsSel'] ?? [];

        if ((int)$serviceSel === 0) {
            $serviceName = 'Portal';
            $image = 'agora';
            $clients = [];
        } else {
            $serviceName = Service::find($serviceSel)->name;
            $image = mb_strtolower($serviceName);

            // In case of 'all' clients, we need to get the full list of clients of the service.
            if ($serviceSelector === 'all') {
                $clients = Instance::select(['clients.id', 'clients.name', 'clients.code'])
                    ->join('clients', 'instances.client_id', '=', 'clients.id')
                    ->where('instances.service_id', $serviceSel)
                    ->where('instances.status', 'active')
                    ->orderBy('clients.name')
                    ->get()->toArray();
            } else {
                $clients = Client::select(['id', 'name', 'code'])
                    ->whereIn('id', $clientsSel)
                    ->orderBy('name')
                    ->get()->toArray();
            }
        }

        return view('admin.batch.query-confirm')
            ->with('sqlQueryEncoded', $sqlQueryEncoded)
            ->with('serviceSel', $serviceSel)
            ->with('clients', $clients)
            ->with('serviceName', $serviceName)
            ->with('image', $image);
    }

    public function executeQuery(Request $request): View {
        $validatedData = $request->validate([
            'sqlQueryEncoded' => 'required|string',
            'serviceSel' => 'required|int',
            'clientsSel' => 'array',
            'serviceName' => 'string|max:15',
            'image' => 'string|max:15',
        ]);

        $sqlQueryEncoded = $validatedData['sqlQueryEncoded'];
        $serviceSel = $validatedData['serviceSel'];
        $clientsSel = $validatedData['clientsSel'] ?? [];
        $serviceName = $validatedData['serviceName'];
        $image = $validatedData['image'];

        $sqlQuery = trim(urldecode($sqlQueryEncoded));
        $isSelect = Str::startsWith(Str::lower($sqlQuery), 'select');

        $request->session()->put('query', $sqlQuery);

        $serviceNameLower = Str::lower($serviceName);
        $globalResults = [];
        $fullResults = [];
        $summary = [];
        $attributes = [];

        // Query executed to the Portal database.
        if ((int)$serviceSel === 0) {
            if ($isSelect) {
                $execResult = DB::select($sqlQuery);
            } else {
                $execResult = DB::statement($sqlQuery);
            }

            [$fullResults, $attributes, $result] = $this->processQueryResults($execResult, env('DB_DATABASE'), 'Portal');

            $globalResults[env('DB_DATABASE')] = [
                'database' => env('DB_DATABASE'),
                'clientName' => 'Portal',
                'result' => $result,
            ];

            return view('admin.batch.query-execute')
                ->with('sqlQueryEncoded', $sqlQueryEncoded)
                ->with('serviceName', $serviceName)
                ->with('image', $image)
                ->with('globalResults', $globalResults)
                ->with('fullResults', [$fullResults])
                ->with('attributes', $attributes)
                ->with('numRows', -1);
        }

        $userName = '';
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

        $instances = Instance::select('db_id', 'db_host', 'clients.name as client_name', 'clients.code as client_code')
            ->join('clients', 'instances.client_id', '=', 'clients.id')
            ->where('instances.service_id', $serviceSel)
            ->whereIn('instances.client_id', $clientsSel)
            ->where('instances.status', 'active')
            ->orderBy('instances.id')
            ->get()->toArray();

        config(["database.connections.$serviceNameLower.password" => $userPassword]);
        $userPrefix = config("app.agora.$serviceKey.userprefix");

        foreach ($instances as $instance) {
            $dbName = $userPrefix . $instance['db_id'];
            $userName = ($serviceName === 'Nodes') ? $userName : $dbName;

            config(["database.connections.$serviceNameLower.host" => $instance['db_host']]);
            config(["database.connections.$serviceNameLower.database" => $dbName]);
            config(["database.connections.$serviceNameLower.username" => $userName]);

            // Force the change of the database connection. Otherwise, the query will be always be executed in the same database.
            DB::connection($serviceNameLower)->reconnect();

            // Execute query
            if ($isSelect) {
                try {
                    $execResult = DB::connection($serviceNameLower)->select($sqlQuery);
                }

                catch (\Exception $e) {
                    $execResult = $e->getMessage();
                }
            } else {
                try {
                    $execResult = DB::connection($serviceNameLower)->affectingStatement($sqlQuery) . ' ' . __('common.affected_rows');
                } catch (\Exception $e) {
                    $execResult = $e->getMessage();
                }
            }

            [$fullResult, $attributes, $result, $numRows] = $this->processQueryResults($execResult, $dbName, $instance['client_name']);
            $fullResults[] = $fullResult;

            $summary[$result] = isset($summary[$result]) ? ++$summary[$result] : 1;

            $globalResults[$dbName] = [
                'database' => $dbName,
                'clientName' => $instance['client_name'],
                'clientCode' => $instance['client_code'],
                'result' => $result,
            ];
        }

        // If there are no results or the number of columns is less than 2, the summary will be shown, if not, it doesn't make sense to show it.
        $showSummary = count($fullResults[0]) == 0 || (count($fullResults[0]) > 0 && $fullResults[0][array_key_first($fullResults[0])] < 2);

        return view('admin.batch.query-execute')
            ->with('sqlQueryEncoded', $sqlQueryEncoded)
            ->with('serviceName', $serviceName)
            ->with('image', $image)
            ->with('globalResults', $globalResults)
            ->with('fullResults', $fullResults)
            ->with('attributes', $attributes)
            ->with('summary', $summary)
            ->with('numRows', $numRows)
            ->with('showSummary', $showSummary);
    }

    private function processQueryResults(mixed $execResult, string $dbName, string $clientName): array {

        $fullResults = [];
        $attributes = [];

        if ($execResult === true) {
            $execResult = 'OK';
            $numRows = 1;
        }

        else {
            $numRows = 0;
        }

        if (is_array($execResult)) {
            $numRows = count($execResult);

            if ($numRows === 0) {
                $execResult = 0;
            } elseif ($numRows === 1) {
                // In this case, there is only one row, so it is not necessary to get the name of the database field. The
                // $result variable will contain the value of the field.
                $attribute = get_object_vars(reset($execResult));
                $execResult = reset($attribute);
            } elseif ($numRows > 1) {
                // If $fullResults is not empty, the template will iterate over it to show a table for each instance. The
                // name of the database fields is kept in the $attributes variable and the $result variable will contain
                // the number of records returned by the query.
                $fullResults[$dbName . ' - ' . $clientName] = $execResult;
                $attributes = array_keys(get_object_vars(reset($execResult)));
                $execResult = $numRows;
            }
        }

        return [$fullResults, $attributes, $execResult, $numRows];

    }

    public static function getTypeList(): array {
        return [
            Query::TYPE_SELECT => __('batch.query_type_select'),
            Query::TYPE_INSERT => __('batch.query_type_insert'),
            Query::TYPE_UPDATE => __('batch.query_type_update'),
            Query::TYPE_DELETE => __('batch.query_type_delete'),
            Query::TYPE_ALTER => __('batch.query_type_alter'),
            Query::TYPE_DROP => __('batch.query_type_drop'),
            Query::TYPE_OTHER => __('batch.query_type_other'),
        ];
    }

}

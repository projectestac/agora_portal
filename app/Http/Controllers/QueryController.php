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
        $search = $request->input('search');

        if (!in_array($type, $allowedValues, true)) {
            $type = 'all';
        }

        $queryBuilder = Query::where('service_id', $serviceId);

        if ($type !== 'all') {
            $queryBuilder->where('type', $type);
        }

        if (!empty($search)) {
            $queryBuilder->where(function ($q) use ($search) {
                $q->where('query', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $queries = $queryBuilder->get();

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
        $request->session()->put('serviceSel', $serviceSel);

        $serviceNameLower = Str::lower($serviceName);
        $globalResults = [];
        $fullResults = [];
        $summary = [];
        $summaryAffectedRows = [];
        $summaryInstances = [];
        $resultPreviewList = [];

        // If the service is 'Portal', we execute the query directly on the Portal database.
        if ((int)$serviceSel === 0) {
            return $this->executeQuerySingleSite($sqlQuery, $isSelect, 'Portal', 'portal', $sqlQueryEncoded);
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

        $instances = Instance::select('instances.db_id', 'instances.db_host', 'clients.name as client_name',
            'clients.code as client_code', 'clients.dns as client_dns', 'services.slug as service_slug')
            ->join('clients', 'instances.client_id', '=', 'clients.id')
            ->join('services', 'instances.service_id', '=', 'services.id')
            ->where('instances.service_id', $serviceSel)
            ->whereIn('instances.client_id', $clientsSel)
            ->where('instances.status', 'active')
            ->orderBy('instances.id')
            ->get()
            ->toArray();

        config(["database.connections.$serviceNameLower.password" => $userPassword]);
        $userPrefix = config("app.agora.$serviceKey.userprefix");

        // If doing a select on several instances, we need to ensure that at least one instance has a unique column to summarize results.
        // If doing a select query on various instances, we will summarize the results by a unique column.
        // Needs to work even if some instances doesn't have the table.
        $atLeastOneInstanceHasUniqueColumn = false;
        $securedAttributes = [];

        foreach ($instances as $instance) {
            $dbName = $userPrefix . $instance['db_id'];
            $userName = ($serviceName === 'Nodes') ? $userName : $dbName;

            config([
                "database.connections.$serviceNameLower.host" => $instance['db_host'],
                "database.connections.$serviceNameLower.database" => $dbName,
                "database.connections.$serviceNameLower.username" => $userName,
            ]);

            DB::connection($serviceNameLower)->reconnect();

            $resultStatus = ['success' => true, 'message' => __('batch.execution_success')];

            if ($isSelect) {
                try {
                    $execResult = DB::connection($serviceNameLower)->select($sqlQuery);
                    $affectedRows = count($execResult);
                } catch (\Exception $e) {
                    $execResult = [];
                    $resultStatus = ['success' => false, 'message' => __('batch.execution_error') . ': ' . $e->getMessage()];
                    $affectedRows = 0;
                }
            } else {
                try {
                    $affectedRows = DB::connection($serviceNameLower)->affectingStatement($sqlQuery);
                    $execResult = []; // No result set for non-select queries
                } catch (\Exception $e) {
                    $execResult = $e->getMessage();
                    $resultStatus = ['success' => false, 'message' => __('batch.execution_error') . ': ' . $e->getMessage()];
                    $affectedRows = 0;
                }
            }

            [$fullResult, $attributes, $result, $numRows] = $this->processQueryResults($execResult, $dbName, $instance['client_name']);
            $fullResults[] = $fullResult;

            if(count($attributes) === 1) {
                $atLeastOneInstanceHasUniqueColumn = true;
            }

            // In case the next instance doesn't have the table, for example, we need to ensure that we have the list of attributes
            if(count($attributes) > 1) {
                $securedAttributes = $attributes;
            }

            if (is_array($execResult) && count($attributes) === 1) {
                $seenValues = [];

                foreach ($execResult as $row) {
                    $value = $row->{$attributes[0]} ?? null;
                    if ($value !== null) {
                        $summary[$value] = isset($summary[$value]) ? $summary[$value] + 1 : 1;

                        if (!isset($seenValues[$value])) {
                            $summaryInstances[$value] = isset($summaryInstances[$value]) ? $summaryInstances[$value] + 1 : 1;
                            $seenValues[$value] = true;
                        }
                    }
                }
            }

            // Prepare the preview of the results
            $previewValues = collect($execResult)
                ->map(function ($row) use ($attributes) {
                    // Limit the preview to the first 20 characters of each attribute
                    // Join columns with ' | '
                    return collect($attributes)->map(function ($attr) use ($row) {
                        return Str::limit($row->$attr ?? '', 20, '...');
                    })->implode(' | ');
                })
                ->implode(', ');

            // Limit the global preview to 120 characters
            $previewValues = Str::limit($previewValues, 120, '...');

            $resultPreviewList[] = [
                'database' => $dbName,
                'clientName' => $instance['client_name'],
                'preview' => $previewValues,
                'clientDNS' => $instance['client_dns'],
                'serviceSlug' => $instance['service_slug'],
                'count' => is_countable($execResult) ? count($execResult) : 0,
                'resultStatus' => $resultStatus,
                'affectedRows' => $affectedRows,
            ];

            $globalResults[$dbName] = [
                'database' => $dbName,
                'clientName' => $instance['client_name'],
                'clientCode' => $instance['client_code'],
                'clientDNS' => $instance['client_dns'],
                'serviceSlug' => $instance['service_slug'],
                'result' => $result,
            ];

            if (!isset($summaryAffectedRows[$affectedRows])) {
                $summaryAffectedRows[$affectedRows] = 0;
            }

            $summaryAffectedRows[$affectedRows]++;
        }

        return view('admin.batch.query-execute-multi-site')
            ->with('sqlQueryEncoded', $sqlQueryEncoded)
            ->with('serviceName', $serviceName)
            ->with('image', $image)
            ->with('globalResults', $globalResults)
            ->with('fullResults', $fullResults)
            ->with('attributes', $securedAttributes)
            ->with('summary', $summary)
            ->with('summaryAffectedRows', $summaryAffectedRows)
            ->with('summaryInstances', $summaryInstances ?? [])
            ->with('resultPreviewList', $resultPreviewList)
            ->with('numRows', $numRows)
            ->with('showSummary', $atLeastOneInstanceHasUniqueColumn)
            ->with('showResults', true)
            ->with('isSelect', $isSelect);
    }

    private function executeQuerySingleSite(string $sqlQuery, bool $isSelect, string $serviceName, string $image, string $sqlQueryEncoded): View
    {
        $execResult = $isSelect ? DB::select($sqlQuery) : DB::statement($sqlQuery);
        [$fullResultsData, $attributes, $result] = $this->processQueryResults($execResult, env('DB_DATABASE'), 'Portal');

        $globalResults[env('DB_DATABASE')] = [
            'database' => env('DB_DATABASE'),
            'clientName' => 'Portal',
            'clientDNS' => 'Portal',
            'serviceSlug' => '',
            'result' => $result,
        ];

        return view('admin.batch.query-execute-single-site')
            ->with('sqlQueryEncoded', $sqlQueryEncoded)
            ->with('serviceName', $serviceName)
            ->with('image', $image)
            ->with('globalResults', $globalResults)
            ->with('fullResults', [$fullResultsData])
            ->with('resultPreviewList', [])
            ->with('summary', [])
            ->with('attributes', $attributes)
            ->with('numRows', -1)
            ->with('showSummary', false)
            ->with('showResults', empty($fullResultsData))
            ->with('isSelect', $isSelect);
    }

    private function processQueryResults(mixed $execResult, string $dbName, string $clientName): array {

        $fullResults = [];
        $attributes = [];

        if ($execResult === true) {
            $execResult = 'OK';
            $numRows = 1;
        } else {
            $numRows = 0;
        }

        if (is_array($execResult)) {
            $numRows = count($execResult);

            if ($numRows === 0) {
                $execResult = 0;
            } elseif ($numRows === 1) {
                $fullResults[$dbName . ' - ' . $clientName] = $execResult;
                $attributes = array_keys(get_object_vars(reset($execResult)));
                $execResult = 1;
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

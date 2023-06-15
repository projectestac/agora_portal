<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQueryRequest;
use App\Http\Requests\UpdateQueryRequest;
use App\Models\Query;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
            'query' => base64_decode($request->input('sqlQueryModalAddEncoded')),
            'description' => $request->input('descriptionModalAdd'),
            'type' => $request->input('queryTypeModalAdd'),
        ]);

        $query->save();

        return redirect()->route('query')->with('success', __('batch.query_added'));
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
            ->render();

        return response()->json(['html' => $content]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQueryRequest $request, Query $query) {
        $query->service_id = $request->input('serviceSelModalEdit');
        $query->query = base64_decode($request->input('sqlQueryModalEditEncoded'));
        $query->description = $request->input('descriptionModalEdit');
        $query->type = $request->input('queryTypeModalEdit');

        $query->save();

        return redirect()->route('query')->with('success', __('batch.query_updated'));
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
}

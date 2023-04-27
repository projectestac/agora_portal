<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQueryRequest;
use App\Http\Requests\UpdateQueryRequest;
use App\Models\Query;
use Illuminate\Http\RedirectResponse;

class QueryController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //
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
            'command' => $request->input('sqlQueryModal'),
            'description' => $request->input('descriptionModal'),
            'type' => $request->input('queryType'),
        ]);

        $query->save();

        return redirect()->route('query')->with('success', __('batch.query_added'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Query $command) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Query $command) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQueryRequest $request, Query $command) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Query $command) {
        //
    }
}

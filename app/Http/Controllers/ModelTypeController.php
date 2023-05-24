<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModelTypeRequest;
use App\Http\Requests\UpdateModelTypeRequest;
use App\Models\ModelType;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;

class ModelTypeController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(): View|Application|Factory|ApplicationContract  {
        return view('model.index');
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
    public function store(StoreModelTypeRequest $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ModelType $modelType) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ModelType $modelType) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateModelTypeRequest $request, ModelType $modelType) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ModelType $modelType) {
        //
    }
}

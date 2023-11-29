<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModelTypeRequest;
use App\Models\ModelType;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModelTypeController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(): View {
        $modelTypes = ModelType::get();

        return view('admin.model-type.index')
            ->with('modelTypes', $modelTypes);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        $services = Service::all()->sortBy('name');

        return view('admin.model-type.create')
            ->with('services', $services);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreModelTypeRequest $request): RedirectResponse
    {
        $service_id = $request->input('service_id');
        $description = $request->input('description');
        $short_code = $request->input('short_code');
        $url = $request->input('url');
        $db = $request->input('db');

        $modelType = new ModelType([
            'service_id' => $service_id,
            'description' => $description,
            'short_code' => $short_code,
            'url' => $url,
            'db' => $db
        ]);

        try {
            $modelType->save();
        } catch (\Exception $e) {

            return redirect()->route('model-types.create')
                ->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('model-types.index')
            ->with('success', __('model.created_model_short'));
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
    public function edit($id): View {
        $services = Service::all()->sortBy('name');
        $modelType = ModelType::findOrFail($id);

        return view('admin.model-type.edit')
                ->with('modelType', $modelType)
                ->with('services', $services); // we send the model type to the view
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse {
        $validatedData = $request->validate([
            'service_id' => 'required|int',
            'description' => 'required|string',
            'short_code' => 'required|string',
            'url' => 'required|url',
            'db' => 'required|string',
        ]);

        $modelType = ModelType::findOrFail($id);

        $modelType->update([
            'service_id' => $validatedData['service_id'],
            'description' => $validatedData['description'],
            'short_code' => $validatedData['short_code'],
            'url' => $validatedData['url'],
            'db' => $validatedData['db'],
        ]);

        return redirect()->route('model-types.index')->with('success', __('request.request_created'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse {
        $modelType = ModelType::findOrFail($id);
        $modelType->delete();

        return redirect()->route('model-types.index')->with('success', __('common.deletion_success'));
    }
}

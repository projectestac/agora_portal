<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModelTypeRequest;
use App\Http\Requests\UpdateModelTypeRequest;
use App\Models\ModelType;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ModelTypeController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(): View {
        $modelTypes = ModelType::with('service')->get();

        return view('admin.model-type.index')
            ->with('modelTypes', $modelTypes);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View {
        $services = Service::all()->sortBy('name');

        return view('admin.model-type.create')
            ->with('services', $services);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreModelTypeRequest $request): RedirectResponse {

        $modelType = new ModelType([
            'service_id' => $request->validated('service_id'),
            'description' => $request->validated('description'),
            'short_code' => $request->validated('short_code'),
            'url' => $request->validated('url'),
            'db' => $request->validated('db'),
        ]);

        try {
            $modelType->save();
        } catch (\Exception $e) {
            return redirect()
                ->route('model-types.create')
                ->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('model-types.index')
            ->with('success', __('modeltype.created_model_short'));

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
            ->with('services', $services);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateModelTypeRequest $request, $id): RedirectResponse {
        $modelType = ModelType::findOrFail($id);

        $modelType->update([
            'service_id' => $request->validated('service_id'),
            'description' => $request->validated('description'),
            'short_code' => $request->validated('short_code'),
            'url' => $request->validated('url'),
            'db' => $request->validated('db'),
        ]);

        return redirect()
            ->route('model-types.index')
            ->with('success', __('request.request_created'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse {
        $modelType = ModelType::findOrFail($id);
        $modelType->delete();

        return redirect()
            ->route('model-types.index')
            ->with('success', __('common.deletion_success'));
    }
}

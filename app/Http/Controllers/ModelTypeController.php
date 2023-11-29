<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModelTypeRequest;
use App\Models\ModelType;
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
        return view('admin.model-type.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreModelTypeRequest $request): RedirectResponse
    {
        $description = $request->input('description');
        $url = $request->input('url');

        $modelType = new ModelType([
            'short_code' => 'xxx', // TO BE CHANGED
            'service_id' => 4, // TO BE CHANGED
            'description' => $description,
            'url' => $url,
            'db' => 'usu1000' // TO BE CHANGED
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
        $modelType = ModelType::findOrFail($id);
        return view('admin.model-type.edit')->with('modelType', $modelType); // we send the model type to the view
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse {
        $validatedData = $request->validate([
            'description' => 'required|string',
            'url' => 'required|url',
        ]);

        $modelType = ModelType::findOrFail($id);

        $modelType->update([
            'description' => $validatedData['description'],
            'url' => $validatedData['url'],
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

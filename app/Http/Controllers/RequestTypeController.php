<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequestTypeRequest;
use App\Http\Requests\UpdateRequestTypeRequest;
use App\Models\RequestType;
use Illuminate\Http\Request;

class RequestTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requestTypes = RequestType::get();

        return view('admin.request-type.index')
            ->with('requestTypes', $requestTypes);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.request-type.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequestTypeRequest $request)
    {
        $name = $request->input('name');
        $description = $request->input('description');
        $prompt = $request->input('prompt');

        $requestType = new RequestType([
            'name' => $name,
            'description' => $description,
            'prompt' => $prompt
        ]);

        try {
            $requestType->save();
        } catch (\Exception $e) {

            return redirect()->route('request-types.create')
                ->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('request-types.index')
            ->with('success', __('request-type.created_request_type_short'));
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestType $requestType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $requestType = RequestType::findOrFail($id);

        return view('admin.request-type.edit')
                ->with('requestType', $requestType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'prompt' => 'required|string'
        ]);

        $requestType = RequestType::findOrFail($id);

        $requestType->update([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'prompt' => $validatedData['prompt']
        ]);

        return redirect()->route('request-types.index')->with('success', __('request.request_created'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $requestType = RequestType::findOrFail($id);
        $requestType->delete();

        return redirect()->route('request-types.index')->with('success', __('common.deletion_success'));
    }
}

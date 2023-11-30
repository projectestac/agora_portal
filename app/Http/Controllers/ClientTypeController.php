<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientTypeRequest;
use App\Http\Requests\UpdateClientTypeRequest;
use App\Models\ClientType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientTypes = ClientType::get();

        return view('admin.client-type.index')
            ->with('clientTypes', $clientTypes);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.client-type.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientTypeRequest $request): RedirectResponse
    {
        $name = $request->input('name');
        $clientType = new ClientType([
            'name' => $name
        ]);

        try {
            $clientType->save();
        } catch (\Exception $e) {

            return redirect()->route('client-types.create')
                ->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('client-types.index')
            ->with('success', __('client-type.created_client_type_short'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ClientType $clientType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $clientType = ClientType::findOrFail($id);

        return view('admin.client-type.edit')
                ->with('clientType', $clientType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string'
        ]);

        $clientType = ClientType::findOrFail($id);

        $clientType->update([
            'name' => $validatedData['name']
        ]);

        return redirect()->route('client-types.index')->with('success', __('request.request_created'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $clientType = ClientType::findOrFail($id);
        $clientType->delete();

        return redirect()->route('client-types.index')->with('success', __('common.deletion_success'));
    }
}

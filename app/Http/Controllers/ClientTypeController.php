<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientTypeRequest;
use App\Http\Requests\UpdateClientTypeRequest;
use App\Models\ClientType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ClientTypeController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(): View {
        $clientTypes = ClientType::get();

        return view('admin.client-type.index')
            ->with('clientTypes', $clientTypes);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View {
        return view('admin.client-type.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientTypeRequest $request): RedirectResponse {
        $name = $request->input('name');

        $clientType = new ClientType([
            'name' => $name,
        ]);

        try {
            $clientType->save();
        } catch (\Exception $e) {
            return redirect()->route('client-types.create')
                ->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('client-types.index')
            ->with('success', __('client-type.client_type_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ClientType $clientType) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClientType $clientType): View {
        return view('admin.client-type.edit')
            ->with('clientType', $clientType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientTypeRequest $request, ClientType $clientType): RedirectResponse {
        $clientType->update([
            'name' => $request->input(['name']),
        ]);

        return redirect()
            ->route('client-types.index')
            ->with('success', __('client-type.client_type_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClientType $clientType): RedirectResponse {
        $clientType->delete();

        return redirect()
            ->route('client-types.index')
            ->with('success', __('client-type.client_type_deleted'));
    }
}

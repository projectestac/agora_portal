<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class LocationController extends Controller {
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View {
        $locations = Location::get();

        return view('admin.locations.index')
            ->with('locations', $locations);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View {
        return view('admin.locations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLocationRequest $request): RedirectResponse {
        $location = new Location([
            'name' => $request->input('name'),
        ]);

        try {
            $location->save();
        } catch (\Exception $e) {
            return redirect()
                ->route('locations.create')
                ->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('locations.index')
            ->with('success', __('location.location_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location): View {
        return view('admin.locations.edit')
            ->with('location', $location);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse {
        $location->update([
            'name' => $request->input(['name']),
        ]);

        return redirect()
            ->route('locations.index')
            ->with('success', __('location.location_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location): RedirectResponse {
        $location->delete();

        return redirect()
            ->route('locations.index')
            ->with('success', __('location.location_deleted'));
    }

}

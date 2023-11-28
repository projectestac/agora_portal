<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class LocationController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = Location::get();

        return view('admin.locations.index')
            ->with('locations', $locations);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.locations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLocationRequest $request): RedirectResponse
    {
        $name = $request->input('name');

        $location = new Location([
            'name' => $name
        ]);

        try {
            $location->save();
        } catch (\Exception $e) {

            return redirect()->route('locations.create')
                ->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('locations.index')
            ->with('success', __('location.created_location_short'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $location = Location::findOrFail($id);
        return view('admin.locations.edit')->with('location', $location); // we send the model type to the view
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {
        $validatedData = $request->validate([
            'name' => 'required|string'
        ]);

        $location = Location::findOrFail($id);

        $location->update([
            'name' => $validatedData['name']
        ]);

        return redirect()->route('locations.index')->with('success', __('request.request_created'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return redirect()->route('locations.index')->with('success', __('common.deletion_success'));
    }
}

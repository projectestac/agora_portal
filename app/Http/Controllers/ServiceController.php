<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;

class ServiceController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(): View|Application|Factory|ApplicationContract {
        return view('admin.service.index')->with('services', Service::all());
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
    public function store(StoreServiceRequest $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequest $request, Service $service) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service) {
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequestTypeRequest;
use App\Http\Requests\UpdateRequestTypeRequest;
use App\Models\RequestType;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class RequestTypeController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(): View {

        $requestTypes = RequestType::get();

        return view('admin.request-type.index')
            ->with('requestTypes', $requestTypes);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View {
        return view('admin.request-type.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequestTypeRequest $request): RedirectResponse {

        $name = $request->input('name');
        $description = $request->input('description');
        $prompt = $request->input('prompt');

        $requestType = new RequestType([
            'name' => $name,
            'description' => $description,
            'prompt' => $prompt,
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
    public function show(RequestType $requestType) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RequestType $requestType): View {

        $requestTypeServices = DB::select('SELECT service_id FROM request_type_service WHERE request_type_id = ?', [$requestType->id]);

        // Convert an array of arrays into an array of values
        $requestTypeServices = array_map(static function ($item) {
            return $item->service_id;
        }, $requestTypeServices);

        return view('admin.request-type.edit')
            ->with('requestType', $requestType)
            ->with('associatedServices', $requestTypeServices)
            ->with('services', \App\Models\Service::get());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequestTypeRequest $request, RequestType $requestType): RedirectResponse {

        $name = $request->input('name');
        $description = $request->input('description');
        $prompt = $request->input('prompt');

        // Recover the checked services. To support a variable number of services, we need to iterate over the
        // service names and check if they are present in the request. If they are, we add them to the array of
        // services.
        $services = [];
        $serviceNames = Service::get()->pluck('name')->toArray();

        foreach ($serviceNames as $serviceName) {
            if ($request->has('service_' . $serviceName)) {
                $serviceId = Service::where('name', $serviceName)->first()->id;
                $services[] = $serviceId;
            }
        }

        // Update the request type.
        $requestType->update([
            'name' => $name,
            'description' => $description,
            'prompt' => $prompt,
        ]);

        // Update the pivot table (request_type_service).
        $requestType->services()->sync($services);

        return redirect()
            ->route('request-types.index')
            ->with('success', __('request.request_created'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequestType $requestType): RedirectResponse {

        // Delete the related values in the pivot table.
        $requestType->services()->detach();

        // Delete the request type.
        $requestType->delete();

        return redirect()
            ->route('request-types.index')
            ->with('success', __('common.deletion_success'));
    }

}

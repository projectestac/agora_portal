<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelectorRequest;
use App\Models\Instance;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SelectorController extends Controller {

    public function getSelector(Request $request, string $selectedServiceName = 'Moodle', bool $addPortal = true): array {

        $services = Service::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->toArray();

        if ($addPortal) {
            array_unshift($services, ['id' => 0, 'name' => 'Portal']);
        }

        // Add 'selected' element to indicate which service will be selected in the dropdown menu.
        $services = array_map(function ($array) use ($selectedServiceName) {
            $array['selected'] = $array['name'] === $selectedServiceName;
            return $array;
        }, $services);

        // Extract the selected service from the full list of services to pass it to the view.
        $selectedService = array_filter($services, function ($item) {
            return $item['selected'] === true;
        });
        $selectedService = reset($selectedService);

        // Get the active instances which belong to the selected service.
        if ($selectedService['id'] !== 0) {
            $instances = Instance::with('client')
                ->join('clients', 'instances.client_id', '=', 'clients.id')
                ->where('instances.status', Instance::STATUS_ACTIVE)
                ->where('instances.service_id', $selectedService['id'])
                ->orderBy('clients.name')
                ->get()
                ->toArray();
        } else {
            $instances = [];
        }

        return [
            'services' => $services,
            'selectedService' => $selectedService,
            'instances' => $instances,
        ];

    }

    public function getClients(Request $request): JsonResponse {

        $serviceSel = $request->input('servicesel');
        $order = $request->input('order');
        $search = $request->input('search');
        $textToSearch = $request->input('texttosearch');

        //dump($serviceSel, $order, $search, $textToSearch);
        $orderBy = 'clients.name';
        switch ($order) {
            case 'dbid':
                $orderBy = 'instances.db_id';
                break;
            case 'clientcode':
                $orderBy = 'clients.code';
                break;
            case 'dns':
                $orderBy = 'clients.dns';
                break;
        }

        $query = Instance::with('client')
            ->join('clients', 'instances.client_id', '=', 'clients.id')
            ->where('instances.status', Instance::STATUS_ACTIVE)
            ->where('instances.service_id', $serviceSel)
            ->orderBy($orderBy);

        if (!empty($textToSearch)) {
            switch ($search) {
                case 'code':
                    $query->where('clients.code', 'like', '%' . $textToSearch . '%');
                    break;
                case 'clientname':
                    $query->where('clients.name', 'like', '%' . $textToSearch . '%');
                    break;
                case 'town':
                    $query->where('clients.town', 'like', '%' . $textToSearch . '%');
                    break;
                case 'dns':
                    $query->where('clients.dns', 'like', '%' . $textToSearch . '%');
                    break;
                case 'dbid':
                    $query->where('instances.db_id', 'like', '%' . $textToSearch . '%');
                    break;
            }
        }

        $instances = $query->get()->toArray();

        $selectedServiceName = Service::where('id', $serviceSel)
            ->pluck('name')
            ->first();

        $template = view('admin.selector.client-select')
            ->with('viewData', [
                'instances' => $instances,
                'selectedService' => ['name' => $selectedServiceName],
            ])
            ->render();

        return response()->json(['html' => $template]);

    }

}

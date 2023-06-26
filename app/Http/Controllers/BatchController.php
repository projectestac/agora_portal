<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JsonException;

class BatchController extends Controller {
    public function batch(): RedirectResponse {
        return redirect()->route('batch.query');
    }

    public function query(Request $request): View {
        $selector = new SelectorController();
        $viewData = $selector->getSelector($request, 'Moodle');
        $query = $request->session()->get('query');

        return view('admin.batch.query')
            ->with('viewData', $viewData)
            ->with('query', $query);
    }

    /**
     * @throws JsonException
     */
    public function operation(Request $request): View {
        $selector = new SelectorController();
        $viewData = $selector->getSelector($request, 'Moodle', false);

        $operationController = new OperationController();
        $operations = $operationController->get_operations_list($viewData['selectedService']);
        $action = current($operations);

        $priority = [
            'low' => __('batch.low'),
            'medium' => __('batch.medium'),
            'high' => __('batch.high'),
            'highest' => __('batch.highest')
        ];

        return view('admin.batch.operation')
            ->with('viewData', $viewData)
            ->with('operations', $operations)
            ->with('action', $action)
            ->with('priority', $priority);
    }

    public function queue(): RedirectResponse {
        return redirect()->route('queue.pending');
    }

    public function create(): View {
        return view('admin.batch.create');
    }

}

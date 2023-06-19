<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;

class BatchController extends Controller {
    public function batch(Request $request): RedirectResponse {
        return redirect()->route('batch.query');
    }

    public function query(Request $request): View {
        $selector = new SelectorController();
        $viewData = $selector->getSelector($request, 'Nodes');
        $query = $request->session()->get('query');

        return view('admin.batch.query')
            ->with('viewData', $viewData)
            ->with('query', $query);
    }

    public function operation(Request $request): View|Application|Factory|ApplicationContract {
        return view('admin.batch.operation');
    }

    public function queue(Request $request): View|Application|Factory|ApplicationContract {
        return view('admin.batch.queue');
    }

    public function create(Request $request): View|Application|Factory|ApplicationContract {
        return view('admin.batch.create');
    }

}

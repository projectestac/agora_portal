<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;

class BatchController extends Controller {
    public function batch(Request $request): View|Application|Factory|ApplicationContract {
        return view('batch.batch');
    }

    public function query(Request $request): View|Application|Factory|ApplicationContract {
        return view('batch.query');
    }

    public function operation(Request $request): View|Application|Factory|ApplicationContract {
        return view('batch.operation');
    }

    public function queue(Request $request): View|Application|Factory|ApplicationContract {
        return view('batch.queue');
    }

    public function create(Request $request): View|Application|Factory|ApplicationContract {
        return view('batch.create');
    }

}

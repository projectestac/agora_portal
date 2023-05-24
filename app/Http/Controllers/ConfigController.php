<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class ConfigController extends Controller {
    public function settings(): View|Application|Factory|ApplicationContract {
        return view('config.settings');
    }
}

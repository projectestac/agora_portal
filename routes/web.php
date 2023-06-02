<?php

use App\Http\Controllers\BatchController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientTypeController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\InstanceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ModelTypeController;
use App\Http\Controllers\MyAgoraController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RequestTypeController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', static function () {
    return view('home');
})->name('home');

// Routes for administrators only.
Route::group(['middleware' => ['auth', 'permission:Administrate site']], static function () {
    Route::resource('/services', ServiceController::class);
    Route::resource('/clients', ClientController::class);

    Route::get('/batch', [BatchController::class, 'batch'])->name('batch');
    Route::get('/batch/query', [BatchController::class, 'query'])->name('query');
    Route::get('/batch/queue', [BatchController::class, 'queue'])->name('queue');
    Route::get('/batch/operation', [BatchController::class, 'operation'])->name('operation');
    Route::get('/batch/create', [BatchController::class, 'create'])->name('create');

    Route::get('/config', [ConfigController::class, 'settings'])->name('config');
    Route::resource('/config/models', ModelTypeController::class);
    Route::resource('/config/request-types', RequestTypeController::class);
    Route::resource('/config/locations', LocationController::class);
    Route::resource('/config/client-types', ClientTypeController::class);
});

Route::group(['middleware' => ['auth', 'permission:Administrate site|Manage own managers|Manage clients']], static function () {
    Route::resource('/managers', ManagerController::class);
    Route::resource('/requests', RequestController::class);
    Route::resource('/instances', InstanceController::class);
    Route::get('/myagora', [MyAgoraController::class, 'myagora'])->name('myagora');
    Route::get('/myagora/instances', [MyAgoraController::class, 'instances'])->name('myagora.instances');
    Route::get('/myagora/files', [MyAgoraController::class, 'files'])->name('myagora.files');
    Route::get('/myagora/requests', [MyAgoraController::class, 'requests'])->name('myagora.requests');
    Route::get('/myagora/managers', [MyAgoraController::class, 'managers'])->name('myagora.managers');
    Route::get('/myagora/logs', [MyAgoraController::class, 'logs'])->name('myagora.logs');
});

// AJAX routes
Route::get('/myagora/request/details', [MyAgoraController::class, 'getRequestDetails']);

require __DIR__.'/auth.php';

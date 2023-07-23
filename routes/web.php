<?php

use App\Http\Controllers\BatchController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientTypeController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\QueryController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\InstanceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ModelTypeController;
use App\Http\Controllers\MyAgoraController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RequestTypeController;
use App\Http\Controllers\SelectorController;
use App\Http\Controllers\ServiceController;
use App\Mail\UpdateRequest;
use Illuminate\Support\Facades\Mail;
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

Route::resource('/queries', QueryController::class);

// Routes for administrators only.
Route::group(['middleware' => ['auth', 'permission:Administrate site']], static function () {
    Route::resource('/services', ServiceController::class);
    Route::resource('/clients', ClientController::class);

    Route::get('/batch', [BatchController::class, 'batch'])->name('batch');
    Route::get('/batch/query', [BatchController::class, 'query'])->name('batch.query');
    Route::post('/batch/query/confirm', [QueryController::class, 'confirmQuery'])->name('batch.query.confirm');
    Route::post('/batch/query/exec', [QueryController::class, 'executeQuery'])->name('batch.query.exec');
    Route::get('/batch/operation', [BatchController::class, 'operation'])->name('operation');
    Route::get('/batch/operation/{action}', [OperationController::class, 'getOperationHtml'])->name('batch.operation');
    Route::post('/batch/operation/{action}/confirm', [OperationController::class, 'confirmOperation'])->name('batch.operation.confirm');
    Route::post('/batch/operation/enqueue', [OperationController::class, 'enqueue'])->name('batch.operation.program');
    Route::get('/batch/queue', [BatchController::class, 'queue'])->name('queue');
    Route::get('/batch/queue/pending', [QueueController::class, 'getPending'])->name('queue.pending');
    Route::get('/batch/queue/success', [QueueController::class, 'getSuccess'])->name('queue.success');
    Route::get('/batch/queue/fail', [QueueController::class, 'getFail'])->name('queue.fail');
    Route::delete('/batch/queue/{id}', [QueueController::class, 'destroy'])->name('queue.destroy');
    Route::get('/batch/create', [BatchController::class, 'create'])->name('create');

    Route::get('/config', [ConfigController::class, 'edit'])->name('config.edit');
    Route::put('/config', [ConfigController::class, 'update'])->name('config.update');
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
    Route::get('/myagora/requests', [MyAgoraController::class, 'requests'])->name('myagora.requests');
    Route::get('/myagora/managers', [MyAgoraController::class, 'managers'])->name('myagora.managers');
    Route::get('/myagora/logs', [MyAgoraController::class, 'logs'])->name('myagora.logs');
});

// AJAX routes
Route::get('/myagora/request/details', [MyAgoraController::class, 'getRequestDetails']);
Route::get('/search', [SelectorController::class, 'getClients'])->name('search');

// Files routes
Route::group(['middleware' => ['auth', 'permission:Administrate site|Manage clients']], static function () {
    Route::get('/myagora/files', [MyAgoraController::class, 'files'])->name('myagora.files');
    Route::get('/myagora/file/download', [MyAgoraController::class, 'downloadFile'])->name('myagora.file.download');
    Route::get('/myagora/file/delete', [MyAgoraController::class, 'deleteFile'])->name('myagora.file.delete');
    Route::get('/myagora/quota/recalc', [MyAgoraController::class, 'recalcQuota'])->name('myagora.quota.recalc');
    Route::post('/upload', [MyAgoraController::class, 'uploadFile'])->name('upload');
});

require __DIR__ . '/auth.php';

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
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\SelectorController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DirectoryController;
use Illuminate\Support\Facades\Route;
use App\Models\Service;
use App\Models\Location;
use App\Models\ClientType;

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
    $services = Service::all();
    $locations = Location::all();
    $clientTypes = ClientType::all();

    return view('home', compact('services', 'locations', 'clientTypes'));
})->name('home');

Route::resource('/queries', QueryController::class);

// AJAX routes for datatables. Must be before the resource route.
Route::get('/clients/list', [ClientController::class, 'getClients'])->name('clients.list');
Route::get('/clients/active-list', [ClientController::class, 'getActiveClients'])->name('clients.active.list');
Route::get('/instances/list', [InstanceController::class, 'getInstances'])->name('instances.list');
Route::get('/users/list', [UserController::class, 'getUsers'])->name('users.list');
Route::get('/managers/list', [ManagerController::class, 'getManagers'])->name('managers.list');

// Routes for administrators only.
Route::group(['middleware' => ['auth', 'permission:Administrate site']], static function () {
    Route::get('/clients/import', [ClientController::class, 'import'])->name('clients.import');
    Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search');

    /*
    Route::get('/users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate');
    Route::get('/users/{user}/stop-impersonating', [UserController::class, 'stopImpersonating'])->name('users.stop-impersonating');
    */

    Route::resource('/services', ServiceController::class);
    Route::resource('/clients', ClientController::class);
    Route::resource('/users', UserController::class);
    Route::resource('/roles', RoleController::class);

    Route::get('role/{role}', [RoleController::class, 'show'])->name('role.show');
    Route::get('role/{role}/edit', [RoleController::class, 'edit'])->name('role.edit');
    Route::put('role/{role}', [RoleController::class, 'update'])->name('role.update');
    Route::delete('role/{role}', [RoleController::class, 'destroy'])->name('role.destroy');
    Route::post('/roles/store', [RoleController::class, 'store'])->name('roles.store');

    Route::put('manager/{manager}', [ManagerController::class, 'update'])->name('manager.update');
    Route::post('/managers/store_new', [ManagerController::class, 'storeNew'])->name('managers.store_new');

    Route::get('/stats', [StatisticsController::class, 'showStats'])->name('stats.show');
    Route::get('/stats/{service}/{periodicity}', [StatisticsController::class, 'showTabStats'])->name('stats.showTabStats');
    Route::get('/stats/export/{service}/{periodicity}', [StatisticsController::class, 'exportTabStats'])->name('stats.exportTabStats');

    Route::get('/batch', [BatchController::class, 'batch'])->name('batch');
    Route::get('/batch/query', [BatchController::class, 'query'])->name('batch.query');
    Route::post('/batch/query/confirm', [QueryController::class, 'confirmQuery'])->name('batch.query.confirm');
    Route::post('/batch/query/exec', [QueryController::class, 'executeQuery'])->name('batch.query.exec');

    Route::get('/batch/operation', [BatchController::class, 'operation'])->name('operation');
    Route::get('/batch/operation/{action}', [OperationController::class, 'getOperationHtml'])->name('batch.operation');
    Route::post('/batch/operation/{action}/confirm', [OperationController::class, 'confirmOperation'])->name('batch.operation.confirm');
    Route::post('/batch/operation/enqueue', [OperationController::class, 'enqueue'])->name('batch.operation.program');
    Route::post('/batch/operation/enqueueFromInputs', [OperationController::class, 'enqueueFromInputs'])->name('batch.operation.enqueueFromInputs');

    Route::get('/batch/queue', [BatchController::class, 'queue'])->name('queue');
    Route::get('/batch/queue/pending', [QueueController::class, 'getPending'])->name('queue.pending');
    Route::get('/batch/queue/success', [QueueController::class, 'getSuccess'])->name('queue.success');
    Route::get('/batch/queue/fail', [QueueController::class, 'getFail'])->name('queue.fail');
    Route::delete('/batch/queue/{id}', [QueueController::class, 'destroy'])->name('queue.destroy');
    Route::get('/batch/queue/{id}/execute', [QueueController::class, 'execute'])->name('queue.execute');

    Route::get('/batch/instance/create', [BatchController::class, 'instanceCreate'])->name('batch.instance.create');
    Route::post('/batch/instance', [BatchController::class, 'instanceStore'])->name('batch.instance.store');
    Route::get('/batch/instances', [BatchController::class, 'instancesList'])->name('batch.instances.list');
    Route::get('/batch/instances/data', [BatchController::class, 'getInstancesData'])->name('batch.instances.data');
    Route::post('/batch/instances/update-status', [BatchController::class, 'updateStatus'])->name('batch.instances.updateStatus');

    Route::get('/files/{path?}', [DirectoryController::class, 'index'])->name('files.index');
    Route::get('/files/download/{path}', [DirectoryController::class, 'download'])->name('files.download');
    Route::post('/files/upload/{currentPath}', [DirectoryController::class, 'upload'])->name('files.upload');
    Route::delete('/files/delete/{path}/{file}', [DirectoryController::class, 'destroy'])->name('files.destroy');

    Route::get('/config', [ConfigController::class, 'edit'])->name('config.edit');
    Route::put('/config', [ConfigController::class, 'update'])->name('config.update');

    Route::resource('/config/model-types', ModelTypeController::class);
    Route::resource('/config/request-types', RequestTypeController::class);
    Route::resource('/config/locations', LocationController::class);
    Route::resource('/config/client-types', ClientTypeController::class);

    Route::get('/quotas/update', [InstanceController::class, 'updateQuotas'])->name('update.quotas');
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
    Route::post('/myagora/changedns', [MyAgoraController::class, 'changeDNS'])->name('myagora.changedns');
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

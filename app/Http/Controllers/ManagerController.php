<?php

namespace App\Http\Controllers;

use App\Helpers\Access;
use App\Helpers\Cache;
use App\Http\Requests\StoreManagerRequest;
use App\Http\Requests\UpdateManagerRequest;
use App\Models\Client;
use App\Models\Log;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class ManagerController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(): View {
        return view('admin.manager.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreManagerRequest $request): RedirectResponse {

        $username = mb_strtolower($request->get('username'));

        // $username must have the form aginard, and $email must have the form aginard@xtec.cat.
        if (!str_contains($username, '@xtec.cat')) {
            // Username is correct, so the email must be created by adding the domain.
            $email = $username . '@xtec.cat';
        } else {
            // Username contains the email, so the domain must be removed.
            $email = $username;
            $username = str_replace('@xtec.cat', '', $username);
        }

        // Look for the user in the database.
        $user = User::where('name', $username)->first();

        // If the user does not exist, create it.
        if (is_null($user)) {
            $user = new User([
                'name' => $username,
                'email' => $email,
                'password' => '',
            ]);
            $user->save();
        }

        if (Access::isClient($user)) {
            return redirect()->back()->withErrors(__('manager.clients_cannot_be_managers'));
        }

        $currentClient = Cache::getCurrentClient($request);

        if (Manager::where('user_id', $user->id)->where('client_id', $currentClient['id'])->exists()) {
            return redirect()->back()->withErrors(__('manager.manager_already_exists'));
        }

        // Add the register to table "managers".
        $manager = new Manager([
            'user_id' => $user->id,
            'client_id' => $currentClient['id'],
        ]);
        $manager->save();

        // Add the register to table "model_has_roles".
        $this->setManagerPermissions($username);

        Log::insert([
            'client_id' => $currentClient['id'],
            'user_id' => Auth::user()->id,
            'action_type' => Log::ACTION_TYPE_ADD,
            'action_description' => __('manager.manager_added_detail', ['username' => $manager->user->name]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', __('manager.manager_added'));

    }

    /**
     * Display the specified resource.
     */
    public function show(Manager $manager) {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Manager $manager) {
        $clients = Client::all();
        $users = User::all();

        return view('admin.manager.edit')
            ->with('manager', $manager)
            ->with('clients', $clients)
            ->with('users', $users);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateManagerRequest $request, Manager $manager) {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Manager $manager): RedirectResponse {

        // Remove register from table "managers".
        $manager->delete();

        // Remove register from table "model_has_roles".
        $this->removeManagerPermissions($manager->user->name);

        Log::insert([
            'client_id' => $manager->client_id,
            'user_id' => Auth::user()->id,
            'action_type' => Log::ACTION_TYPE_DELETE,
            'action_description' => __('manager.manager_removed_detail', ['username' => $manager->user->name]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', __('manager.manager_removed'));

    }

    public function setManagerPermissions(string $username): void {
        $user = User::where('name', $username)->first();
        $managerRole = Role::findByName('manager');
        $user->assignRole($managerRole);
    }

    public function removeManagerPermissions(string $username): void {
        $user = User::where('name', $username)->first();
        $managerRole = Role::findByName('manager');
        $user->removeRole($managerRole);
    }

    public function getManagers(Request $request): JsonResponse {

        $manager = Manager::select(['managers.*', 'clients.code'])
            ->leftJoin('clients', 'managers.user_id', '=', 'clients.id')
            ->leftJoin('users', 'managers.user_id', '=', 'users.id');

        $manager = $manager->get();

        return DataTables::make($manager)
            ->rawColumns(['id', 'client_name', 'user_name', 'assigned'])
            ->addColumn('client_name', function ($manager) {
                return new HtmlString('<a href="' . route('myagora.instances', ['code' => $manager->client->code]) . '">' .
                    $manager->client->name . '</a><br/>' . $manager->client->dns . ' - ' . $manager->client->code);
            })
            ->addColumn('user_name', function ($manager) {
                return new HtmlString('<span>' . $manager->user->name . '</span>');
            })
            ->addColumn('assigned', function ($manager) {
                return new HtmlString('<span>' . $manager->created_at->format('d/m/Y H:i') . '</span>');
            })
            ->addColumn('actions', static function ($manager) {
                return view('admin.manager.action', ['manager' => $manager]);
            })
            ->make(true);

    }

}

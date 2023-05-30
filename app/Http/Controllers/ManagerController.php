<?php

namespace App\Http\Controllers;

use App\Helpers\Cache;
use App\Http\Requests\StoreManagerRequest;
use App\Http\Requests\UpdateManagerRequest;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class ManagerController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
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
        $user = User::where('email', $email)->first();

        // If the user does not exist, create it.
        if (is_null($user)) {
            $user = new User([
                'name' => $username,
                'email' => $email,
                'password' => '',
            ]);
            $user->save();
        }

        $current_client = Cache::get_current_client($request);

        if (Manager::where('user_id', $user->id)->where('client_id', $current_client['id'])->exists()) {
            return redirect()->back()->withErrors(__('manager.manager_already_exists'));
        }

        $manager = new Manager([
            'user_id' => $user->id,
            'client_id' => $current_client['id'],
        ]);
        $manager->save();

        return redirect()->back()->with('success', __('manager.manager_added'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Manager $manager) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Manager $manager) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateManagerRequest $request, Manager $manager) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Manager $manager) {
        $manager->delete();
        return redirect()->back()->with('success', __('manager.manager_removed'));
    }
}

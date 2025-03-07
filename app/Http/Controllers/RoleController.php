<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\View\View;

class RoleController extends Controller {
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View {
        $roles = \Spatie\Permission\Models\Role::paginate(25);
        return view('admin.role.index')->with('roles', $roles);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $role = Role::findOrFail($id);
        return view('admin.role.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        return view('admin.role.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->update($request->only('name', 'guard_name'));

        return redirect()->route('admin.role.index')->with('success', 'Rôle mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role) {
    }
}

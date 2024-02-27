<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class UserController extends Controller {
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View {
        return view('admin.user.index');
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
    public function show(User $user) {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View {
        $roles = Role::pluck('name','id');
        $assignedRoles = $user->getRoleNames()->toArray();

        return view('admin.user.edit')
            ->with('user', $user)
            ->with('roles', $roles)
            ->with('assignedRoles', $assignedRoles);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse {
        // Validate the fields: name, email, and password (if provided).
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'nullable|string|confirmed', // Password is nullable, confirmed
        ]);

        $beforeUpdate = $user->toArray();

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Check if a new password is provided and if so, update it
        if ($request->filled('password')) {
            // The password and password_confirmation are already validated by 'confirmed' rule
            $user->password = bcrypt($validated['password']); // Hash the new password before saving
        }

        // Save the updated user
        $user->save();

        $beforeRoles = $user->roles->pluck('name')->toArray();

        if ($request->has('roles')) {
            $user->syncRoles($request->input('roles'));
        }

        $afterRoles = $user->roles->pluck('name')->toArray();

        $rolesUpdated = !empty(array_diff($beforeRoles, $afterRoles));

        $afterUpdate = $user->toArray();

        $isUpdated = !empty(array_diff_assoc($beforeUpdate, $afterUpdate));

        if ($isUpdated || $rolesUpdated) {
            $type = 'success';
            $message = __('user.user_updated');
        } else {
            $type = 'error';
            $message = __('user.user_noUpdated');
        }

        // Redirect back with a success or error message
        return redirect()->route('users.index')->with($type, $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user) {
        $user->syncRoles([]);
        $user->deleted_at = Carbon::now();
        $user->save();
        return redirect()->back();
    }

    public function getUsers(Request $request): JsonResponse {

        $search = $request->validate(['search.value' => 'string|max:50|nullable']);
        $searchValue = $search['search']['value'] ?? '';
        $users = User::select(['users.*']);

        if (!empty($searchValue)) {
            $users = $users->where('name', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('email', 'LIKE', '%' . $searchValue . '%')
                ->orWhereHas('roles', function ($query) use ($searchValue) {
                    // https://laravel.com/docs/10.x/eloquent-relationships#querying-relationship-existence
                    $query->where('name', 'LIKE', '%' . $searchValue . '%');
                });
        }

        $users = $users->get();

        return DataTables::make($users)
            ->addColumn('name', function ($user) {
                return new HtmlString('<span>' . $user->name . '</span>');
            })
            ->addColumn('email', function ($user) {
                return new HtmlString('<span>' . $user->email . '</span>');
            })
            ->addColumn('roles', function ($user) {
                $roles = $user->getRoleNames();
                $rolesString = '';
                foreach ($roles as $role) {
                    $rolesString .= ucfirst($role) . '<br />';
                }
                return new HtmlString('<span>' . $rolesString . '</span>');
            })
            ->addColumn('actions', static function ($user) {
                return view('admin.user.action', ['user' => $user]);
            })
            ->rawColumns(['id', 'name', 'email', 'roles'])
            ->make(true);
    }
}

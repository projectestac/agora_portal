<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Yajra\DataTables\Facades\DataTables;

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
        return view('admin.user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        return redirect()->route('users.index')->with('success', __('user.user_created'));
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
    public function update(Request $request, User $user) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255'
        ]);

        $beforeUpdate = $user->toArray();

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        if ($request->has('roles')) {
            $user->syncRoles($request->input('roles'));
        }

        $afterUpdate = $user->toArray();

        $isUpdated = !empty(array_diff_assoc($beforeUpdate, $afterUpdate));

        if ($isUpdated) {
            $type = 'success';
            $message = __('user.user_updated');
        }else{
            $type = 'error';
            $message = __('user.user_noUpdated');
        }
        return redirect()->back()->with($type, $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user) {
        // Check if the user is referenced in the managers table
        $managerExists = DB::table('managers')->where('user_id', $user->id)->exists();

        if ($managerExists) {
            return redirect()->route('users.index')->with('error', __('user.cannot_delete_user_has_managers'));
        }

        // Delete the user if they are not referenced
        $user->delete();

        return redirect()->route('users.index')->with('success', __('user.user_deleted'));
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

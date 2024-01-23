<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
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
    public function edit(User $user) {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $id) {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user) {
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

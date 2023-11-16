<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use App\Models\User;
use App\Models\RoleHasModel;
use App\Models\Manager;
use Yajra\DataTables\Facades\DataTables;

class UserManagementController extends Controller
{
    public function users(): View {
        return view('admin.user.index');
    }

    public function getUsers(): JsonResponse {
        $user = User::orderBy('updated_at', 'desc');
        return DataTables::make($user)
            ->rawColumns(['id'])
            ->addColumn('name', function ($user){
                return new HtmlString('<a>'.$user->name.'</a>');
            })
            ->addColumn('email', function ($user){
                return new HtmlString('<a>'.$user->email.'</a>');
            })
            ->addColumn('actions', static function ($user) {
                return view('admin.user.action', ['user' => $user]);
            })
            ->make();
    }

    public function roles(){
        $roles = RoleHasModel::paginate(10);
        return view('admin.role.index', compact('roles'));
    }

    public function managers(): View {
        return view('admin.manager.index');
    }

    public function getManagers(){
        $manager = Manager::orderBy('updated_at', 'desc');
        return DataTables::make($manager)
        ->rawColumns(['id'])
        ->addColumn('client_id', function ($manager){
            return new HtmlString('<a>'.$manager->client_id.'</a>');
        })
        ->addColumn('client_name', function ($manager){
            return new HtmlString('<a>'.$manager->client->name.'</a>');
        })
        ->addColumn('client_code', function ($manager){
            return new HtmlString('<a>'.$manager->client->code.'</a>');
        })
        ->addColumn('client_dns', function ($manager){
            return new HtmlString('<a>'.$manager->client->dns.'</a>');
        })
        ->addColumn('user_name', function ($manager){
            return new HtmlString('<a>'.$manager->user->name.'</a>');
        })
        ->addColumn('actions', static function ($manager) {
            return view('admin.manager.action', ['manager' => $manager]);
        })
        ->make();
    }
}

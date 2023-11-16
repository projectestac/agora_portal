@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content users">
        <h3>Llista de rols</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Role_ID</th>
                    <th>Role_name</th>
                    <th>Model_type</th>
                    <th>User_ID</th>
                    <th>User</th>
                    <th>{{ __('common.actions') }}</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr>
                        <td>{{ $role->role->id }}</td>
                        <td title="{{ $role->role }}">{{ $role->role->name }}</td>
                        <td>{{ $role->model_type }}</td>
                        <td>{{ $role->model_id }}</td>
                        <td title="{{ $role->user }}">{{ $role->user->name }} ({{ $role->user->email }})</td>
                        <td>
                            <a href="#" class="btn btn-info" title="{{ __('common.show') }}">
                                <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
                            </a>
                            <a href="#" class="btn btn-primary" title="{{ __('common.edit') }}">
                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                            </a>
                            <form action="#" method="POST" style="display: inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" title="{{ __('common.delete') }}">
                                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="text-align:center;">
            {{ $roles->links() }}
        </div>
    </div>
@endsection

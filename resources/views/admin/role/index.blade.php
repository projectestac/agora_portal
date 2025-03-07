@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content users">
        <h3>{{ __('user.roles_list') }}</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ __('common.id') }}</th>
                    <th>{{ __('common.name') }}</th>
                    <th>{{ __('user.guard') }}</th>
                    <th>{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr>
                        <td>{{ $role->id }}</td>
                        <td>{{ $role->name }}</td>
                        <td>{{ $role->guard_name }}</td>
                        <td>
                            <a href="{{ route('role.show', $role->id) }}" class="btn btn-info" title="{{ __('common.show') }}">
                                <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
                            </a>
                            <a href="{{ route('role.edit', $role->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                            </a>
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

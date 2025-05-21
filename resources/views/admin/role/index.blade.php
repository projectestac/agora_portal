@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content users">
        <h3>{{ __('user.roles_list') }}</h3>

        <div class="row">
            <div class="col-md-6">
                <a href="{{ route('roles.create') }}" class="btn btn-primary">{{ __('role.add_role') }}</a>
            </div>
        </div>

        <br>

        @include('components.messages')

        <table class="table table-striped" style="max-width: 1000px; margin: 0 auto;">
            <thead>
                <tr>
                    <th>{{ __('common.id') }}</th>
                    <th>{{ __('common.name') }}</th>
                    <th>{{ __('user.guard') }}</th>
                    <th style="text-align: right; padding-right: 50px;">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr @if($role->deleted_at) style="background-color: #ffcccc;" @endif>
                        <td>{{ $role->id }}</td>
                        <td>{{ $role->name }}</td>
                        <td>{{ $role->guard_name }}</td>
                        <td style="text-align: right; margin-right: 20px;">

                            @if (!$role->deleted_at)
                                <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                </a>

                                @include('components.confirm-delete-modal', [
                                    'id' => $role->id,
                                    'name' => $role->name,
                                    'route' => route('roles.destroy', $role->id)
                                ])
                            @else
                                @include('components.confirm-restore-modal', [
                                    'id' => $role->id,
                                    'restoreRoute' => route('roles.restore', $role->id)
                                ])
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

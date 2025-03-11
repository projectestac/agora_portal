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
                            <a href="{{ route('role.edit', $role->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                            </a>

                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#confirmDeleteModal{{ $role->id }}">
                                <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                            </button>

                            <div class="modal fade" id="confirmDeleteModal{{ $role->id }}" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel{{ $role->id }}" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h2 class="modal-title text-danger" id="confirmDeleteModalLabel{{ $role->id }}">{{ __('role.warning_delete') }}</h2>
                                        </div>
                                        <div class="modal-body">
                                            {!! __('role.confirm_delete', ['role' => '<strong>' . $role->name . '</strong>']) !!}
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.cancel') }}</button>
                                            <form action="{{ route('role.destroy', $role->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">{{ __('role.delete_role') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

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

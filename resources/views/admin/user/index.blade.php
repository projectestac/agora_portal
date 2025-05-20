@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content users">
        <h3>{{ __('user.users_list') }}</h3>
        @include('components.messages')

        <div class="row">
            <div class="col-md-6">
                <a href="{{ route('users.create') }}" class="btn btn-primary">{{ __('user.add_user') }}</a>
            </div>
        </div>

        <br>

        <table class="table table-striped" id="user-list">
            <thead>
            <tr>
                <th>{{ __('common.id') }}</th>
                <th>{{ __('user.name') }}</th>
                <th>{{ __('user.email') }}</th>
                <th>{{ __('user.roles') }}</th>
                <th>{{ __('user.last_login_at') }}</th>
                <th>{{ __('user.assigned_clients') }}</th>
                <th style="text-align: right; padding-right: 50px;">{{ __('common.actions') }}</th>
            </tr>
            </thead>
        </table>
    </div>

    <script>
        $(function () {
            $('#user-list').DataTable({
                processing: true,
                serverSide: true,
                language: {
                    url: '{{ url('/datatable/ca.json') }}'
                },
                lengthMenu: [10, 25, 50, 100, 250],
                pageLength: 25,
                ajax: '{{ route('users.list') }}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'email', name: 'email'},
                    {data: 'roles', name: 'roles'},
                    {data: 'last_login_at', name: 'last_login_at'},
                    {data: 'manages_clients', name: 'manages_clients'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false}
                ],
                "createdRow": function (row, data, dataIndex) {
                    if (data.deleted_at) {
                        $(row).css('background-color', '#ffcccc');
                    }
                }
            });
        });
    </script>

@endsection

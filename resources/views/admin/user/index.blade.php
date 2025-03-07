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
                    <th>{{ __('common.actions') }}</th>
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
                    {data: 'actions', name: 'actions', orderable: false, searchable: false}
                ]
            });
        });
    </script>

@endsection

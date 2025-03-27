@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content users">
        <h3>{{ __('manager.manager_list') }}</h3>

        <div class="row">
            <div class="col-md-6">
                <a href="{{ route('managers.create') }}" class="btn btn-primary">{{ __('manager.add_manager') }}</a>
            </div>
        </div>

        <br>

        @include('components.messages')

        <table class="table table-striped" id="manager-list">
            <thead>
                <tr>
                    <th>{{ __('common.id') }}</th>
                    <th>{{ __('user.user') }}</th>
                    <th>{{ __('client.client') }}</th>
                    <th>{{ __('manager.manager_added_date') }}</th>
                    <th>{{ __('common.actions') }}</th>

                </tr>
            </thead>
        </table>
    </div>

    <script>
        $(function () {
            $('#manager-list').DataTable({
                processing: true,
                serverSide: true,
                language: {
                    url: '{{ url('/datatable/ca.json') }}'
                },
                lengthMenu: [10, 25, 50, 100, 250],
                pageLength: 25,
                ajax: '{{ route('managers.list') }}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'user_name', name: 'user_name'},
                    {data: 'client_name', name: 'client_name'},
                    {data: 'assigned', name: 'assigned', orderable: true, searchable: false},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false}
                ]
            });
        });
    </script>

@endsection

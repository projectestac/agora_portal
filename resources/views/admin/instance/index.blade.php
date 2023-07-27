@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <h3>{{ __('instance.instance_list') }}</h3>

        @include('components.messages')

        <table class="table table-striped" id="instance-list">
            <thead>
            <tr>
                <th>{{ __('common.id') }}</th>
                <th>{{ __('client.name') }}</th>
                <th>{{ __('instance.db_id') }}</th>
                <th>{{ __('common.type') }}</th>
                <th>{{ __('common.status') }}</th>
                <th>{{ __('service.service') }}</th>
                <th>{{ __('instance.location_long') }}</th>
                <th>{{ __('common.dates') }}</th>
                <th>{{ __('common.actions') }}</th>
            </tr>
            </thead>
        </table>

        <script>
            $(function () {
                $('#instance-list').DataTable({
                    processing: true,
                    serverSide: false,
                    language: {
                        url: '{{ url('/datatable/ca.json') }}'
                    },
                    lengthMenu: [10, 25, 50, 100, 250],
                    pageLength: 25,
                    ajax: '{{ route('instances.list') }}',
                    columns: [
                        {data: 'id', name: 'id'},
                        {data: 'client_name', name: 'client_name'},
                        {data: 'db_id', name: 'db_id'},
                        {data: 'type', name: 'type'},
                        {data: 'status', name: 'status'},
                        {data: 'service', name: 'service'},
                        {data: 'location', name: 'location'},
                        {data: 'dates', name: 'dates'},
                        {data: 'actions', name: 'actions', orderable: false, searchable: false}
                    ]
                });
            });
        </script>

    </div>
@endsection

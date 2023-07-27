@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <h3>{{ __('client.client_list') }}</h3>

        @include('components.messages')

        <table class="table table-striped" id="client-list">
            <thead>
            <tr>
                <th>{{ __('common.id') }}</th>
                <th>{{ __('client.name') }}</th>
                <th>{{ __('client.code') }}</th>
                <th>{{ __('service.services') }}</th>
                <th>{{ __('client.dns') }}</th>
                <th>{{ __('client.old_dns') }}</th>
                <th>{{ __('common.status') }}</th>
                <th>{{ __('common.description') }}</th>
                <th>{{ __('common.actions') }}</th>
            </tr>
            </thead>
        </table>

        <script>
            $(function () {
                $('#client-list').DataTable({
                    processing: true,
                    serverSide: false,
                    language: {
                        url: '{{ url('/datatable/ca.json') }}'
                    },
                    lengthMenu: [10, 25, 50, 100, 250],
                    pageLength: 25,
                    ajax: '{{ route('clients.list') }}',
                    columns: [
                        {data: 'id', name: 'id'},
                        {data: 'name', name: 'name'},
                        {data: 'code', name: 'code'},
                        {data: 'services', name: 'services'},
                        {data: 'dns', name: 'dns'},
                        {data: 'old_dns', name: 'old_dns'},
                        {data: 'status', name: 'status'},
                        {data: 'description', name: 'description'},
                        {data: 'actions', name: 'actions', orderable: false, searchable: false}
                    ]
                });
            });
        </script>

    </div>
@endsection

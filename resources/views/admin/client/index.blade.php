@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content client">
        <h3>{{ __('client.client_list') }}</h3>

        @include('components.messages')

        <div class="row mb-3" id="filters-container">
            <div class="col-md-2">
                <select id="service-filter" class="form-control">
                    <option value="">{{ __('service.service') }}</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select id="status-filter" class="form-control">
                    <option value="">{{ __('common.status') }}</option>
                    @foreach($statusList as $key => $status)
                        <option value="{{ $key }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select id="visible-filter" class="form-control">
                    <option value="">{{ __('client.visible') }}</option>
                    <option value="yes">{{ __('common.yes') }}</option>
                    <option value="no">{{ __('common.no') }}</option>
                </select>
            </div>

            @include('components.reset-filters', [
                'filtersContainerId' => 'filters-container',
                'datatableId' => 'client-list'
            ])
        </div>

        <br />

        <div class="row">
            <div class="col-md-6">
                <a href="{{ route('clients.create') }}" class="btn btn-primary">{{ __('client.new_client') }}</a>
            </div>
        </div>

        <br />

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
                <th>{{ __('client.visible') }}</th>
                <th>{{ __('common.dates') }}</th>
                <th>{{ __('common.actions') }}</th>
            </tr>
            </thead>
        </table>

        <script>
            $(function () {
                var table = $('#client-list').DataTable({
                    processing: true,
                    serverSide: true,
                    language: {
                        url: '{{ url('/datatable/ca.json') }}'
                    },
                    lengthMenu: [10, 25, 50, 100, 250],
                    pageLength: 25,
                    ajax: {
                        url: '{{ route('clients.list') }}',
                        data: function (d) {
                            d.service = $('#service-filter').val();
                            d.status = $('#status-filter').val();
                            d.visible = $('#visible-filter').val();
                        }
                    },
                    columns: [
                        {data: 'id', name: 'id'},
                        {data: 'name', name: 'name'},
                        {data: 'code', name: 'code'},
                        {data: 'services', name: 'services'},
                        {data: 'dns', name: 'dns'},
                        {data: 'old_dns', name: 'old_dns'},
                        {data: 'status', name: 'status'},
                        {data: 'visible', name: 'visible'},
                        {data: 'dates', name: 'dates'},
                        {data: 'actions', name: 'actions', orderable: false, searchable: false}
                    ]
                });

                $('#service-filter, #status-filter, #visible-filter').on('change', function () {
                    table.ajax.reload();
                });
            });
        </script>

    </div>
@endsection

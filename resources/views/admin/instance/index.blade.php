@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <h3>{{ __('instance.instance_list') }}</h3>

        @include('components.messages')

        <div class="row">
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
        </div>

        <br>

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
                <th>{{ __('service.quota') }}</th>
                <th>{{ __('common.dates') }}</th>
                <th>{{ __('common.actions') }}</th>
            </tr>
            </thead>
        </table>

        <script>
            $(function () {
                var table = $('#instance-list').DataTable({
                    processing: true,
                    serverSide: true,
                    language: {
                        url: '{{ url('/datatable/ca.json') }}'
                    },
                    lengthMenu: [10, 25, 50, 100, 250],
                    pageLength: 25,
                    ajax: {
                        url: '{{ route('instances.list') }}',
                        data: function (d) {
                            d.service = $('#service-filter').val();
                            d.status = $('#status-filter').val();
                        }
                    },
                    columns: [
                        {data: 'id', name: 'id'},
                        {data: 'client_name', name: 'client_name'},
                        {data: 'db_id', name: 'db_id'},
                        {data: 'type', name: 'type'},
                        {data: 'status', name: 'status'},
                        {data: 'service_id', name: 'service_id'},
                        {data: 'location', name: 'location'},
                        {data: 'quota', name: 'quota'},
                        {data: 'updated_at', name: 'updated_at'},
                        {data: 'actions', name: 'actions', orderable: false, searchable: false}
                    ]
                });

                $('#service-filter, #status-filter').on('change', function () {
                    table.ajax.reload();
                });
            });
        </script>

    </div>
@endsection

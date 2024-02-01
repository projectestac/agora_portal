@extends('layout.default')

@section('content')

    @auth()
        <div class="myagora-menu-container">
            @include('menu.clientmenu')
        </div>
    @endauth

    <h3>{{ __('home.active_instances') }}</h3>

    <table class="table table-striped" id="client-list">
        <thead>
            <tr>
                <th>
                    <select class="form-control filter" data-column="0" id="filter-location">
                        <option value="">{{ __('home.filter_by_location') }}</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </th>
                <th>
                    <select class="form-control filter" data-column="1" id="filter-clientType">
                        <option value="">{{ __('home.filter_by_client_type') }}</option>
                        @foreach($clientTypes as $clientType)
                            <option value="{{ $clientType->id }}">{{ $clientType->name }}</option>
                        @endforeach
                    </select>
                </th>
                <th>
                    <select class="form-control filter" data-column="2" id="filter-service">
                        <option value="">{{ __('home.filter_by_service') }}</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </th>
            </tr>
            <tr>
                <th>{{ __('common.name') }}</th>
                <th>{{ __('client.city') }}</th>
                <th>{{ __('instance.instances') }}</th>
            </tr>
        </thead>
    </table>

    <script>
        let table;

        $(function () {
            table = $('#client-list').DataTable({
                processing: true,
                serverSide: true,
                language: {
                    url: '{{ url('/datatable/ca.json') }}'
                },
                lengthMenu: [10, 25, 50, 100, 250],
                pageLength: 25,
                ajax: {
                    url: '{{ route('clients.active.list') }}',
                    type: 'GET',
                    data: function (d) {
                        d.length = $('#client-list').DataTable().page.len();
                        d.page = $('#client-list').DataTable().page.info().page + 1;
                    }
                },
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'city', name: 'city'},
                    {data: 'instances_links', name: 'instances_links'}
                ]
            });

            $('.filter').change(function () {
                const locationFilter = $('#filter-location').val();
                const clientTypeFilter = $('#filter-clientType').val();
                const serviceFilter = $('#filter-service').val();

                table.ajax.url('{{ route('clients.active.list') }}' +
                    '?location_id=' + locationFilter +
                    '&type_id=' + clientTypeFilter +
                    '&service_id=' + serviceFilter).load();
            });
        });
    </script>

    <div class="content myagora">
        @if (isset($currentClient['name']))
            <h3>{{ __('myagora.instance_list', ['name' => $currentClient['name']]) }}</h3>
        @endif

    @include('components.messages')

@endsection

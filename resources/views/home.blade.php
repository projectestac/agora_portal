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
                </th>
            </tr>
            <tr>
                <th>{{ __('common.name') }}</th>
                <th>{{ __('client.city') }}</th>
                <th>{{ __('client.instances') }}</th>
            </tr>
        </thead>
    </table>

    <script>
        var table;

        $(function () {
            table = $('#client-list').DataTable({
                processing: true,
                serverSide: false,
                language: {
                    url: '{{ url('/datatable/ca.json') }}'
                },
                lengthMenu: [10, 25, 50, 100, 250],
                pageLength: 25,
                ajax: '{{ route('clients.active.list') }}',
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'city', name: 'city'},
                    {data: 'instances_links', name: 'instances_links'}
                ]
            });

            $('.filter').change(function () {
                var locationFilter = $('#filter-location').val();
                var clientTypeFilter = $('#filter-clientType').val();

                table.ajax.url('{{ route('clients.active.list') }}' +
                    '?location_id=' + locationFilter +
                    '&type_id=' + clientTypeFilter).load();
            });
        });
    </script>

    <div class="content myagora">
        @if (isset($currentClient['name']))
            <h3>{{ __('myagora.instance_list', ['name' => $currentClient['name']]) }}</h3>
        @endif

    @include('components.messages')
@endsection

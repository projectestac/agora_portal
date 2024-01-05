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
            <th>{{ __('common.name') }}</th>
            <th>{{ __('client.city') }}</th>
            <th>{{ __('client.instances') }}</th>
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
                ajax: '{{ route('clients.active.list') }}',
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'city', name: 'city'},
                    {data: 'instances_links', name: 'instances_links'}
                ]
            });
        });
    </script>

    <div class="content myagora">
        @if (isset($currentClient['name']))
            <h3>{{ __('myagora.instance_list', ['name' => $currentClient['name']]) }}</h3>
        @endif

    @include('components.messages')
@endsection

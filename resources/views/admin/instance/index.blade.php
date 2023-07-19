@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <h3>{{ __('instance.instance_list') }}</h3>

        @include('components.messages')

        @if(!empty($instances))

            <div class="pull-right">
                {{ $instances->links('pagination::bootstrap-4') }}
            </div>

            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{{ __('instance.db_id') }}</th>
                    <th>{{ __('client.name') }}</th>
                    <th>{{ __('common.type') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th>{{ __('service.service') }}</th>
                    <th>{{ __('instance.location_long') }}</th>
                    <th>{{ __('common.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($instances as $instance)
                    <tr>
                        <td>{{ $instance->db_id }}</td>
                        <td>{{ $instance->client_name }}</td>
                        <td>{{ $instance->modelType->description }}</td>
                        <td>{{ $instance->status }}</td>
                        <td>
                            <img src="{{ asset('images/' . mb_strtolower($instance->service_name . '.gif')) }}"
                                 alt="{{ $instance->service_name }}"
                                 title="{{ $instance->service_name }}"
                            >
                        </td>
                        <td>{{ $instance->location_name }}</td>
                        <td>
                            <a class="btn btn-info" href="{{ route('instances.edit', $instance->id) }}" title="{{ __('service.edit') }}">
                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                            </a>
                            <form action="{{ route('instances.destroy', $instance->id) }}" method="POST"
                                  style="display: inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" title="{{ __('service.delete') }}">
                                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        @else
            <div class="alert alert-warning">{{ __('instances.no_instances') }}</div>
        @endif
    </div>
@endsection

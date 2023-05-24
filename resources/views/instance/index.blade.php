@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <p class="h3">{{ __('instance.instance_list') }}</p>

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
                        <th>{{ __('service.name') }}</th>
                        <th>{{ __('location.name') }}</th>
                        <th>{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($instances as $instance)
                    <tr>
                        <td>{{ $instance->db_id }}</td>
                        <td>{{ $instance->client_name }}</td>
                        <td>{{ $instance->client_type_name }}</td>
                        <td>{{ $instance->status }}</td>
                        <td>{{ $instance->service_name }}</td>
                        <td>{{ $instance->location_name }}</td>
                        <td>
                            <a href="{{ route('instances.show', $instance->id) }}"
                               class="btn btn-info">{{ __('common.show') }}</a>
                            <a href="{{ route('instances.edit', $instance->id) }}"
                               class="btn btn-primary">{{ __('common.edit') }}</a>
                            <form action="{{ route('instances.destroy', $instance->id) }}" method="POST"
                                  style="display: inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">{{ __('common.delete') }}</button>
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

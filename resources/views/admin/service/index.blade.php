@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <p class="h3">{{ __('service.service_list') }}</p>
        @if(!empty($services))
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{{ __('service.name') }}</th>
                    <th>{{ __('service.description') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th>{{ __('service.url') }}</th>
                    <th>{{ __('service.quota') }}</th>
                    <th>{{ __('service.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($services as $service)
                    <tr>
                        <td>{{ $service->name }}</td>
                        <td>{{ $service->description }}</td>
                        <td>{{ $service->status }}</td>
                        <td>/{{ $service->slug }}</td>
                        <td>{{ $service->quota }}</td>
                        <td>
                            <a href="{{ route('services.show', $service->id) }}"
                               class="btn btn-info">{{ __('common.show') }}</a>
                            <a href="{{ route('services.edit', $service->id) }}"
                               class="btn btn-primary">{{ __('common.edit') }}</a>
                            <form action="{{ route('services.destroy', $service->id) }}" method="POST"
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
            <div class="alert alert-warning">{{ __('service.no_services') }}</div>
        @endif
    </div>
@endsection

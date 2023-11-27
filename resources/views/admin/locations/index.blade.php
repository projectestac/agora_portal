@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <p class="h3">{{ __('locations.locations') }}</p>

        @if (!empty($locations))
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{{ __('service.name') }}</th>
                    <th>{{ __('common.created_at') }}</th>
                    <th>{{ __('common.updated_at') }}</th>
                    <th>{{ __('common.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($locations as $location)
                    <tr>
                        <td>{{ $location->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($location->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($location->updated_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('locations.edit', $location->id) }}"
                               class="btn btn-primary">{{ __('common.edit') }}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-warning">{{ __('locations.no_locations') }}</div>
        @endif
    </div>
@endsection

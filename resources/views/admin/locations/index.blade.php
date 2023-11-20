@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <h3>{{ __('location.locations') }}</h3>

        @include('components.messages')

        <div class="row">
            <div class="col-md-6">
                <a href="{{ route('locations.create') }}" class="btn btn-primary">{{ __('location.new_location') }}</a>
            </div>
        </div>

        @if (!empty($locations))
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{{ __('common.id') }}</th>
                    <th>{{ __('common.name') }}</th>
                    <th>{{ __('common.created_at') }}</th>
                    <th>{{ __('common.updated_at') }}</th>
                    <th>{{ __('common.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($locations as $location)
                    <tr>
                        <td>{{ $location->id }}</td>
                        <td>{{ $location->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($location->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($location->updated_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('locations.edit', $location->id) }}" class="btn btn-primary">
                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                            </a>
                            <form action="{{ route('locations.destroy', $location->id) }}"
                                  onsubmit="return confirm('{{ __('common.confirm_deletion') }}')" method="POST" style="display: inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-warning">{{ __('location.no_locations') }}</div>
        @endif
    </div>
@endsection

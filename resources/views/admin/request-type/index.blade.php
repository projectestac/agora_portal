@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content client-type">
        <p class="h3">{{ __('request-type.request_types') }}</p>

        <div class="row">
            <div class="col-md-6">
                <a href="{{ route('request-types.create') }}" class="btn btn-primary">{{ __('request-type.request_type_new') }}</a>
            </div>
        </div>

        @if (!empty($requestTypes))

            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{{ __('common.id') }}</th>
                    <th>{{ __('common.name') }}</th>
                    <th>{{ __('service.services') }}</th>
                    <th>{{ __('common.created_at') }}</th>
                    <th>{{ __('common.updated_at') }}</th>
                    <th>{{ __('common.actions') }}</th>
                </tr>
                </thead>
                <tbody>

                    @foreach ($requestTypes as $requestType)

                        <tr>
                            <td>{{ $requestType->id }}</td>
                            <td>{{ $requestType->name }}</td>
                            <td>
                                @foreach ($requestType->services as $service)
                                    <img src="{{ secure_asset('images/' . mb_strtolower($service->name . '.gif')) }}" alt="{{ $service->name }}">
                                @endforeach
                            <td>{{ \Carbon\Carbon::parse($requestType->created_at)->format('d/m/Y H:i')}}</td>
                            <td>{{ \Carbon\Carbon::parse($requestType->updated_at)->format('d/m/Y H:i')}}</td>
                            <td style="min-width: 100px;">
                                <a href="{{ route('request-types.edit', $requestType->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                </a>

                                @include('components.confirm-delete-modal', [
                                    'id' => $requestType->id,
                                    'name' => $requestType->name,
                                    'route' => route('request-types.destroy', $requestType->id)
                                ])
                            </td>
                        </tr>

                    @endforeach

                </tbody>
            </table>

        @else
            <div class="alert alert-warning">{{ __('model.no_models') }}</div>
        @endif
    </div>
@endsection

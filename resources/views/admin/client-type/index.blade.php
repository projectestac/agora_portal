@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content client-type">
        <h3>{{ __('client-type.client_types') }}</h3>

        @include('components.messages')

        <div class="row">
            <div class="col-md-6">
                <a href="{{ route('client-types.create') }}" class="btn btn-primary">{{ __('client-type.client_type_new') }}</a>
            </div>
        </div>

        @if (!empty($clientTypes))

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

                @foreach ($clientTypes as $clientType)
                    <tr>
                        <td>{{ $clientType->id }}</td>
                        <td>{{ $clientType->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($clientType->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($clientType->updated_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('client-types.edit', $clientType->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                            </a>

                            @include('components.confirm-delete-modal', [
                                'id' => $clientType->id,
                                'route' => route('client-types.destroy', $clientType->id)
                            ])
                        </td>
                    </tr>

                @endforeach

                </tbody>
            </table>

        @else
            <div class="alert alert-warning">{{ __('client-type.no_client_types') }}</div>
        @endif
    </div>
@endsection

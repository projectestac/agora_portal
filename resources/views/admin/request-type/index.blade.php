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
                    <th>{{ __('common.description') }}</th>
                    <th>{{ __('common.prompt') }}</th>
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
                            <td>{{ $requestType->description }}</td>
                            <td>{{ $requestType->prompt }}</td>
                            <td>{{ $requestType->created_at }}</td>
                            <td>{{ $requestType->updated_at }}</td>
                            <td>
                                <a href="{{ route('request-types.edit', $requestType->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                </a>

                                <form action="{{ route('request-types.destroy', $requestType->id) }}" onsubmit="return confirm('{{ __('common.confirm_deletion') }}')" method="POST" style="display: inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>
                              </form>
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

@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content client-type">
        <p class="h3">{{ __('client-type.client_types') }}</p>

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
                            <td>{{ $clientType->created_at }}</td>
                            <td>{{ $clientType->updated_at }}</td>
                            <td>
                                <a href="{{ route('client-types.edit', $clientType->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                </a>

                                <form action="{{ route('client-types.destroy', $clientType->id) }}" onsubmit="return confirm('{{ __('common.confirm_deletion') }}')" method="POST" style="display: inline">
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

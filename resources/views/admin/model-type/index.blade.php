@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content config">

        <p class="h3">{{ __('modeltype.models') }}</p>

        <div class="row">
            <div class="col-md-6">
                <a href="{{ route('model-types.create') }}" class="btn btn-primary">{{ __('modeltype.new_model') }}</a>
            </div>
        </div>

        {{-- $modelTypes is defined when returning the view in ModelTypeController.php --}}

        @if (!empty($modelTypes))

            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{{ __('service.service') }}</th>
                    <th>{{ __('common.description') }}</th>
                    <th>{{ __('common.short_code') }}</th>
                    <th>{{ __('service.url') }}</th>
                    <th>{{ __('common.database') }}</th>
                    <th>{{ __('common.created_at') }}</th>
                    <th>{{ __('common.updated_at') }}</th>
                    <th>{{ __('common.actions') }}</th>
                </tr>
                </thead>
                <tbody>

                    @foreach ($modelTypes as $modelType)

                        <tr>
                            <td>
                                <img src="{{ secure_asset('images/' . mb_strtolower($modelType->service->name . '.gif')) }}" alt="">
                            </td>
                            <td>{{ $modelType->description }}</td>
                            <td>{{ $modelType->short_code }}</td>
                            <td><a href="{{ $modelType->url }}" target="_blank">{{ $modelType->url }}</a></td>
                            <td>{{ $modelType->db }}</td>
                            <td>{{ \Carbon\Carbon::parse($modelType->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($modelType->updated_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('model-types.edit', $modelType->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                </a>
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

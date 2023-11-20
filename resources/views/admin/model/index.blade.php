@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content batch">
        {{-- Translations are defined in lang/ca folder --}}

        <p class="h3">{{ __('model.models') }}</p>

        {{-- $modelTypes is defined when returning the view in ModelTypeController.php --}}

        @if (!empty($modelTypes))

            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{{ __('common.description') }}</th>
                    <th>URL</th>
                    <th>{{ __('common.created_at') }}</th>
                    <th>{{ __('common.updated_at') }}</th>
                    <th>{{ __('common.actions') }}</th>
                </tr>
                </thead>
                <tbody>

                    @foreach ($modelTypes as $modelType)

                        <tr>
                            <td>{{ $modelType->description }}</td>
                            <td>{{ $modelType->url }}</td>
                            <td>{{ \Carbon\Carbon::parse($modelType->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($modelType->updated_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('models.edit', $modelType->id) }}" class="btn btn-primary">{{ __('common.edit') }}</a>
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

@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content batch">
        <p class="h3">{{ __('model.models') }}</p>

        @if (!empty($modelTypes))

            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{{ __('common.description') }}</th>
                    <th>URL</th>
                </tr>
                </thead>
                <tbody>

                    @foreach ($modelTypes as $modelType)

                        <tr>
                            <td>{{ $modelType->description }}</td>
                            <td>{{ $modelType->url }}</td>
                        </tr>

                    @endforeach

                </tbody>
            </table>

        @else
            <div class="alert alert-warning">{{ __('model.no_models') }}</div>
        @endif
    </div>
@endsection

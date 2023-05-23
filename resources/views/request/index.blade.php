@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <p class="h3">{{ __('request.request_list') }}</p>
        @if (!empty($requests))
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{{ __('client.name') }}</th>
                    <th>{{ __('service.name') }}</th>
                    <th>{{ __('user.name') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th>{{ __('common.type') }}</th>
                    <th>{{ __('common.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($requests  as $request)
                    <tr>
                        <td>{{ $request->client->name }}</td>
                        <td>{{ $request->service->name }}</td>
                        <td>{{ $request->user->name }}</td>
                        <td>{{ $request->status }}</td>
                        <td>{{ $request->requestType->name }}</td>
                        <td>
                            <a href="{{ route('requests.show', $request->id) }}"
                               class="btn btn-info">{{ __('common.show') }}</a>
                            <a href="{{ route('requests.edit', $request->id) }}"
                               class="btn btn-primary">{{ __('common.edit') }}</a>
                            <form action="{{ route('requests.destroy', $request->id) }}" method="POST"
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
            <div class="alert alert-warning">{{ __('request.no_requests') }}</div>
        @endif
    </div>
@endsection

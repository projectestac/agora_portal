@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <h3>{{ __('request.request_list') }}</h3>
        @if (!empty($requests))
            <div class="pull-right">
                {{ $requests->links('pagination::bootstrap-4') }}
            </div>

            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{{ __('client.client') }}</th>
                    <th>{{ __('service.service') }}</th>
                    <th>{{ __('user.name') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th>{{ __('common.type') }}</th>
                    <th>{{ __('common.created_at') }}</th>
                    <th>{{ __('common.updated_at') }}</th>
                    <th>{{ __('common.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($requests as $request)
                    <tr>
                        <td>
                            <a href="{{ route('myagora.instances', ['code' => $request->client->code]) }}">
                                {{ $request->client->name }}
                            </a>
                        </td>
                        <td>
                            <img src="{{ secure_asset('images/' . mb_strtolower($request->service->name . '.gif')) }}"
                                 alt="{{ $request->service->name }}">
                        </td>
                        <td>{{ $request->user->name }}</td>
                        <td>
                            <span class="btn btn-{{ (new \App\Http\Controllers\RequestController)->getStatusColor($request->status) }}">
                                {{ $request->status }}
                            </span>
                        </td>
                        <td>{{ $request->requestType->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($request->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($request->updated_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('requests.edit', $request->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                            </a>
                            <form action="{{ route('requests.destroy', $request->id) }}" method="POST" style="display: inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" title="{{ __('common.delete') }}">
                                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="pull-right">
                {{ $requests->links('pagination::bootstrap-4') }}
            </div>
        @else
            <div class="alert alert-warning">{{ __('request.no_requests') }}</div>
        @endif
    </div>
@endsection

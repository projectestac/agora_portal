@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <p class="h3">{{ __('client.client_list') }}</p>
        @if(!empty($clients))
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{{ __('client.name') }}</th>
                    <th>{{ __('client.code') }}</th>
                    <th>{{ __('client.dns') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th>{{ __('common.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($clients as $client)
                    <tr>
                        <td>{{ $client->name }}</td>
                        <td>{{ $client->code }}</td>
                        <td>{{ $client->dns }}</td>
                        <td>{{ $client->status }}</td>
                        <td>
                            <a href="{{ route('clients.show', $client->id) }}"
                               class="btn btn-info">{{ __('common.show') }}</a>
                            <a href="{{ route('clients.edit', $client->id) }}"
                               class="btn btn-primary">{{ __('common.edit') }}</a>
                            <form action="{{ route('clients.destroy', $client->id) }}" method="POST"
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
            <div class="alert alert-warning">{{ __('client.no_clients') }}</div>
        @endif
    </div>
@endsection

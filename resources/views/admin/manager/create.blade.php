@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content manager">
        <h3>{{ __('manager.add_manager') }}</h3>

        @include('components.messages')

        <form class="form-horizontal" action="{{ route('managers.store_new') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="username">{{ __('manager.user') }}</label>
                <div class="col-sm-8">
                    <select name="username" id="username" class="form-control">
                        <option value="" selected disabled>{{ __('manager.select_user') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->name }}">
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="client_id">{{ __('manager.client') }}</label>
                <div class="col-sm-8">
                    <select name="client_id" id="client_id" class="form-control">
                        <option value="" selected disabled>{{ __('manager.select_client') }}</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div class="text-center">
                    <button type="submit" class="btn btn-success">{{ __('common.save') }}</button>
                </div>
            </div>

        </form>
    </div>

@endsection

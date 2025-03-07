@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content manager">
        <h3>{{ __('manager.edit_manager') }}</h3>

        @include('components.messages')

        <?php
        // dd($clients);
        ?>

        <form class="form-horizontal" action="{{ route('manager.update', ['manager' => $manager->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="client_id">{{ __('manager.client') }}</label>
                <div class="col-sm-8">
                    <select name="client_id" id="client_id" class="form-control">
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ $client->id == old('client_id', $manager->client_id) ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="user_id">{{ __('manager.user') }}</label>
                <div class="col-sm-8">
                    <select name="user_id" id="user_id" class="form-control">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $user->id == old('user_id', $manager->user_id) ? 'selected' : '' }}>
                                {{ $user->name }}
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

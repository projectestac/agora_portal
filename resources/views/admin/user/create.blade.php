@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content user-manager">
        <h3>{{ __('user.add_user') }}</h3>

        @include('components.messages')

        <form class="form-horizontal" action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="name">{{ __('user.name') }}</label>
                <div class="col-sm-8">
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="email">{{ __('user.email') }}</label>
                <div class="col-sm-8">
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="password">{{ __('user.password') }}</label>
                <div class="col-sm-8">
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="password_confirmation">{{ __('user.password_confirmation') }}</label>
                <div class="col-sm-8">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
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

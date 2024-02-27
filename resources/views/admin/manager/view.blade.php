@extends('layout.default')

@section('content')

    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content users">
        <h3>{{ __('user.users_list') }}</h3>

        @include('components.messages')

        <div class="alert alert-info" role="alert">
            {{ __('common.not_implemented_yet') }}
        </div>
    </div>

@endsection

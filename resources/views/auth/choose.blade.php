@extends('layout.default')

@section('content')

    <div class="">
        <h2>{{ __('login.login_page') }}</h2>
    </div>

    <!-- Session Status -->
    <x-auth-session-status :status="session('status')"/>

    <div class="login-form">
        <fieldset>
            <div class="col-md-6 login-block center-image">
                <a href="{{ route('login.google') }}" class="text-center">
                    <div>
                        <img src="{{ asset('images/xtec.png') }}" alt="{{ __('home.xtec') }}" title="{{ __('home.xtec') }}"/>
                    </div>
                    {{ __('login.login_xtec') }}
                </a>
            </div>

            <div class="col-md-6 login-block center-image">
                <a href="{{ route('login') }}" class="text-center">
                    <div>
                        <img src="{{ asset('images/agora.png') }}" alt="{{ __('login.login_local') }}" title="{{ __('login.login_local') }}"/>
                    </div>
                    {{ __('login.login_local') }}
                </a>
            </div>
        </fieldset>
    </div>

@endsection

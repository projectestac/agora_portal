@extends('layout.default')

@section('content')

    <div class="">
        <h2>{{ __('login.local_login_page') }}</h2>
    </div>

    @include('components.messages')

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')"/>

    <form method="POST" action="{{ route('login') }}" class="login-form">
        @csrf

        <fieldset>
            <!-- Email Address -->
            <div class="row-form">
                <x-input-label for="email" :value="__('login.email')"/>
                <x-text-input id="email" class="block" type="email" name="email" :value="old('email')"
                              required autofocus
                              autocomplete="username"/>
                <x-input-error :messages="$errors->get('email')"/>
            </div>

            <!-- Password -->
            <div class="row-form">
                <x-input-label for="password" :value="__('login.password')"/>
                <x-text-input id="password" class="block"
                              type="password"
                              name="password"
                              required autocomplete="current-password"/>
            </div>
            <x-input-error :messages="$errors->get('password')"/>

            <!-- Remember Me -->
            <div class="">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox"
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                           name="remember">
                    <span class="text-sm text-gray-600">{{ __('login.remember_me') }}</span>
                </label>
            </div>
        </fieldset>

        <div class="text-center">
            <x-primary-button id="login">
                {{ __('common.login') }}
            </x-primary-button>
        </div>
    </form>
@endsection

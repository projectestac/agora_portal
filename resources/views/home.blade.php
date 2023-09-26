@extends('layout.default')

@section('content')

    @auth()
        <div class="myagora-menu-container">
            @include('menu.clientmenu')
        </div>
    @endauth

    <h3>{{ __('home.active_instances') }}</h3>

    <div class="content myagora">
        @if (isset($currentClient['name']))
            <h3>{{ __('myagora.instance_list', ['name' => $currentClient['name']]) }}</h3>
    @endif

    @include('components.messages')
@endsection

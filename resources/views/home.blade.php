@extends('layout.default')

@section('content')
    <div class="myagora-menu-container">
        @include('menu.clientmenu')
    </div>

    <div class="content myagora">
        @if (isset($currentClient['name']))
            <h3>{{ __('myagora.instance_list', ['name' => $currentClient['name']]) }}</h3>
    @endif

    @include('components.messages')
@endsection

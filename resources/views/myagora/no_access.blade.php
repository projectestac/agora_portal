@extends('layout.default')

@section('content')
    <div class="myagora-menu-container">
        @include('menu.clientmenu')
    </div>

    <div class="content myagora">
            <div class="alert alert-warning">{{ $message }}</div>
    </div>
@endsection

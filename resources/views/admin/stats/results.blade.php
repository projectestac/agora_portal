@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content stats">

        @include('admin.stats.header')

        @include('admin.stats.controls')

        @include('admin.stats.table')

    </div>
@endsection

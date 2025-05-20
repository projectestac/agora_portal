@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content manager">
        <h3>{{ __('role.edit_role') }}</h3>

        @include('components.messages')

        <form class="form-horizontal" action="{{ route('roles.update', ['role' => $role->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="name">{{ __('role.role_name') }}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="name" name="name" value="{{ $role->name }}" required>
                </div>
            </div>

            <input type="hidden" name="guard_name" value="web">

            <div class="form-group">
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">{{ __('role.update') }}</button>
                </div>
            </div>

        </form>
    </div>

@endsection

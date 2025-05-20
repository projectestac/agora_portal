@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content role-manager">
        <h3>{{ __('role.add_role') }}</h3>

        @include('components.messages')

        <form class="form-horizontal" action="{{ route('roles.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="name">{{ __('role.role_name') }}</label>
                <div class="col-sm-8">
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                </div>
            </div>

            <input type="hidden" name="guard_name" value="web">

            <div class="form-group">
                <div class="text-center">
                    <button type="submit" class="btn btn-success">{{ __('common.save') }}</button>
                </div>
            </div>

        </form>
    </div>

@endsection

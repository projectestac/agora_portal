@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content client">
        <h3>{{ __('client.edit_client') }}</h3>

        @include('components.messages')

        <form class="form-horizontal" action="{{ route('users.update', ['user' => $user]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="name">{{ __('user.name') }}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="email">{{ __('user.email') }}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="role">{{ __('user.roles') }}</label>
                <div class="col-sm-8">
                    <select name="roles[]" class="form-control" multiple style="height:14vh;">
                        @foreach ($roles as $id => $role)
                            <option value="{{ $role }}" {{ in_array($role, $assignedRoles) ? 'selected' : '' }}>
                                {{ ucfirst($role) }}
                            </option>
                        @endforeach
                        <option value="" {{ (empty($assignedRoles)) ? 'selected' : '' }}>Sense rol</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="password">{{ __('user.password') }}</label>
                <div class="col-sm-8">
                    <input type="password" class="form-control" id="password" name="password" placeholder="{{ __('user.enter_new_password') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="password_confirmation">{{ __('user.confirm_password') }}</label>
                <div class="col-sm-8">
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="{{ __('user.confirm_new_password') }}">
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

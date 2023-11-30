@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content client">
        <h3>{{ __('client-type.client_type_new') }}</h3>

        @include('components.messages')

        <form class="form-horizontal" action="{{ route('client-types.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="name">{{ __('common.name') }}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
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

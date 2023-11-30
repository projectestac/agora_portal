@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content request-type">
        <h3>{{ __('request-type.request_type_new') }}</h3>

        @include('components.messages')

        <form class="form-horizontal" action="{{ route('request-types.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="name">{{ __('common.name') }}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="description">{{ __('common.description') }}</label>
                <div class="col-sm-8">
                    <textarea class="form-control" id="description" name="description">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="prompt">{{ __('common.prompt') }}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="prompt" name="prompt" value="{{ old('prompt') }}">
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

@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <h3>{{ __('request-type.request_type_edit') }}</h3>

        <div class="panel panel-default">
            <div class="panel-body">
                <form class="form-horizontal" action="{{ route('request-types.update', $requestType->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" id="model_id" name="model_id" value="{{ $requestType->id }}"/>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear" for="name">{{ __('common.name') }}</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="name" name="name" value="{{ $requestType->name }}">

                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear" for="description">{{ __('common.description') }}</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="description" name="description" value="{{ $requestType->description }}">

                            @error('description')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear" for="prompt">{{ __('common.prompt') }}</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="prompt" name="prompt" value="{{ $requestType->prompt }}">

                            @error('prompt')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="text-center">
                            <button type="submit" class="btn btn-success">{{ __('common.save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

@endsection

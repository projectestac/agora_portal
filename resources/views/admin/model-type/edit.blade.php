{{-- How do we land here in the code:
       Route /config/models/{id}/edit in web.php -> goes to the ModelTypeController.php -> returns this view --}}

@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <h3>{{ __('model.model_edit') }}</h3>

        <div class="panel panel-default">
            <div class="panel-body">
                <form class="form-horizontal" action="{{ route('model-types.update', $modelType->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" id="model_id" name="model_id" value="{{ $modelType->id }}"/>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear" for="service_id">{{ __('model.service') }}</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="service_id" name="service_id">
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ $modelType->service_id === $service->id ? 'selected' : '' }}>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('description')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear" for="description">{{ __('common.description') }}</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="description" name="description" value="{{ $modelType->description }}">

                            @error('description')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear" for="short_code">{{ __('common.short_code') }}</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="short_code" name="short_code" value="{{ $modelType->short_code }}">

                            @error('short_code')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear" for="url">{{ __('service.url') }}</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="url" name="url" value="{{ $modelType->url }}">

                            @error('url')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear" for="db">{{ __('common.database') }}</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="db" name="db" value="{{ $modelType->db }}">

                            @error('db')
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

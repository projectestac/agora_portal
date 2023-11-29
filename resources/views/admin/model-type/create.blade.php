@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content client">
        <h3>{{ __('model.new_model') }}</h3>

        @include('components.messages')

        <form class="form-horizontal" action="{{ route('model-types.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="service_id">{{ __('model.service') }}</label>
                <div class="col-sm-8">
                    <select class="form-control" id="service_id" name="service_id">
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ (int)old('service') === $service->id ? 'selected' : '' }}>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="description">{{ __('common.description') }}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="description" name="description" value="{{ old('description') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="url">{{ __('common.url') }}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="url" name="url" value="{{ old('url') }}">
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

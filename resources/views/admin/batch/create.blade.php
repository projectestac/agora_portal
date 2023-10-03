@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content batch create">
        <h3>{{ __('batch.batch_creation_instances') }}</h3>

        @include('components.messages')

        <form class="form-horizontal" action="{{ route('batch.instance.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="codeAndServer">{{ __('batch.code_and_server') }}</label>
                <div class="col-sm-8">
                    <textarea class="form-control" id="codeAndServer" name="codeAndServer" cols="20" style="height:15em;"></textarea>
                    <div class="alert alert-info">{{ __('batch.code_and_server_info') }}</div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="serviceId">{{ __('service.service') }}</label>
                <div class="col-sm-8">
                    <select class="form-control" id="serviceId" name="serviceId">
                        @foreach($services as $service)
                            <option value="{{ $service['id'] }}">{{ $service['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="modelTypeId">{{ __('model.model') }}</label>
                <div class="col-sm-8">
                    <select class="form-control" id="modelTypeId" name="modelTypeId">
                        @foreach($modelTypes as $modelType)
                            <option value="{{ $modelType->id }}">{{ $modelType->service->name }} - {{ $modelType->description }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div class="text-center">
                    <button type="submit" class="btn btn-success">{{ __('common.continue') }}</button>
                </div>
            </div>

        </form>

    </div>
@endsection

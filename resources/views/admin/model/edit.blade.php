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
                <form class="form-horizontal" action="{{ route('models.update', $modelType->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" id="model_id" name="model_id" value="{{ $modelType->id }}"/>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear">{{ __('common.description') }}</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="description" name="description" value="{{ $modelType->description }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear">URL</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="url" name="url" value="{{ $modelType->url }}">
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

    @include('error-popup')

@endsection

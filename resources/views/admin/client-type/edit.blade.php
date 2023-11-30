@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <h3>{{ __('client-type.client_type_edit') }}</h3>

        <div class="panel panel-default">
            <div class="panel-body">
                <form class="form-horizontal" action="{{ route('client-types.update', $clientType->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" id="model_id" name="model_id" value="{{ $clientType->id }}"/>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear" for="name">{{ __('common.name') }}</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="name" name="name" value="{{ $clientType->name }}">

                            @error('name')
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

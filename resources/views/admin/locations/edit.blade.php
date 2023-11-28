@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <h3>{{ __('locations.location_edit') }}</h3>

        <div class="panel panel-default">
            <div class="panel-body">
                <form class="form-horizontal" action="{{ route('locations.update', $location->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" id="location_id" name="location_id" value="{{ $location->id }}"/>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear">{{ __('common.name') }}</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="name" name="name" value="{{ $location->name }}">

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

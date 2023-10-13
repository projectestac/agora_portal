@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content client">
        <h3>{{ __('client.new_client') }}</h3>

        @include('components.messages')

        <form class="form-horizontal" action="{{ route('clients.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="name">{{ __('client.name') }}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="code">{{ __('client.code') }}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="dns">{{ __('client.dns') }}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="dns" name="dns" value="{{ old('dns') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="old_dns">{{ __('client.old_dns') }}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="old_dns" name="old_dns" value="{{ old('old_dns') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="status">{{ __('common.status') }}</label>
                <div class="col-sm-8">
                    <select class="form-control" id="status" name="status">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>{{ __('client.status_active') }}</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>{{ __('client.status_inactive') }}</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="location">{{ __('config.location') }}</label>
                <div class="col-sm-8">
                    <select class="form-control" id="location" name="location">
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ (int)old('location') === $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="client_type">{{ __('client.client_type') }}</label>
                <div class="col-sm-8">
                    <select class="form-control" id="client_type" name="client_type">
                        @foreach($client_types as $client_type)
                            <option value="{{ $client_type->id }}" {{ (int)old('client_type') === $client_type->id ? 'selected' : '' }}>
                                {{ $client_type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="visible">{{ __('client.visible') }}</label>
                <div class="col-sm-8">
                    <select class="form-control" id="visible" name="visible">
                        <option value="yes" {{ old('visible') === 'yes' ? 'selected' : '' }}>{{ __('common.yes') }}</option>
                        <option value="no" {{ old('visible') === 'no' ? 'selected' : '' }}>{{ __('common.no') }}</option>
                    </select>
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

@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content client">
        <h3>{{ __('client.edit_client') }}</h3>

        @include('components.messages')

        <form class="form-horizontal" action="{{ route('clients.update', ['client' => $client]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="name">{{ __('client.name') }} *</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $client->name) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="code">{{ __('client.code') }} *</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $client->code) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="dns">{{ __('client.dns') }} *</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="dns" name="dns" value="{{ old('dns', $client->dns) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="old_dns">{{ __('client.old_dns') }}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="old_dns" name="old_dns" value="{{ old('old_dns', $client->old_dns) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="status">{{ __('common.status') }} *</label>
                <div class="col-sm-8">
                    <select class="form-control" id="status" name="status">
                        <option value="active" {{ old('status', $client->status) === 'active' ? 'selected' : '' }}>
                            {{ __('client.status_active') }}
                        </option>
                        <option value="inactive" {{ old('status', $client->status) === 'inactive' ? 'selected' : '' }}>
                            {{ __('client.status_inactive') }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="location">{{ __('config.location') }} *</label>
                <div class="col-sm-8">
                    <select class="form-control" id="location" name="location">
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ (int)old('location', $client->location_id) === $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="client_type">{{ __('client.client_type') }} *</label>
                <div class="col-sm-8">
                    <select class="form-control" id="client_type" name="client_type">
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" {{ (int)old('client_type', $client->type_id) === $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="address">{{ __('client.address') }}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $client->address) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="city">{{ __('client.city') }}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $client->city) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="postal_code">{{ __('client.postal_code') }}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code', $client->postal_code) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label clear" for="visible">{{ __('client.visible') }} *</label>
                <div class="col-sm-8">
                    <select class="form-control" id="visible" name="visible">
                        <option value="yes" {{ old('visible', $client->visible) === 'yes' ? 'selected' : '' }}>{{ __('common.yes') }}</option>
                        <option value="no" {{ old('visible', $client->visible) === 'no' ? 'selected' : '' }}>{{ __('common.no') }}</option>
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

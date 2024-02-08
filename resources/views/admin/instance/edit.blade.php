@extends('layout.default')

@php
    use App\Helpers\Util;
    use Carbon\Carbon;
@endphp

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <h3>{{ __('instance.instance_edit') }}</h3>

        @include('components.messages')

        @if(!empty($instance))

            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <table class="table table-striped first_column_bold">
                            <tbody>
                            <tr>
                                <td>{{ __('service.name') }}</td>
                                <td>{{ $instance->service->name }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('instance.db_id') }}</td>
                                <td>{{ $instance->db_id }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('client.name') }}</td>
                                <td>{{ $instance->client->name }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('client.code') }}</td>
                                <td>{{ $instance->client->code }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('client.dns') }}</td>
                                <td>{{ $instance->client->dns }}</td>
                            </tr>
                            @if (!empty($instance->client->old_dns))
                                <tr>
                                    <td>{{ __('client.old_dns') }}</td>
                                    <td>{{ $instance->client->old_dns }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td>{{ __('config.location') }}</td>
                                <td>{{ $instance->client->location->name }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('client.address') }}</td>
                                <td>{{ $instance->client->address }} ({{ $instance->client->postal_code }} {{ $instance->client->city }})</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">{{ __('instance.requester') }}</td>
                                <td>{{ $instance->contact_name }}. {{ $instance->contact_profile }}</td>
                            </tr>

                            <tr>
                                <td>{{ __('common.disk_usage') }}</td>
                                <td>{{ Util::getFormattedDiskUsage($instance->used_quota, $instance->quota) }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('common.requested_at') }}</td>
                                <td>{{ Carbon::parse($instance->requested_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('common.created_at') }}</td>
                                <td>{{ Carbon::parse($instance->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('common.updated_at') }}</td>
                                <td>{{ Carbon::parse($instance->updated_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <form class="form-horizontal" action="{{ route('instances.update', $instance->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label class="col-sm-4 control-label clear" for="status">{{ __('common.status') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="status" name="status">
                                        @foreach($statusList as $key => $status)
                                            <option value="{{ $key }}" {{ $instance->status === $key ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('status')
                                <div class="text-danger">{{ __('common.error_occurred') }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label clear"></label>
                                <div class="col-sm-8">
                                    <label for="send_email">
                                        <input type="checkbox" id="send_email" name="send_email" checked> {{ __('instance.send_email_on_change') }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label clear" for="model_type_id">{{ __('common.type') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="model_type_id" name="model_type_id">
                                        @foreach($modelTypeList as $key => $description)
                                            <option value="{{ $key }}" {{ $instance->model_type_id === $key ? 'selected' : '' }}>{{ $description }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('model_type_id')
                                <div class="text-danger">{{ __('common.error_occurred') }}</div>
                                @enderror
                            </div>


                            <div class="form-group">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label clear" for="db_host">{{ __('instance.db_host') }}</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="db_host" name="db_host" value="{{ $instance->db_host }}">
                                    </div>
                                    @error('db_host')
                                    <div class="text-danger">{{ __('common.error_occurred') }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label clear" for="quota">{{ __('service.quota') }} (GB)</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="quota" name="quota"
                                               value="{{ Util::formatGb($instance->quota) }}">
                                    </div>
                                    @error('quota')
                                    <div class="text-danger">{{ __('common.error_occurred') }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label clear" for="observations">{{ __('instance.observations') }}</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" id="observations" name="observations"
                                                  rows="4">{{ $instance->observations }}</textarea>
                                    </div>
                                    @error('observations')
                                    <div class="text-danger">{{ __('common.error_occurred') }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label clear" for="annotations">{{ __('instance.annotations') }}</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" id="annotations" name="annotations"
                                                  rows="4">{{ $instance->annotations }}</textarea>
                                    </div>
                                    @error('annotations')
                                    <div class="text-danger">{{ __('common.error_occurred') }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success">{{ __('common.save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        @else
            <div class="alert alert-warning">{{ __('instances.no_instances') }}</div>
        @endif
    </div>
@endsection

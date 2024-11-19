@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content service">
        <h3>{{ __('request.request_edit') }}</h3>

        @include('components.messages')

        <div class="panel panel-default">
            <div class="panel-body">
                <form class="form-horizontal" action="{{ route('requests.update', $request->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" id="request_id" name="request_id" value="{{ $request->id }}"/>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear">{{ __('request.request_type') }}</label>
                        <div class="col-sm-8">
                            {{ $request->requestType->name }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear">{{ __('service.name') }}</label>
                        <div class="col-sm-8">
                            {{ $request->service->name }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear">{{ __('client.name') }}</label>
                        <div class="col-sm-8">
                            <a href="/portal/clients/{{ $request->client->id }}/edit" target="_blank">{{ $request->client->name }}</a>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear">{{ __('instance.instance') }}</label>
                        <div class="col-sm-8">
                            <a href="/portal/instances/{{ $instanceId }}/edit" target="_blank" class="btn btn-primary" title="{{ __('common.edit') }}">
                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                            </a>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear">{{ __('common.dashboard') }}</label>
                        <div class="col-sm-8">
                            <a href="/portal/myagora/instances?code={{ $request->client->code }}" target="_blank">
                                {{ $request->client->code }}
                            </a>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear">{{ __('user.name') }}</label>
                        <div class="col-sm-8">
                            {{ $request->user->name }} ({{ $request->user->email }})
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear" for="status">{{ __('common.status') }}</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="status" name="status">
                                @foreach($statusList as $key => $status)
                                    <option value="{{ $key }}" {{ $request->status === $key ? 'selected' : '' }}>{{ $status }}</option>
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
                                <input type="checkbox" id="send_email" name="send_email" checked> {{ __('request.send_email_on_change') }}
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear">{{ __('request.user_comment') }}</label>
                        <div class="col-sm-8">
                            {{ $request->user_comment }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear" for="admin_comment">{{ __('request.admin_comment') }}</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" id="admin_comment" name="admin_comment" rows="4">{{ $request->admin_comment }}</textarea>
                        </div>
                        @error('admin_comment')
                        <div class="text-danger">{{ __('common.error_occurred') }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label clear" for="private_note">{{ __('request.private_note') }}</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" id="private_note" name="private_note" rows="4">{{ $request->private_note }}</textarea>
                        </div>
                        @error('private_note')
                        <div class="text-danger">{{ __('common.error_occurred') }}</div>
                        @enderror
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

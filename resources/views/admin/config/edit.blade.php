@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content config">
        <h3>{{ __('config.config_settings') }}</h3>

        @include('components.messages')

        @if(!empty($config))

            <form class="form-horizontal" action="{{ route('config.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="col-sm-4 control-label clear" for="notify_address_quota">{{ __('config.notify_address_quota') }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="notify_address_quota" name="notify_address_quota"
                               @if(isset($config['notify_address_quota'])) value="{{ $config['notify_address_quota'] }}" @endif>
                        <div class="alert alert-info">{{ __('config.comma_separated_emails') }}</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label clear" for="notify_address_request">{{ __('config.notify_address_request') }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="notify_address_request" name="notify_address_request"
                               @if(isset($config['notify_address_request'])) value="{{ $config['notify_address_request'] }}" @endif>
                        <div class="alert alert-info">{{ __('config.comma_separated_emails') }}</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label clear" for="notify_address_user_cco">{{ __('config.notify_address_user_cco') }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="notify_address_user_cco" name="notify_address_user_cco"
                               @if(isset($config['notify_address_user_cco'])) value="{{ $config['notify_address_user_cco'] }}" @endif>
                        <div class="alert alert-info">{{ __('config.comma_separated_emails') }}</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label clear" for="quota_usage_to_request">{{ __('config.quota_usage_to_request') }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="quota_usage_to_request" name="quota_usage_to_request"
                               @if(isset($config['quota_usage_to_request'])) value="{{ $config['quota_usage_to_request'] * 100 }}" @endif>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label clear" for="quota_free_to_request">{{ __('config.quota_free_to_request') }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="quota_free_to_request" name="quota_free_to_request"
                               @if(isset($config['quota_free_to_request'])) value="{{ $config['quota_free_to_request'] }}" @endif>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label clear" for="quota_usage_to_notify">{{ __('config.quota_usage_to_notify') }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="quota_usage_to_notify" name="quota_usage_to_notify"
                               @if(isset($config['quota_usage_to_notify'])) value="{{ $config['quota_usage_to_notify'] * 100 }}" @endif>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label clear" for="quota_free_to_notify">{{ __('config.quota_free_to_notify') }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="quota_free_to_notify" name="quota_free_to_notify"
                               @if(isset($config['quota_free_to_notify'])) value="{{ $config['quota_free_to_notify'] }}" @endif>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label clear" for="xtecadmin_hash">{{ __('config.xtecadmin_hash') }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="xtecadmin_hash" name="xtecadmin_hash"
                               @if(isset($config['xtecadmin_hash'])) value="{{ $config['xtecadmin_hash'] }}" @endif>
                        <div class="alert alert-info">{{ __('config.xtecadmin_hash_help') }}</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label clear"
                           for="max_file_size_for_large_upload">{{ __('config.max_file_size_for_large_upload') }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="max_file_size_for_large_upload" name="max_file_size_for_large_upload"
                               @if(isset($config['max_file_size_for_large_upload'])) value="{{ $config['max_file_size_for_large_upload'] }}" @endif>
                        <div class="alert alert-info">{{ __('config.max_file_size_for_large_upload_help') }}</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label clear" for="nodes_create_db">{{ __('config.nodes_create_db') }}</label>
                    <div class="col-sm-8">
                        <input type="checkbox" id="nodes_create_db" name="nodes_create_db" @if($config['nodes_create_db']) checked @endif>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label clear" for="min_db_id">{{ __('config.min_db_id') }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="min_db_id" name="min_db_id"
                               @if(isset($config['min_db_id'])) value="{{ $config['min_db_id'] }}" @endif>
                        <div class="alert alert-info">{{ __('config.min_db_id_help') }}</div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="text-center">
                        <button type="submit" class="btn btn-success">{{ __('common.save') }}</button>
                    </div>
                </div>
            </form>

        <br>

            <form action="{{ route('update.quotas') }}" method="post">
                @csrf
                <div class="form-group">
                    <div class="text-center">
                        <button type="submit" class="btn btn-info">{{ __('common.update_quotas') }}</button>
                    </div>
                </div>
            </form>

        @else
            <div class="alert alert-warning">{{ __('config.no_config_settings') }}</div>
        @endif

    </div>
@endsection

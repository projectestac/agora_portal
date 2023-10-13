@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content batch queue">
        <h3>{{ __('batch.queues_operations') }}</h3>

        @include('components.messages')

        <ul class="nav nav-tabs">
            <li><a href="{{ route('queue.pending') }}">{{ __('batch.queue_pending') }}</a></li>
            <li class="active"><a href="{{ route('queue.success') }}">{{ __('batch.queue_success') }}</a></li>
            <li><a href="{{ route('queue.fail') }}">{{ __('batch.queue_fail') }}</a></li>
        </ul>

        <div class="tab-content">
            <div id="tab1" class="tab-pane fade">
            </div>

            <div id="tab2" class="tab-pane fade in active">
                @if(is_array($data))
                    <br>
                    <div class="pull-right">
                        {!! $links !!}
                    </div>

                    <br>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>{{ __('common.id') }}</th>
                            <th>{{ __('batch.operation') }}</th>
                            <th>{{ __('batch.queue') }}</th>
                            <th>{{ __('client.name') }}</th>
                            <th>{{ __('batch.priority') }}</th>
                            <th>{{ __('service.service') }}</th>
                            <th>{{ __('batch.queued_at') }}</th>
                            <th>{{ __('common.created_at') }}</th>
                            <th>{{ __('common.updated_at') }}</th>
                            <th>{{ __('common.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($data as $item)
                            <tr>
                                <td>{{ $item['id'] }}</td>
                                <td>{{ $item['operation_data']['action'] }}</td>
                                <td>{{ $item['queue'] }}</td>
                                <td>{{ $item['operation_data']['instance_name'] }}</td>
                                <td>{{ $item['operation_data']['priority'] }}</td>
                                <td>
                                    @if($item['instance'] instanceof \App\Models\Instance)
                                        <a href="{{ \App\Helpers\Util::getInstanceUrl($item['instance']) }}" target="_blank">
                                            <img src="{{ secure_asset('images/' . mb_strtolower($item['operation_data']['service_name'] . '.gif')) }}"
                                                 alt="{{ $item['operation_data']['service_name'] }}"
                                                 title="{{ $item['operation_data']['service_name'] }}"
                                            >
                                        </a>
                                    @else
                                        <img src="{{ secure_asset('images/' . mb_strtolower($item['operation_data']['service_name'] . '.gif')) }}"
                                             alt="{{ $item['operation_data']['service_name'] }}"
                                             title="{{ $item['operation_data']['service_name'] }}"
                                        >
                                    @endif
                                </td>
                                <td>{{ $item['queued_at'] }}</td>
                                <td>{{ $item['created_at'] }}</td>
                                <td>{{ $item['updated_at'] }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-info" title="{{ __('batch.params') }}"
                                                data-toggle="modal"
                                                data-target="#modal_params_{{ $item['id'] }}">
                                            <span class="glyphicon glyphicon-tasks" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-info" title="{{ __('batch.execution_log') }}"
                                                data-toggle="modal"
                                                data-target="#modal_result_{{ $item['id'] }}">
                                            <span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                    @foreach($data as $item)
                        <div class="modal fade" id="modal_params_{{ $item['id'] }}" tabindex="-1"
                             aria-labelledby="modal_params_title_{{ $item['id'] }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="panel-info">
                                        <div class="panel-heading">
                                            {{ __('batch.params') }}
                                            <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('common.close') }}">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <ul class="list-unstyled">
                                            @foreach ($item['operation_data'] as $name => $line)
                                                @if (is_array($line))
                                                    <hr>
                                                    @if(!empty($line))
                                                        {{ __('batch.params') }}:
                                                        @foreach ($line as $key => $value)
                                                            <li>{{ $key }}: {{ $value }}</li>
                                                        @endforeach
                                                    @else
                                                        {{ __('batch.no_params') }}
                                                    @endif
                                                    <hr>
                                                @else
                                                    <li>{{ $name }}: {{ $line }}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="panel-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.close') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="modal_result_{{ $item['id'] }}" tabindex="-1"
                             aria-labelledby="modal_result_title_{{ $item['id'] }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="panel-info">
                                        <div class="panel-heading">
                                            {{ __('batch.execution_log') }}
                                            <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('common.close') }}">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <ul class="list-unstyled">
                                            @foreach ($item['result'] as $line)
                                                <li>{{ $line }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="panel-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.close') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div id="tab3" class="tab-pane fade">
            </div>
        </div>

@endsection

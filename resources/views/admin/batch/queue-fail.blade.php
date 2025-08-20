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
            <li><a href="{{ route('queue.success') }}">{{ __('batch.queue_success') }}</a></li>
            <li class="active"><a href="{{ route('queue.fail') }}">{{ __('batch.queue_fail') }}</a></li>
        </ul>

        <div class="tab-content">
            <div id="tab1" class="tab-pane fade">
            </div>

            <div id="tab2" class="tab-pane fade">
            </div>

            <div id="tab3" class="tab-pane fade in active">
                @if(is_array($data))

                    <br>

                    <x-batch-search :action="url()->current()" />

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
                            <th>{{ __('batch.failed_at') }}</th>
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
                                    <img src="{{ secure_asset('images/' . mb_strtolower($item['operation_data']['service_name'] . '.gif')) }}"
                                         alt="{{ $item['operation_data']['service_name'] }}"
                                         title="{{ $item['operation_data']['service_name'] }}"
                                    >
                                </td>
                                <td>{{ $item['failed_at'] }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-info" title="{{ __('batch.execution_error') }}"
                                                data-toggle="modal"
                                                data-target="#modal_result_{{ $item['id'] }}">
                                            <span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                    <div class="btn-group" role="group" style="margin-left: 15px;">
                                        @include('admin.batch.query-form-enqueue')
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                    @foreach($data as $item)
                        <div class="modal fade" id="modal_result_{{ $item['id'] }}" tabindex="-1"
                             aria-labelledby="modal_result_title_{{ $item['id'] }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="panel-info">
                                        <div class="panel-heading">
                                            {{ __('batch.execution_error') }}
                                            <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('common.close') }}">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        {!! nl2br($item['exception']) !!}
                                    </div>
                                    <div class="panel-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.close') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @include('admin.batch.query-modal-enqueue')
                    @endforeach
                @endif
            </div>
        </div>

@endsection

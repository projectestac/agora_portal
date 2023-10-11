@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content batch queue">
        <h3>{{ __('batch.queues_operations') }}</h3>

        @include('components.messages')

        <ul class="nav nav-tabs">
            <li class="active"><a href="{{ route('queue.pending') }}">{{ __('batch.queue_pending') }}</a></li>
            <li><a href="{{ route('queue.success') }}">{{ __('batch.queue_success') }}</a></li>
            <li><a href="{{ route('queue.fail') }}">{{ __('batch.queue_fail') }}</a></li>
        </ul>

        <div class="tab-content">
            <div id="tab1" class="tab-pane fade in active">
                @if(is_array($data) && count($data) > 0)
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
                                    <a href="{{ \App\Helpers\Util::getInstanceUrl($item['instance_id']) }}" target="_blank">
                                        <img src="{{ secure_asset('images/' . mb_strtolower($item['operation_data']['service_name'] . '.gif')) }}"
                                             alt="{{ $item['operation_data']['service_name'] }}"
                                             title="{{ $item['operation_data']['service_name'] }}"
                                        >
                                    </a>
                                </td>
                                <td>{{ $item['created_at'] }}</td>
                                <td>
                                    <form class="form-inline" method="POST"
                                          action="{{ route('queue.destroy', $item['id']) }}"
                                          id="delete_operation_{{ $item['id'] }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger"
                                                title="{{ __('batch.remove_queue_operation', ['id' => $item['id']]) }}"
                                                onclick="confirmDelete({{ $item['id'] }});">
                                            <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                @else
                    <br>
                    <div class="alert alert-info">
                        {{ __('batch.no_operation_pending') }}
                    </div>
                @endif
            </div>

            <div id="tab2" class="tab-pane fade">
            </div>

            <div id="tab3" class="tab-pane fade">
            </div>
        </div>

        <script>
            function confirmDelete(id) {
                event.preventDefault();
                if (confirm('{{ __('batch.remove_queue_operation_confirm') }}')) {
                    document.getElementById('delete_operation_' + id).submit();
                }
            }
        </script>

@endsection

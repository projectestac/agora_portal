@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content batch operation">
        <h3>{{ __('batch.operations') }}</h3>

        @include('components.messages')

        <div class="panel panel-info">
            <div class="panel-heading">
                {{ __('batch.operation_to_queue') }}: {{ $action }}
            </div>
            <div class="panel-body">
                <div class="form-horizontal">
                    <strong>{{ __('batch.priority') }}</strong>:
                    <span>{{ $priority }}</span>
                </div>
                <div class="form-horizontal">
                    <strong>{{ __('common.params') }}</strong>:
                    @if(!empty($params))
                        <ul>
                            @foreach($params as $key => $param)
                                <li><strong>{{ $key }}</strong>: {{ $param }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <img src="{{ asset('images/' .  $image . '.gif') }}" alt="{{ $serviceName }}" title="{{ $serviceName }}"/>
                {{ __('batch.instances_on_execute') }}
            </div>
            <div class="panel-body">
                <ul>
                    @foreach($instances as $instance)
                        <li>{{ $instance['name'] }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <form action="{{ route('batch.operation.program') }}" method="POST">
            @csrf
            <div class="row form-inline clear text-center">
                <button type="submit" class="btn btn-primary">{{ __('batch.send_to_queue') }}</button>
            </div>
        </form>

    </div>
@endsection

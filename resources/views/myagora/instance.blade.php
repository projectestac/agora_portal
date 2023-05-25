@extends('layout.default')

@php use Carbon\Carbon; @endphp

@section('content')
    <div class="myagora-menu-container">
        @include('menu.clientmenu')
    </div>

    <div class="content myagora">
        @if (!empty($instances))
            <h3>{{ __('myagora.instance_list', ['name' => $current_client['name']]) }}</h3>

            @foreach($instances as $instance)
                @php $date = Carbon::parse($instance->pivot->created_at); @endphp

                <div class="panel panel-default">
                    <div class="panel-heading row-fluid clearfix">
                        {{ $instance->name }}: {{ $instance->description }}
                    </div>
                    <div class="panel-body">
                        <ul>
                            <li><strong>{{ __('myagora.requested_by') }}:</strong> {{ $instance->pivot->contact_name }}</li>
                            <li><strong>{{ __('myagora.active_date') }}:</strong> {{ $date->format('d/m/Y') }}</li>
                            <li><strong>{{ __('common.status') }}:</strong> {{ $instance->pivot->status }}</li>
                            <li><strong>{{ __('myagora.database') }}:</strong> {{ $instance->pivot->db_id }}</li>
                            <li><strong>{{ __('service.quota') }}:</strong> ({{ $instance->pivot->used_quota }} / {{ $instance->pivot->quota }})</li>
                        </ul>
                    </div>
                </div>

            @endforeach
        @else
            <div class="alert alert-warning">{{ __('myagora.no_instances') }}</div>
        @endif

    </div>
@endsection

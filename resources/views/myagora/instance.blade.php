@extends('layout.default')

@php
    use Carbon\Carbon;
    use App\Helpers\Util;
@endphp

@section('content')
    <div class="myagora-menu-container">
        @include('menu.clientmenu')
    </div>

    <div class="content myagora">
        @if (isset($current_client['name']))
            <h3>{{ __('myagora.instance_list', ['name' => $current_client['name']]) }}</h3>
        @endif

        @include('components.messages')

        @if (!empty($instances))
            @foreach($instances as $instance)
                @php
                    $date = Carbon::parse($instance->pivot->created_at);
                    $quota = Util::formatBytes($instance->pivot->quota);
                    $usedQuota = Util::formatBytes($instance->pivot->used_quota);
                @endphp

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
                            <li><strong>{{ __('service.quota') }}:</strong> ({{ $usedQuota }} / {{ $quota }})</li>
                        </ul>
                    </div>
                </div>
            @endforeach
        @else
            <div class="alert alert-warning">{{ __('myagora.no_instances') }}</div>
        @endif

    </div>
@endsection

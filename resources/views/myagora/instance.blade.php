@extends('layout.default')

@php
    use App\Helpers\Util;
    use Carbon\Carbon;
@endphp

@section('content')
    <div class="myagora-menu-container">
        @include('menu.clientmenu')
    </div>

    <div class="content myagora">
        @if (isset($currentClient['name']))
            <h3>{{ __('myagora.instance_list', ['name' => $currentClient['name']]) }}</h3>
        @endif

        @include('components.messages')

        @if (!empty($instances))
            @foreach($instances as $instance)
                @php
                    $date = Carbon::parse($instance->created_at);
                    $quota = Util::formatBytes($instance->quota);
                    $usedQuota = Util::formatBytes($instance->used_quota);
                @endphp

                <div class="panel panel-default">
                    <div class="panel-heading row-fluid clearfix">
                        {{ $instance->service->name }}: {{ $instance->service->description }}
                    </div>
                    <div class="panel-body">
                        <ul>
                            <li><strong>{{ __('myagora.requested_by') }}:</strong> {{ $instance->contact_name }}</li>
                            <li><strong>{{ __('myagora.active_date') }}:</strong> {{ $date->format('d/m/Y') }}</li>
                            <li><strong>{{ __('common.status') }}:</strong> {{ $instance->status }}</li>
                            <li><strong>{{ __('myagora.database') }}:</strong> {{ $instance->db_id }}</li>
                            <li><strong>{{ __('service.quota') }}:</strong> ({{ $usedQuota }} / {{ $quota }})</li>
                        </ul>
                    </div>
                </div>
            @endforeach
        @else
            <div class="alert alert-warning">{{ __('myagora.no_instances') }}</div>
        @endif

        @if (!empty($availableServices))
            <div class="panel panel-default">
                <div class="panel-heading row-fluid clearfix">
                    {{ __('myagora.other_services_available') }}
                </div>
                <div class="panel-body">
                    @foreach($availableServices as $service)
                        <div style="margin: 10px 0 10px 0">
                            <a class="btn btn-default" href="{{ route('instances.create', ['service_id' => $service['id']]) }}" role="button">
                                <img src="{{ asset('images/' . mb_strtolower($service['name']) . '.gif') }}"
                                     alt="{{ $service['name'] }}"
                                     title="{{ $service['name'] }}"
                                >
                                <span>{{ __('myagora.request_now') }}</span>
                            </a>
                            <strong>{{ $service['name'] }}</strong>: {{ $service['description'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
@endsection

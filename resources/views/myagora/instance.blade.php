@extends('layout.default')

@php
    use App\Helpers\Access;
    use App\Helpers\Util;
    use Carbon\Carbon;
@endphp

@section('content')
    <div class="myagora-menu-container">
        @include('menu.clientmenu')
    </div>

    <div class="content myagora">
        @if (isset($currentClient['name']))
            <h3>{{ __('myagora.instance_list', ['name' => $currentClient['name']]) }}

                @if (Access::isAdmin(Auth::user()))
                    <a href="{{ route('clients.edit', $currentClient['id']) }}" class="btn btn-primary" title="{{ __('common.edit') }}" target="_blank">
                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                    </a>
                @endif
            </h3>
        @endif

        @if (!empty($error))
            <div class="alert alert-info">{{ $error }}</div>
        @endif

        @include('components.messages')

        @if (!empty($currentClient))

            @if (!empty($instances))
                @foreach($instances as $instance)
                    @php
                        $date = Carbon::parse($instance->created_at);
                        $quota = Util::formatBytes($instance->quota);
                        $usedQuota = Util::formatBytes($instance->used_quota);
                        $percent = round($instance->used_quota / $instance->quota * 100);
                        $instanceId = $instance->id;
                        $url = Util::getInstanceUrl($instance);
                    @endphp

                    <div class="panel panel-default">
                        <div class="panel-heading row-fluid clearfix">
                            {{ $instance->service->name }}: {{ $instance->service->description }}
                        </div>
                        <div class="panel-body">
                            <div><strong>{{ __('service.url') }}:</strong> <a href="{{ $url }}" target="_blank">{{ $url }}</a></div>
                            <div><strong>{{ __('myagora.requested_by') }}:</strong> {{ $instance->contact_name }}</div>
                            <div><strong>{{ __('myagora.active_date') }}:</strong> {{ $date->format('d/m/Y') }}</div>
                            <div><strong>{{ __('common.status') }}:</strong> {{ $instance->status }}</div>
                            @if($instance->status === \App\Models\Instance::STATUS_ACTIVE)
                                <div><strong>{{ __('common.database') }}:</strong> {{ $instance->db_id }}</div>
                                @include('myagora.components.quota-usage')
                            @endif
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
                                    <img src="{{ secure_asset('images/' . mb_strtolower($service['name']) . '.gif') }}"
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

            @if(!empty($newDNS) && ($newDNS !== $currentDNS) && !$instances->isEmpty())
                <div class="alert alert-info">
                    <form action="{{ route('myagora.changedns') }}" method="post">
                        @csrf
                        <p>{!! __('client.nompropi_has_changed_1', ['currentDNS' => $currentDNS, 'newDNS' => $newDNS]) !!}</p>
                        <p>{!! __('client.nompropi_has_changed_2') !!}</p>
                        <p>{{ __('client.nompropi_change_confirm_question') }}</p>

                        <input type="hidden" name="clientId" value="{{ $currentClient['id'] }}"/>
                        <input type="hidden" name="currentDNS" value="{{ $currentDNS }}"/>
                        <input type="hidden" name="newDNS" value="{{ $newDNS }}"/>

                        <button type="submit" class="btn btn-success">
                            <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                            {{ __('client.nompropi_change_confirm_yes') }}
                        </button>
                    </form>
                </div>
            @endif

        @endif
    </div>
@endsection

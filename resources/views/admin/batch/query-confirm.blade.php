@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content batch query">
        <h3>{{ __('batch.query_execution_confirm') }}</h3>

        @include('components.messages')

        <div class="panel panel-info">
            <div class="panel-heading">
                <img src="{{ asset('images/' .  $image . '.gif') }}" alt="{{ $serviceName }}" title="{{ $serviceName }}"/>
                {{ __('batch.query_to_execute') }}
            </div>
            <div class="panel-body">
                {{ base64_decode(addslashes($sqlQueryEncoded)) }}
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                {{ __('batch.instances_to_exec') }}
            </div>
            <div class="panel-body">
                @if(!empty($clients))
                    @foreach($clients as $client)
                        {{ $client['name'] }}, {{ $client['code'] }} <br/>
                    @endforeach
                @else
                    {{ __('service.portal') }}
                @endif
            </div>
        </div>

        <form action="{{ route('batch.query.exec') }}" method="POST">
            @csrf
            <div class="hidden">
                <input type="hidden" name="sqlQueryEncoded" value="{{ $sqlQueryEncoded }}">
                <input type="hidden" name="serviceSel" value="{{ $serviceSel }}">
                @if(!empty($clients))
                    <select name="clientsSel[]" multiple="multiple">
                        @foreach($clients as $client)
                            <option value="{{ $client['id'] }}" selected>{{ $client['id'] }}</option>
                        @endforeach
                    </select>
                @endif
                <input type="hidden" name="serviceName" value="{{ $serviceName }}">
                <input type="hidden" name="image" value="{{ $image }}">
            </div>
            <button type="submit" class="btn btn-success">
                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> {{ __('common.execute') }}
            </button>
            <a href="{{ route('batch.query') }}" class="btn btn-danger">
                <span class="glyphicon glyphicon-remove" aria-hidden="true"></span> {{ __('common.cancel') }}
            </a>
        </form>
    </div>
@endsection

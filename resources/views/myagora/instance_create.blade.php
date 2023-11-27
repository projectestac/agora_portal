@extends('layout.default')

@section('content')
    <div class="myagora-menu-container">
        @include('menu.clientmenu')
    </div>

    <div class="content myagora">

        <h3>{{ __('myagora.request_service') }} <strong>{{ $service['name'] }}</strong></h3>

        @include('components.messages')

        @if (empty($error))

            <form action="{{ route('instances.store') }}" method="post">
                @csrf
                <input type="hidden" name="service_id" value="{{ $service['id'] }}">
                <input type="hidden" name="client_id" value="{{ $client_id }}">
                <div class="panel panel-default">
                    <div class="panel-heading row-fluid clearfix">
                        <img src="{{ secure_asset('images/' . mb_strtolower($service['name'] . '.gif')) }}"
                             alt="{{ $service['name'] }}"
                             title="{{ $service['name'] }}"
                        >
                    </div>
                    <div class="panel-body">
                        <div class="margin-1">
                            <strong>{{ __('service.description') }}</strong>: {{ $service['description'] }}
                        </div>
                        @if (!empty($models))
                            <div class="margin-1"><strong>{{ __('model.select_model') }}</strong>:</div>
                            <div class="margin-1 margin-left-2">
                                @foreach($models as $model)
                                    <input type="radio" id="{{ $model['id'] }}" name="model_type_id" value="{{ $model['id'] }}"/>
                                    <label for="{{ $model['id'] }}">{{ $model['description'] }}</label><br/>
                                @endforeach
                            </div>
                        @endif
                        <div class="margin-1">
                            <strong>{{ __('myagora.request_by') }}</strong>: {{ $username }}
                        </div>
                        <div class="margin-1">
                            <label for="contact_profile"><strong>{{ __('myagora.contact_profile') }}</strong>:</label>
                            <input type="text" id="contact_profile" name="contact_profile" size="50"/>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-success" title="{{ __('common.add') }}">
                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> {{ __('common.submit') }}
                    </button>
                    <a class="btn btn-danger" href="{{ route('myagora.instances') }}" title="{{ __('common.cancel') }}">
                        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span> {{ __('common.cancel') }}
                    </a>
                </div>
            </form>

        @else
            <div class="alert alert-danger">
                {{ $error }}
            </div>
        @endif
    </div>
@endsection

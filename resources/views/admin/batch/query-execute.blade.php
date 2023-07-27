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
                <img src="{{ secure_asset('images/' .  $image . '.gif') }}" alt="{{ $serviceName }}" title="{{ $serviceName }}"/>
                {{ __('batch.query_executed') }}
                <div class="pull-right">
                    <a href="{{ route('batch.query') }}" class="btn btn-info">
                        {{ __('batch.modify_query') }}
                    </a>
                </div>
            </div>
            <div class="panel-body">
                {{ base64_decode(addslashes($sqlQueryEncoded)) }}
            </div>
        </div>

        {{-- Summary table --}}
        @if (($numRows === 1) && ($serviceName !== 'portal'))
            <div class="panel panel-default">
                <div class="panel-heading">
                    {{ __('batch.execution_summary') }}
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>{{ __('batch.result') }}</th>
                            <th>{{ __('batch.num_ocurrences') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($summary as $result => $numOcurrences)
                            <tr>
                                <td>{{ $result }}</td>
                                <td>{{ $numOcurrences }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Results table --}}
        <div class="panel panel-default">
            <div class="panel-heading">
                {{ __('batch.execution_results') }}
            </div>
            <div class="panel-body">
                @if (is_array($globalResults))
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>{{ __('common.database') }}</th>
                            <th>{{ __('client.client') }}</th>
                            <th>{{ __('batch.result_or_num_results') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($globalResults as $result)
                            <tr>
                                <td>{{ $result['database'] }}</td>
                                <td>{{ $result['clientName'] }}</td>
                                <td>{{ $result['result'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

    {{-- Build one table for each database --}}
    @if(!empty($fullResults))
        @foreach($fullResults as $fullResult)
            @foreach($fullResult as $dbName => $results)
            <div class="panel panel-default">
                <div class="panel-heading">
                    <a id="{{ $dbName }}">{{ $dbName }}</a>
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            @foreach($attributes as $attribute)
                                <th>{{ $attribute }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($results as $result)
                            <tr>
                                @foreach($attributes as $attribute)
                                    <td>{{ $result->$attribute }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        @endforeach
    @endif
</div>
@endsection

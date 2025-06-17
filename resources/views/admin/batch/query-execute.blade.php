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
            {{ urldecode($sqlQueryEncoded) }}
        </div>
    </div>

    {{-- Summary table --}}
    @if ($showSummary && count($summary) > 0)
        <div class="panel panel-default">
            <div class="panel-heading">
                {{ __('batch.execution_summary') }}
            </div>
            <div class="panel-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('batch.num_ocurrences') }}</th>
                            <th>{{ __('batch.result') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Order $summary by its keys, using a custom comparison function.
                        uksort($summary, static function ($a, $b) use ($summary) {
                            if ($summary[$b] === $summary[$a]) {
                                // Compare the keys alphabetically in ascending order.
                                return strcmp($a, $b);
                            }
                            // If the values are different, compare them in descending order.
                            return $summary[$b] - $summary[$a];
                        });
                        ?>
                        @foreach($summary as $result => $numOcurrences)
                            <tr>
                                <td>{{ $numOcurrences }}</td>
                                <td>{{ $result }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Results table --}}
    @if ($showResults && !empty($resultPreviewList))
        <div class="panel panel-default">
            <div class="panel-heading">
                {{ __('batch.execution_results') }}
            </div>
            <div class="panel-body">
                @if ($isSelect)
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('common.database') }}</th>
                                <th>{{ __('client.client') }}</th>
                                <th>{{ __('batch.result_preview') }}</th>
                                <th>{{ __('batch.num_results') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resultPreviewList as $instanceResult)
                                    <?php
                                        $clientURL = $instanceResult['clientDNS'] . '/' . $instanceResult['serviceSlug'];
                                    ?>
                                <tr>
                                    <td><a href="#{{ $instanceResult['database'] }} - {{ $instanceResult['clientName'] }}">{{ $instanceResult['database'] }}</a></td>
                                    <td><a href="/{{ $clientURL }}" target="_blank">{{ $instanceResult['clientName'] }}</a></td>
                                    <td>{{ $instanceResult['preview'] }}</td>
                                    <td>{{ $instanceResult['count'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('common.database') }}</th>
                                <th>{{ __('client.client') }}</th>
                                <th>{{ __('batch.result') }}</th>
                                <th>{{ __('batch.affected_rows') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resultPreviewList as $instanceResult)
                                    <?php
                                        $clientURL = $instanceResult['clientDNS'] . '/' . $instanceResult['serviceSlug'];
                                    ?>
                                <tr>
                                    <td><a href="#{{ $instanceResult['database'] }} - {{ $instanceResult['clientName'] }}">{{ $instanceResult['database'] }}</a></td>
                                    <td><a href="/{{ $clientURL }}" target="_blank">{{ $instanceResult['clientName'] }}</a></td>
                                    <td>{{ $instanceResult['resultStatus'] }}</td>
                                    <td>{{ $instanceResult['affectedRows'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endif

    {{-- Build one table for each database --}}
    @if(!empty($fullResults))
        @foreach($fullResults as $fullResult)
            @foreach($fullResult as $dbName => $results)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ __('batch.result') }}: <a id="{{ $dbName }}">{{ $dbName }}</a>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    {{-- If the attributes are not empty, use them as headers --}}
                                    @if(!empty($attributes))
                                        @foreach($attributes as $attribute)
                                            <th>{{ $attribute }}</th>
                                        @endforeach
                                        {{-- Otherwise, use the first attribute of the first result as header --}}
                                    @else
                                        <th>{{ array_keys(get_object_vars($results[0]))[0] }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $result)
                                    <tr>
                                        {{-- If the attributes are not empty, use them as values --}}
                                        @if(!empty($attributes))
                                            @foreach($attributes as $attribute)
                                                <td>{{ $result->$attribute }}</td>
                                            @endforeach
                                            {{-- Otherwise, use the first attribute of the first result as value --}}
                                        @else
                                            <td>{{ $result->{array_keys(get_object_vars($result))[0]} }}</td>
                                        @endif
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

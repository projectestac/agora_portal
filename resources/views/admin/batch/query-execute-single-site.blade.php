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
                            <th>{{ __('batch.instances_count') }}</th>
                            <th>{{ __('batch.num_ocurrences') }}</th>
                            <th>{{ __('batch.result') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        uksort($summary, static function ($a, $b) use ($summary) {
                            if ($summary[$b] === $summary[$a]) {
                                return strcmp($a, $b);
                            }
                            return $summary[$b] - $summary[$a];
                        });
                        ?>
                        @foreach($summary as $result => $numOcurrences)
                            <tr>
                                <td>{{ $summaryInstances[$result] ?? 0 }}</td>
                                <td>{{ $numOcurrences }}</td>
                                <td>{{ $result }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="panel panel-default">
            <div class="panel-heading">
                {{ __('batch.execution_summary') }}
            </div>
            <div class="panel-body">
                <p>{{ __('batch.result') }}: {{ $globalResults['portal']['result'] }}</p>
            </div>
        </div>
    @endif

    {{-- Build one table for each database --}}
    @if(!empty($fullResults))
        @foreach($fullResults as $fullResult)
            @foreach($fullResult as $dbName => $results)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ __('batch.result') }}: <span id="{{ $dbName }}">{{ $dbName }}</span>
                    </div>
                    <div class="panel-body table-container" id="table-{{ $dbName }}">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    {{-- If the attributes are not empty, use them as headers --}}
                                    @if(!empty($attributes))
                                        @foreach($attributes as $attribute)
                                            <th>{{ $attribute }}</th>
                                        @endforeach
                                    @else
                                        <th>{{ array_keys(get_object_vars($results[0]))[0] }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $result)
                                    <tr>
                                        @if(!empty($attributes))
                                            @foreach($attributes as $attribute)
                                                <td>{{ $result->$attribute }}</td>
                                            @endforeach
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

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
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('batch.instances_count') }}</th>
                            <th>{{ __('batch.num_records') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        uksort($summaryAffectedRows, static function ($a, $b) use ($summaryAffectedRows) {
                            if ($summaryAffectedRows[$b] === $summaryAffectedRows[$a]) {
                                return $a <=> $b;
                            }
                            return $summaryAffectedRows[$b] - $summaryAffectedRows[$a];
                        });
                        ?>
                        @foreach($summaryAffectedRows as $affectedRows => $instancesCount)
                            <tr>
                                <td>{{ $instancesCount }}</td>
                                <td>{{ $affectedRows }}</td>
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
                                <th>{{ __('batch.num_records') }}</th>
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
                                        $resultStatus = $instanceResult['resultStatus'];
                                    ?>
                                <tr style="background-color: {{ $resultStatus['success'] ? '#e8fbe8' : '#fde3e3'}}">
                                    <td><a href="#{{ $instanceResult['database'] }} - {{ $instanceResult['clientName'] }}">{{ $instanceResult['database'] }}</a></td>
                                    <td><a href="/{{ $clientURL }}" target="_blank">{{ $instanceResult['clientName'] }}</a></td>
                                    <td>{{ $resultStatus['message'] }}</td>
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
    @if(!empty($fullResults) && $isSelect)

        <p class="mb-3">
            {{ __('batch.detailed_results') }}:
            <button id="show-all" class="btn btn-success">{{ __('batch.show_all') }}</button>
            <button id="hide-all" class="btn btn-danger">{{ __('batch.hide_all') }}</button>
        </p>

        @foreach($fullResults as $fullResult)
            @foreach($fullResult as $dbName => $results)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a href="#" class="toggle-table" data-target="table-{{ $dbName }}">
                            {{ __('batch.result') }}: <span id="{{ $dbName }}">{{ $dbName }}</span>
                        </a>
                    </div>
                    <div class="panel-body table-container" id="table-{{ $dbName }}" style="display: none;">
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

        <script>
            // JavaScript to handle the toggle functionality for the result tables
            document.addEventListener('DOMContentLoaded', function () {
                const toggleLinks = document.querySelectorAll('.toggle-table');
                toggleLinks.forEach(function (link) {
                    link.addEventListener('click', function (e) {
                        e.preventDefault();
                        const targetId = this.getAttribute('data-target');
                        const targetElement = document.getElementById(targetId);
                        if (targetElement.style.display === 'none') {
                            targetElement.style.display = 'block';
                        } else {
                            targetElement.style.display = 'none';
                        }
                    });
                });
            });

            // Show all tables
            document.getElementById('show-all').addEventListener('click', function () {
                document.querySelectorAll('.table-container').forEach(function (el) {
                    el.style.display = 'block';
                });
            });

            // Hide all tables
            document.getElementById('hide-all').addEventListener('click', function () {
                document.querySelectorAll('.table-container').forEach(function (el) {
                    el.style.display = 'none';
                });
            });
        </script>

    @endif
</div>
@endsection

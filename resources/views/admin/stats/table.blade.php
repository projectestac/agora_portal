@if(isset($results) && count($results) > 0)


    <ul class="nav nav-tabs" id="myTabs">
        <li class="nav-item active">
            <a class="nav-link active" id="table-tab" data-toggle="tab" href="#table">{{ __('common.table') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="chart-tab" data-toggle="tab" href="#chart">{{ __('common.chart') }}</a>
        </li>
    </ul>

    <div class="tab-content" style="padding: 10px 0;">
        <div class="tab-pane fade active in" id="table">
            <table class="table table-striped" id="results">
                <thead>
                    <tr>
                        @foreach($results[0] as $key => $value)
                            <th>{{ __('database-table.' . $key) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $result)
                        <tr>
                            @foreach($result as $value)
                                <td>{{ $value }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="tab-pane fade" id="chart" style="height: 400px">
            <canvas id="myChart" width="800" height="400"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Generating datatable
        $(function () {
            var columnNames = {!! json_encode(array_keys((array) $results[0])) !!};

            $('#results').DataTable({
                processing: true,
                serverSide: false,
                language: {
                    url: '{{ url('/datatable/ca.json') }}'
                },
                lengthMenu: [10, 25, 50, 100, 250],
                pageLength: 25,
                data: {!! json_encode($results) !!},
                columns: columnNames.map(function(column) {
                    return { data: column, name: column };
                })
            });
        });

        // Generating chart (chart config to be changed)
        var ctx = document.getElementById('myChart').getContext('2d'),
            dataTableData = {!! json_encode($results) !!},
            columnNames = {!! json_encode(array_keys((array) $results[0])) !!};

        var chartData = {
            labels: dataTableData.map(function (row) {
                return row[columnNames[0]];
            }),
            datasets: columnNames.slice(1).map(function (columnName, index) {
                return {
                    label: columnName,
                    data: dataTableData.map(function (row) {
                        return row[columnName];
                    }),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                };
            })
        };

        var chartOptions = {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        };

        var myChart = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: chartOptions
        });

    </script>

@else

    <p>{{ __('common.no_results_found') }}</p>

@endif

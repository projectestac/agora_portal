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
            </table>
        </div>

        <div class="tab-pane fade" id="chart" style="height: 400px">

            @if($daily_stats != null)
                <p><i>{{ __('stats.chart_tutorial') }}</i></p>
                <canvas id="myChart" width="800" height="400"></canvas>
            @else
                <p><i>{{ __('stats.chart_unavailable') }}</i></p>
            @endif

        </div>
    </div>

    <script src="{{ secure_asset('js/chart.js') }}"></script>

    <script>
        function formatDate(date) {
            const matches = date.match(/^(\d{4})(\d{2})(\d{2})$/);
            const year = parseInt(matches[1]);
            const month = parseInt(matches[2]) - 1;
            const day = parseInt(matches[3]);

            const formattedDate = new Date(year, month, day);
            const formattedDateString = `${formattedDate.getDate()}/${formattedDate.getMonth() + 1}/${formattedDate.getFullYear()}`;

            return formattedDateString;
        }

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

        @if($daily_stats != null)

            <?php

            $daily_stats = json_decode(json_encode($daily_stats), true);

            $chartData = [
                'labels' => array_map(function ($row) {
                    return date('d/m/Y', strtotime($row['day']));
                }, $daily_stats),
                'datasets' => []
            ];

            $keys = array_keys($daily_stats[0]);
            $colors = [
                'rgba(255, 99, 132, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(54, 162, 235, 0.2)',
            ];
            $index = 0;

            foreach ($keys as $key) {
                if ($key !== 'day') {
                    $chartData['datasets'][] = [
                        'label' => __('database-table.' . $key),
                        'data' => array_map(function ($row) use ($key) {
                            return $row[$key];
                        }, $daily_stats),
                        'backgroundColor' => $colors[$index % count($colors)],
                        'borderColor' => $colors[$index % count($colors)],
                        'borderWidth' => 1
                    ];
                    $index++;
                }
            }

            ?>

            // Generating chart (chart config to be changed)
            var ctx = document.getElementById('myChart').getContext('2d'),
                chartData = <?php echo json_encode($chartData); ?>;

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

        @endif

    </script>

@else

    <p>{{ __('common.no_results_found') }}</p>

@endif

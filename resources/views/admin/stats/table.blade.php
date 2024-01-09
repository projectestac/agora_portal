@if(isset($results) && count($results) > 0)

    <table class="table table-striped" id="results">
        <thead>
            <tr>
                @foreach($results[0] as $key => $value)
                    <th>{{ $key }}</th>
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

    <script>
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
    </script>

@else

    <p>{{ __('common.no_results_found') }}</p>

@endif

@if(!empty($queries))
    @foreach($queries as $query)
        <div class="tab-content query-item" id="query_{{ $query->id }}">
            <a href="#sqlQueryLink" onclick="querySelect({{ $query->id }})">
                <div class="query-description" id="query_description_{{ $query->id }}">{{ $query->description }}</div>
                <div class="query-query" id="query_query_{{ $query->id }}">{{ \Str::limit($query->query, 400) }}</div>
            </a>
            <div class="text-right" role="group">
                <button type="button" class="btn btn-info" onclick="editQuery({{ $query->id }})" title="{{ __('common.edit') }}">
                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                    <span class="sr-only">{{ __('common.edit') }}</span>
                </button>
                <button type="button" class="btn btn-danger" onclick="deleteQuery({{ $query->id }})" title="{{ __('common.delete') }}">
                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                    <span class="sr-only">{{ __('common.delete') }}</span>
                </button>
            </div>
        </div>
    @endforeach

    <script>
        // When a query or a description is clicked, copy the query to the main textarea for later execution.
        function querySelect(id) {
            let query = $('#query_query_' + id).text();
            $('#sqlQuery').val(query);
        }
    </script>
@endif

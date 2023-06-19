@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content batch query">
        <h3>{{ __('batch.query_execution') }}</h3>

        @include('components.messages')

        <a id="sqlQueryLink"></a>
        <form id="queryExecForm" action="{{ route('batch.query.confirm') }}" method="POST">
            @csrf
            <div id="query-container" class="col-md-8">
                <!-- Textarea for SQL query -->
                <div class="form-group">
                    <label for="sqlQuery">{{ __('batch.sql_query') }}</label>
                    <textarea class="form-control" id="sqlQuery" name="sqlQuery" rows="8">@if(!is_null($query)){{ $query }}@endif</textarea>
                    <input type="hidden" id="sqlQueryEncoded" name="sqlQueryEncoded">
                </div>

                <!-- Row of buttons -->
                <div class="form-group">
                    <span class="btn btn-warning" name="clear" onclick="document.getElementById('sqlQuery').value='';">
                        <span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span>
                        {{ __('common.delete') }}
                    </span>
                    <span class="btn btn-info" id="saveQuery" name="saveQuery"
                          data-toggle="modal" data-target="#queryModalAdd">
                        <span class="glyphicon glyphicon-save" aria-hidden="true"></span>
                        {{ __('batch.save_query') }}
                    </span>
                    <button type="submit" class="btn btn-primary" id="runQuery" name="runQuery">
                        <span class="glyphicon glyphicon-flash" aria-hidden="true"></span>
                        {{ __('batch.run_query') }}
                    </button>
                </div>

                <!-- Helper for SQL syntax -->
                <div class="panel panel-default form-inline">
                    <div class="panel-heading">
                        <label for="queryOperation">{{ __('batch.query_syntax') }}</label>
                    </div>
                    <div class="panel-body">
                        <select class="form-control" id="queryOperation" name="queryOperation"
                                onchange="querySyntaxes();">
                            <option value="select">SELECT</option>
                            <option value="insert">INSERT</option>
                            <option value="update">UPDATE</option>
                            <option value="delete">DELETE</option>
                            <option value="alter">ALTER</option>
                            <option value="drop">DROP</option>
                        </select>
                        <span id="querySyntax"></span>
                    </div>
                </div>

                <!-- List of stored queries -->
                <div class="form-group">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ __('batch.saved_queries') }}
                        </div>
                        <div class="panel-body">
                            <ul class="nav nav-tabs">
                                <li id="tab_all" role="presentation" class="active tab-item">
                                    <a href="#" class="btn" onclick="getQueries('all');">{{ __('batch.all') }}</a>
                                </li>
                                <li id="tab_select" role="presentation" class="tab-item">
                                    <a href="#" onclick="getQueries('select');">SELECT</a>
                                </li>
                                <li id="tab_insert" role="presentation" class="tab-item">
                                    <a href="#" onclick="getQueries('insert');">INSERT</a>
                                </li>
                                <li id="tab_update" role="presentation" class="tab-item">
                                    <a href="#" onclick="getQueries('update');">UPDATE</a>
                                </li>
                                <li id="tab_delete" role="presentation" class="tab-item">
                                    <a href="#" onclick="getQueries('delete');">DELETE</a>
                                </li>
                                <li id="tab_alter" role="presentation" class="tab-item">
                                    <a href="#" onclick="getQueries('alter');">ALTER</a>
                                </li>
                                <li id="tab_drop" role="presentation" class="tab-item">
                                    <a href="#" onclick="getQueries('drop');">DROP</a>
                                </li>
                            </ul>
                            <div id="queryList" class="query-list">
                                @include('admin.batch.query-list-item')
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Block to select the clients -->
            <div id="client-selector">
                @include('admin.selector.index')
            </div>
        </form>

        <!-- Modal to save the new query -->
        <div id="queryModalAdd" class="modal fade" role="dialog">
            @include('admin.batch.query-modal-add')
        </div>

        <!-- Modal to save the existing query -->
        <div id="queryModalEdit" class="modal fade" role="dialog"></div>

        <script>
            // Select and show the query syntax examples.
            function querySyntaxes() {
                let operation = document.getElementById('queryOperation').value;
                let syntax = document.getElementById('querySyntax');
                switch (operation) {
                    case "select":
                        syntax.innerHTML = "SELECT [columna] FROM [taules] WHERE [condicions]";
                        break;
                    case "update":
                        syntax.innerHTML = "UPDATE [table] SET [column]=[value] WHERE [conditions]";
                        break;
                    case "delete":
                        syntax.innerHTML = "DELETE FROM [table] WHERE [conditions]";
                        break;
                    case "insert":
                        syntax.innerHTML = "INSERT INTO [table] ([columns]) VALUES ([values])";
                        break;
                    case "alter":
                        syntax.innerHTML = "ALTER TABLE [table] [ADD | ALTER COLUMN] [column] [type]";
                        break;
                    case "drop":
                        syntax.innerHTML = "DROP TABLE [IF EXISTS] [table]";
                        break;
                    default:
                        syntax.innerHTML = "";
                }
            }

            // Show the query syntax example when the page is loaded, not only when the select changes.
            document.addEventListener('DOMContentLoaded', function () {
                querySyntaxes();
                getQueries('all');
                $('#tab_all').addClass('active');
            });

            // Monitor the service dropdown menu in the selector.
            document.getElementById("serviceSel").addEventListener("change", function () {
                getQueries('all');
                $('#tab_all').addClass('active');
            });

            // Get the queries to show in the list of queries.
            function getQueries(filter) {
                event.preventDefault();
                $('.tab-item').removeClass('active');
                $(event.target).parent().addClass('active');
                let $serviceId = $('#serviceSel').val();

                $.ajax({
                    url: '{{ route('queries.index') }}',
                    method: 'GET',
                    data: {
                        filter: filter,
                        serviceId: $serviceId
                    },
                    success: function (response) {
                        $('#queryList').html(response.html);
                    },
                    error: function (xhr, status, error) {
                        console.log('Error on AJAX call:', error);
                    }
                });
            }

            // Generate and show the modal to edit a query.
            function editQuery(id) {
                $.ajax({
                    url: '{{ route('queries.edit', '%%ID%%') }}'.replace('%%ID%%', id),
                    method: 'GET',
                    data: {},
                    success: function (response) {
                        $('#queryModalEdit').html(response.html); // Insert the content in the modal.
                        $('#queryModalEdit').modal('show'); // Show the modal.
                    },
                    error: function (xhr, status, error) {
                        console.log('Error on AJAX call:', error);
                    }
                });
            }

            // Delete a query.
            function deleteQuery(id) {
                let answer = confirm('{{ __('batch.query_removal_confirm') }}');
                if (answer === true) {
                    $.ajax({
                        url: '{{ route('queries.destroy', '%%ID%%') }}'.replace('%%ID%%', id),
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('input[name="_token"]').attr('value')
                        },
                        data: {},
                        success: function (response) {
                            $('#query_' + id).html(response.html);
                        },
                        error: function (xhr, status, error) {
                            console.log('Error on AJAX call:', error);
                        }
                    });
                }
            }

            // Encode query in base64 before submitting the form to avoid blockages from firewalls.
            $('#queryExecForm').submit(function () {
                let queryPlain = $('#sqlQuery').val();
                let queryEncoded = btoa(queryPlain);
                $('#sqlQueryEncoded').val(queryEncoded);
                $('#sqlQuery').val(''); // Clear the textarea to avoid sending the query in plain text.
            });
        </script>
    </div>
@endsection

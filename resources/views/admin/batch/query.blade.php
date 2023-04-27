@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content batch query">
        <h3>{{ __('batch.query_execution') }}</h3>
        <form>
            @csrf
            <div id="query-container" class="col-md-8">
                <div class="form-group">
                    <label for="sqlQuery">{{ __('batch.sql_query') }}</label>
                    <textarea class="form-control" id="sqlQuery" name="sqlQuery" rows="8"></textarea>
                </div>

                <div class="form-group">
                    <span class="btn btn-warning" name="clear" onclick="document.getElementById('sqlQuery').value='';">
                        <span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span>
                        {{ __('common.delete') }}
                    </span>
                    <span class="btn btn-info" id="saveQuery" name="saveQuery" data-toggle="modal" data-target="#queryModal">
                        <span class="glyphicon glyphicon-save" aria-hidden="true"></span>
                        {{ __('batch.save_query') }}
                    </span>
                    <button type="submit" class="btn btn-primary" id="run-query" name="runQuery">
                        <span class="glyphicon glyphicon-flash" aria-hidden="true"></span>
                        {{ __('batch.run_query') }}
                    </button>
                </div>

                <div class="panel panel-default form-inline">
                    <div class="panel-heading">Mostra un exemple de l'operació:</div>
                    <div class="panel-body">
                        <select class="form-control" id="sqloperation" onchange="sqlExampleUpdate();">
                            <option value=""></option>
                            <option value="SELECT">SELECT</option>
                            <option value="INSERT">INSERT</option>
                            <option value="UPDATE">UPDATE</option>
                            <option value="DELETE">DELETE</option>
                            <option value="ALTER">ALTER</option>
                            <option value="DROP">DROP</option>
                        </select>
                        <span id="sqlexample">INSERT INTO [taula] ([columnes]) VALUES ([valors])</span>
                    </div>
                </div>

                <div class="form-group">
                    <div class="panel panel-default">
                        <div class="panel-heading">Comandes desades: <span id="msg"></span></div>
                        <div class="panel-body">
                            <input type="hidden" id="selected_tab" value="select">
                            <ul class="nav nav-tabs">
                                <li id="tab_all" role="presentation" class="">
                                    <a href="#msg" onclick="sqlComandsUpdateTab('all');">Totes</a>
                                </li>
                                <li id="tab_select" role="presentation" class="active">
                                    <a href="#msg" onclick="sqlComandsUpdateTab('select');">SELECT</a>
                                </li>
                                <li id="tab_insert" role="presentation" class="">
                                    <a href="#msg" onclick="sqlComandsUpdateTab('insert');">INSERT</a>
                                </li>
                                <li id="tab_update" role="presentation" class="">
                                    <a href="#msg" onclick="sqlComandsUpdateTab('update');">UPDATE</a>
                                </li>
                                <li id="tab_delete" role="presentation" class="">
                                    <a href="#msg" onclick="sqlComandsUpdateTab('delete');">DELETE</a>
                                </li>
                                <li id="tab_alter" role="presentation" class="">
                                    <a href="#msg" onclick="sqlComandsUpdateTab('alter');">ALTER</a>
                                </li>
                                <li id="tab_drop" role="presentation" class="">
                                    <a href="#msg" onclick="sqlComandsUpdateTab('drop');">DROP</a>
                                </li>
                            </ul>
                            <div id="commandList" style="max-height:450px; overflow:auto;">
                                <div class="">
                                    <div class="tab-content">
                                        <a href="#sqlForm" onclick="sqlFunctionUpdate('insert', 9)" id="comandBox_" 9="" title="Insereix">
                                            <div class="">
                                                Compta el nombre d'activitats H5P
                                                <div style="font-size: 0.8em; color: #999;">SELECT count(*) FROM m2hvp</div>
                                            </div>
                                            <div class="text-right" role="group">
                                                <button type="button" class="btn btn-info" onclick="sqlFunctionUpdate('edit', 9)"
                                                        title="Modifica">
                                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                    <span class="sr-only">Modifica</span>
                                                </button>
                                                <button type="button" class="btn btn-danger" onclick="sqlDelete(9)" title="Esborra">
                                                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                                    <span class="sr-only">Esborra</span>
                                                </button>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="tab-content">
                                        <a href="#sqlForm" onclick="sqlFunctionUpdate('insert', 12)" id="comandBox_" 12="" title="Insereix">
                                            <div class="">
                                                Darrer accés d'un usuari que no és xtecadmin
                                                <div style="font-size: 0.8em; color: #999;">SELECT date(to_timestamp(MAX(lastaccess))) FROM m2user
                                                    WHERE username!='xtecadmin' AND username!='guest' AND confirmed=1 AND suspended=0 AND
                                                    deleted=0
                                                </div>
                                            </div>
                                            <div class="text-right" role="group">
                                                <button type="button" class="btn btn-info" onclick="sqlFunctionUpdate('edit', 12)"
                                                        title="Modifica">
                                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                    <span class="sr-only">Modifica</span>
                                                </button>
                                                <button type="button" class="btn btn-danger" onclick="sqlDelete(12)" title="Esborra">
                                                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                                    <span class="sr-only">Esborra</span>
                                                </button>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="client-selector">@include('admin.selector.index')</div>
        </form>

        <div id="queryModal" class="modal fade" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="panel-info">
                        <div class="panel-heading">
                            {{ __('batch.save_query') }}
                            <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('common.close') }}">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="panel-body">
                            <form action="{{ route('queries.store') }}" method="POST">
                                @csrf
                                <div class="form-group" id="serviceSelCopy">
                                    <label for="serviceSelModal">{{ __('service.service') }}</label>
                                </div>
                                <div class="form-group">
                                    <label for="sqlQueryModal">{{ __('batch.sql_query') }}</label>
                                    <textarea class="form-control" id="sqlQueryModal" name="sqlQueryModal" rows="4"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="descriptionModal">{{ __('batch.add_description') }}</label>
                                    <textarea class="form-control" id="descriptionModal" name="descriptionModal" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="queryTypeModal">{{ __('batch.query_type') }}</label>
                                    <select class="form-control" id="queryTypeModal" name="queryType">
                                        <option value=""></option>
                                        <option value="select">SELECT</option>
                                        <option value="insert">INSERT</option>
                                        <option value="update">UPDATE</option>
                                        <option value="delete">DELETE</option>
                                        <option value="alter">ALTER</option>
                                        <option value="drop">DROP</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> {{ __('common.save') }}
                                </button>
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span> {{ __('common.cancel') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('saveQuery').addEventListener('click', function() {
                // Copy the query to the modal.
                let sourceTextarea = document.getElementById('sqlQuery');
                let destinationTextarea = document.getElementById('sqlQueryModal');
                destinationTextarea.value = sourceTextarea.value;

                // Copy the service menu to the modal.
                let originalSelect = document.getElementById('service-sel');
                let destinationSelect = document.getElementById('serviceSelCopy');
                let copiedSelect = document.getElementById('serviceSelModal');

                if (copiedSelect) {
                    copiedSelect.remove();
                }

                let clonedSelect = originalSelect.cloneNode(true);
                clonedSelect.id = 'serviceSelModal';
                clonedSelect.name = 'serviceSelModal';
                destinationSelect.appendChild(clonedSelect);
            });
        </script>
@endsection

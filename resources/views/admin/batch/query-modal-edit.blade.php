<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="panel-info">
            <div class="panel-heading">
                {{ __('batch.update_query') }}
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('common.close') }}">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="panel-body">
                <form id="editQueryForm" action="{{ route('queries.update', $query->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="form-group" id="serviceSelEdit">
                        <label for="serviceSelModalEdit">{{ __('service.service') }}</label>
                        <select class="form-control" id="serviceSelModalEdit" name="serviceSelModalEdit">
                            @foreach($services as $service)
                                <option value="{{ $service['id'] }}" {{ $service['id'] === $query->service_id ? 'selected' : '' }}>
                                    {{ $service['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sqlQueryModalEdit">{{ __('batch.sql_query') }}</label>
                        <textarea class="form-control" id="sqlQueryModalEdit" name="sqlQueryModalEdit" rows="4">{{ $query->query }}</textarea>
                        <input type="hidden" id="sqlQueryModalEditEncoded" name="sqlQueryModalEditEncoded">
                    </div>
                    <div class="form-group">
                        <label for="descriptionModalEdit">{{ __('batch.add_description') }}</label>
                        <textarea class="form-control" id="descriptionModalEdit" name="descriptionModalEdit"
                                  rows="3">{{ $query->description }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="queryTypeModalEdit">{{ __('batch.query_type') }}</label>
                        <select class="form-control" id="queryTypeModalEdit" name="queryTypeModalEdit">
                            <option value="select" {{ $query->type === 'select' ? 'selected' : '' }}>SELECT</option>
                            <option value="insert" {{ $query->type === 'insert' ? 'selected' : '' }}>INSERT</option>
                            <option value="update" {{ $query->type === 'update' ? 'selected' : '' }}>UPDATE</option>
                            <option value="delete" {{ $query->type === 'delete' ? 'selected' : '' }}>DELETE</option>
                            <option value="alter" {{ $query->type === 'alter' ? 'selected' : '' }}>ALTER</option>
                            <option value="drop" {{ $query->type === 'drop' ? 'selected' : '' }}>DROP</option>
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

<script>
    // Encode query in base64 before submitting the form to avoid blockages from firewalls.
    $('#editQueryForm').submit(function () {
        let queryPlain = $('#sqlQueryModalEdit').val();
        let queryEncoded = btoa(queryPlain);
        $('#sqlQueryModalEditEncoded').val(queryEncoded);
        $('#sqlQueryModalEdit').val(''); // Clear the textarea to avoid sending the query in plain text.
    });
</script>

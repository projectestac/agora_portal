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
                <form id="saveQueryForm" action="{{ route('queries.store') }}" method="POST">
                    @csrf
                    <div class="form-group" id="serviceSelCopy">
                        <label for="serviceSelModal">{{ __('service.service') }}</label>
                    </div>
                    <div class="form-group">
                        <label for="sqlQueryModalAdd">{{ __('batch.sql_query') }}</label>
                        <textarea class="form-control" id="sqlQueryModalAdd" name="sqlQueryModalAdd" rows="4"></textarea>
                        <input type="hidden" id="sqlQueryModalAddEncoded" name="sqlQueryModalAddEncoded">
                    </div>
                    <div class="form-group">
                        <label for="descriptionModalAdd">{{ __('batch.add_description') }}</label>
                        <textarea class="form-control" id="descriptionModalAdd" name="descriptionModalAdd" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="queryTypeModalAdd">{{ __('batch.query_type') }}</label>
                        <select class="form-control" id="queryTypeModalAdd" name="queryTypeModalAdd">
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

<script>
    // Event to copy the query and the service id to the modal.
    document.getElementById('saveQuery').addEventListener('click', function () {
        // Copy the query to the modal.
        let sourceTextarea = document.getElementById('sqlQuery');
        let destinationTextarea = document.getElementById('sqlQueryModalAdd');
        destinationTextarea.value = sourceTextarea.value;

        // Copy the service menu to the modal.
        let originalSelect = document.getElementById('serviceSel');
        let destinationSelect = document.getElementById('serviceSelCopy');
        let copiedSelect = document.getElementById('serviceSelModal');

        if (copiedSelect) {
            copiedSelect.remove();
        }

        let clonedSelect = originalSelect.cloneNode(true);
        clonedSelect.id = 'serviceSelModal';
        clonedSelect.name = 'serviceSelModal';
        let selectedOption = originalSelect.options[originalSelect.selectedIndex];
        clonedSelect.value = selectedOption.value;
        clonedSelect.selectedIndex = selectedOption.index;
        destinationSelect.appendChild(clonedSelect);
    });

    // Encode query in base64 before submitting the form to avoid blockages from firewalls.
    $('#saveQueryForm').submit(function () {
        let queryPlain = $('#sqlQueryModalAdd').val();
        let queryEncoded = btoa(queryPlain);
        $('#sqlQueryModalAddEncoded').val(queryEncoded);
        $('#sqlQueryModalAdd').val(''); // Clear the textarea to avoid sending the query in plain text.
    });
</script>

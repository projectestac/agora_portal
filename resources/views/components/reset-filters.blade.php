<button id="reset-filters" class="btn btn-primary">
    <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> {{ __('common.reset_filters') }}
</button>

<script>
    $(function () {
        $('#reset-filters').on('click', function () {
            $('#{{ $filtersContainerId }}').find('select').val('');
            $('#{{ $datatableId }}').DataTable().ajax.reload();
        });
    });
</script>

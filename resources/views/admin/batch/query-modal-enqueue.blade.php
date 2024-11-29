<div class="modal fade" id="confirmationModal_{{ $item['id'] }}" tabindex="-1"
    aria-labelledby="confirmationModalTitle_{{ $item['id'] }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="panel-info">
                <div class="panel-heading">
                    {{ __('batch.confirm_execution') }}
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('common.close') }}">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
            </div>
            <div class="panel-body">
                {{ __('batch.confirm_run_action_again') }}
            </div>
            <div class="panel-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="submitEnqueueForm('{{ $item['id'] }}')">
                    {{ __('batch.confirm_execution') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function submitEnqueueForm(itemId) {
   document.getElementById('enqueue-form-' + itemId).submit();
}
</script>

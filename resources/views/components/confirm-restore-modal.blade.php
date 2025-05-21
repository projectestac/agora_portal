<button type="button" class="btn btn-success" data-toggle="modal" data-target="#confirmRestoreModal{{ $id }}">
    <span class="glyphicon glyphicon-wrench" aria-hidden="true"></span>
</button>

<div class="modal fade" id="confirmRestoreModal{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="confirmRestoreModalLabel{{ $id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="text-align: left;">
                <h2 class="modal-title text-success" id="confirmRestoreModalLabel{{ $id }}">{{ __('common.warning_restore') }}</h2>
            </div>
            <div class="modal-body" style="text-align: left;">
                {{ __('common.confirm_restoration') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.cancel') }}</button>
                <form action="{{ $restoreRoute }}" method="POST" style="display:inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success">{{ __('common.restore') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

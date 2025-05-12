{{-- We can't delete the admin role or user --}}
@if (!(strtolower($name) === 'admin' && (str_contains($route, 'role') || str_contains($route, 'user'))))
    {{-- Button trigger modal --}}
    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#confirmDeleteModal{{ $id }}">
        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
    </button>

    <div class="modal fade" id="confirmDeleteModal{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel{{ $id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="text-align: left;">
                    <h2 class="modal-title text-danger" id="confirmDeleteModalLabel{{ $id }}">{{ __('common.warning_delete') }}</h2>
                </div>
                <div class="modal-body" style="text-align: left;">
                    {{ __('common.confirm_deletion') }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.cancel') }}</button>
                    <form action="{{ $route }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">{{ __('common.delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

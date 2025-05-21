<div style="text-align: right; margin-right: 20px;">

    {{-- Users already soft-deleted don't show delete or edit button --}}
    @if(!$user->deleted_at)
        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
        </a>

        @include('components.confirm-delete-modal', [
            'id' => $user->id,
            'name' => $user->name,
            'route' => route('users.destroy', $user->id)
        ])
    @else
        @include('components.confirm-restore-modal', [
            'id' => $user->id,
            'restoreRoute' => route('users.restore', $user->id)
        ])
    @endif

</div>

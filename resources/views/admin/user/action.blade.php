<div style="text-align: right; margin-right: 20px;">

    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
    </a>

    {{-- Users already soft-deleted don't show delete button --}}
    @if(!$user->deleted_at)
        @include('components.confirm-delete-modal', [
            'id' => $user->id,
            'name' => $user->name,
            'route' => route('users.destroy', $user->id)
        ])
    @endif

</div>

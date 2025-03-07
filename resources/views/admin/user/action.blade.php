<div style="text-align: right; margin-right: 20px;">
    @if ($user->hasRole('manager'))
        <a href="{{ route('manager.showManager', $user) }}" class="btn btn-warning" title="{{ __('manager.manager') }}">
            <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
        </a>
    @endif

    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
    </a>

    <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" title="{{ __('common.delete') }}"
                onclick="return confirm('{{ __('user.deleteUser_confirm') }}');">
            <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
        </button>
    </form>
</div>

<a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
</a>

@include('components.confirm-delete-modal', [
    'id' => $user->id,
    'name' => $user->name,
    'route' => route('users.destroy', $user->id)
])

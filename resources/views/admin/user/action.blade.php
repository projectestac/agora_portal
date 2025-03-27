<a href="{{ route('users.show', $user->id) }}" class="btn btn-info" title="{{ __('common.show') }}">
    <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
</a>
<a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
</a>

@include('components.confirm-delete-modal', [
    'id' => $user->id,
    'name' => $user->name,
    'route' => route('users.destroy', $user->id)
])

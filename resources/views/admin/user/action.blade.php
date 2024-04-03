{{-- <a href="{{ route('users.show', $user->id) }}" class="btn btn-info" title="{{ __('common.show') }}">
    <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
</a> --}}
{{-- {{dump($user->manager)}} --}}
@if ($user->hasRole('manager'))
    <a href="{{ route('manager.wipFunction', $user) }}" class="btn btn-warning" title="{{ __('manager.manager') }}">
        <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
    </a>
@endif
<a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
</a>
<form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger" title="{{ __('common.delete') }}">
        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
    </button>
</form>

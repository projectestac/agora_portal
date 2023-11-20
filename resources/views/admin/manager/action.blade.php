<a href="{{ route('managers.show', $manager->id) }}" class="btn btn-info" title="{{ __('common.show') }}">
    <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
</a>
<a href="{{ route('managers.edit', $manager->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
</a>
<form id="remove_manager_{{ $manager->id }}" action="{{ route('managers.destroy', $manager->id) }}" method="POST" style="display: inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger" title="{{ __('common.delete') }}" onclick="confirmDelete({{ $manager->id }});">
        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
    </button>
</form>

<script>
    function confirmDelete(id) {
        event.preventDefault();
        if (confirm('{{ __('manager.remove_manager_confirm') }}')) {
            document.getElementById('remove_manager_' + id).submit();
        }
    }
</script>

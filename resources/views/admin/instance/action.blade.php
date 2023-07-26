<a href="{{ route('instances.show', $instance->id) }}" class="btn btn-info" title="{{ __('common.show') }}">
    <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
</a>
<a href="{{ route('instances.edit', $instance->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
</a>
<form action="{{ route('instances.destroy', $instance->id) }}" method="POST" style="display: inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger" title="{{ __('common.delete') }}">
        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
    </button>
</form>

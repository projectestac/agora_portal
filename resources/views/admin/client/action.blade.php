<a href="{{ route('clients.edit', $client->id) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
</a>
<!--
<form action="{{ route('clients.destroy', $client->id) }}" method="POST" style="display: inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger" title="{{ __('common.delete') }}">
        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
    </button>
</form>
-->

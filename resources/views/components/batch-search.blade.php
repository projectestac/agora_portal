<form method="GET" action="{{ $action }}" class="form-inline" style="margin-bottom: 15px;">
    <div class="form-group">
        <input type="text" name="q" value="{{ request('q') }}"
               class="form-control" placeholder="{{ __('common.keyword') }}">
    </div>

    <button type="submit" class="btn btn-primary" style="margin-left: 10px;">
        {{ __('common.search') }}
    </button>

    <a href="{{ $action }}" class="btn btn-default" style="margin-left: 5px;">
        {{ __('common.reset') }}
    </a>
</form>

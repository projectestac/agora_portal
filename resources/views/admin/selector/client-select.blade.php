<div class="form-group">
    <label for="clientsSel">
        {{ count($viewData['instances']) }} {{ __('client.clients') }} [{{ $viewData['selectedService']['name'] }}]
    </label>
    <select class="form-control" id="clientsSel" name="clientsSel[]" size="15" multiple="multiple">
        @foreach($viewData['instances'] as $instance)
            <option value="{{ $instance['id'] }}">
                {{ $instance['db_id'] }} - {{ $instance['code'] }} - {{ $instance['name'] }} - {{ $instance['dns'] }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="clients_sel">
        {{ count($viewData['instances']) }} {{ __('client.clients') }} [{{ $viewData['selectedService']['name'] }}]
    </label>
    <select class="form-control" id="clients_sel" name="clients_sel[]" size="15" multiple="multiple" style="width:100%;">
        @foreach($viewData['instances'] as $instance)
            <option value="{{ $instance['id'] }}">
                {{ $instance['db_id'] }} - {{ $instance['code'] }} - {{ $instance['name'] }} - {{ $instance['dns'] }}
            </option>
        @endforeach
    </select>
</div>

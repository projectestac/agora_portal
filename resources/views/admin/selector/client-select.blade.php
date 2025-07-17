<div class="form-group">
    <input type="file" id="fileInput" accept=".txt,.csv" style="display:none;" />

    <button type="button" class="btn btn-primary" id="btnSelectFile">
        {{ __('batch.select_file_clients') }}
    </button>

    <button type="button" class="btn btn-secondary mt-2" id="btnDeselectAll">
        {{ __('batch.deselect_all') }}
    </button>

    <i><p id="selectedClientsCount"></p></i>
</div>

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

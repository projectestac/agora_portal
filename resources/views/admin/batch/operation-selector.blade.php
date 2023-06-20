@if(is_array($operations))
    <label for="operationAction" class="form-label">{{ __('batch.action') }}</label>
    <select id="operationAction" class="form-control">
        <option value="none">{{ __('common.choose_an_option') }}</option>
        @foreach ($operations as $operation)
            <option value="{{ $operation['action'] }}"
                    @if($action['action'] === $operation['action']) selected="selected" @endif>
                {{ $operation['title'] }}
            </option>
        @endforeach
    </select>

    <br>
    <label>{{ __('common.description') }}</label>
    <div class="alert alert-info">{!! $action['description'] !!}</div>

    <br>
    @if(!empty($action['params']))
        <label>{{ __('common.params') }}</label>
        <div class="form-horizontal">
            @foreach($action['params'] as $param)
                <div class="form-group">
                    <label for="{{ $action['action'] }}-{{ $param }}" class="col-sm-4 control-label">
                        {{ $param }}
                    </label>
                    <div class="col-sm-8">
                        <input class="form-control" type="text" id="{{ $action['action'] }}-{{ $param }}" name="{{ $action['action'] }}-{{ $param }}">
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@else
    <div class="alert alert-danger" role="alert">{{ $operations }}</div>
@endif



<script>
    document.getElementById("operationAction").addEventListener("change", function () {
        let serviceId = $('#serviceSel').val();
        let action = $('#operationAction').val();
        updateAction(action, serviceId);
    });

    function updateAction(action, serviceId) {
        $.ajax({
            url: '{{ route('batch.operation', '%%ID%%') }}'.replace('%%ID%%', action),
            method: 'GET',
            data: {
                serviceId: serviceId
            },
            success: function (response) {
                $('#operationContainer').html(response.html);
            },
            error: function (xhr, status, error) {
                console.error('Error on AJAX call:', error);
            }
        });
    }
</script>

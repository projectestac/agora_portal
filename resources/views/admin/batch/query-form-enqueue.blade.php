<form id="enqueue-form-{{ $item['id'] }}"
        action="{{ route('batch.operation.enqueueFromInputs') }}"
        method="POST">
    @csrf

    <input type="hidden" name="action" value="{{ $item['operation_data']['action'] }}">
    <input type="hidden" name="priority" value="{{ $item['operation_data']['priority'] }}">
    <input type="hidden" name="params"
            value="{{ json_encode($item['operation_data']['params'], JSON_THROW_ON_ERROR) }}">
    <input type="hidden" name="service_name" value="{{ $item['operation_data']['service_name'] }}">
    <input type="hidden" name="instance_id" value="{{ $item['operation_data']['instance_id'] }}">
    <input type="hidden" name="instance_name" value="{{ $item['operation_data']['instance_name'] }}">
    <input type="hidden" name="instance_dns" value="{{ $item['operation_data']['instance_dns'] }}">

    <div class="row form-inline clear text-center">
        <button type="button" class="btn btn-info" title="{{ __('batch.run_again') }}"
                data-toggle="modal" data-target="#confirmationModal_{{ $item['id'] }}">
            <span class="glyphicon glyphicon-repeat" aria-hidden="true"></span>
        </button>
    </div>
</form>

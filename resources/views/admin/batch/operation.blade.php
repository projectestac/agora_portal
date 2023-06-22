@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content batch operation">
        <h3>{{ __('batch.operations') }}</h3>

        @include('components.messages')

        <form action="{{ route('batch.operation.confirm', ['action' => $action['action']]) }}" method="POST">
            @csrf

            <!-- Block to select the operation and fill the parameters list -->
            <div id="operationContainer" class="col-md-8">
                @include('admin.batch.operation-selector')
            </div>

            <!-- Block to select the clients -->
            <div id="client-selector">
                @include('admin.selector.index')
            </div>

            <div class="row form-inline clear text-center">
                <label for="priority">{{ __('batch.priority') }}</label>
                <select class="form-control" id="priority" name="priority" style="width:auto;">
                    @foreach($priority as $key => $value)
                        <option value="{{ $key }}" @if($value === __('batch.medium')) selected="selected" @endif>{{ $value }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">{{ __('common.continue') }}</button>
            </div>
        </form>

    </div>

    <script>
        document.getElementById("serviceSel").addEventListener("change", function () {
            let serviceId = $('#serviceSel').val();
            let action = $('#operationAction').val();
            updateAction(action, serviceId);
        });
    </script>
@endsection

@extends('layout.default')

@section('content')
<div class="admin-menu-container">
    @include('menu.adminmenu')
</div>

<div class="content batch instances">
    <h3>{{ __('instance.instance_list') }}</h3>

    @include('components.messages')

    <div class="mt-3" style="display: flex; margin-bottom: 20px">
        <select id="new-status" class="form-control" style="width: auto; margin-right: 10px">
            <option value="">{{ __('common.status') }}</option>
            @foreach($statusList as $key => $status)
                <option value="{{ $key }}">{{ $status }}</option>
            @endforeach
        </select>
        <button id="apply-changes" class="btn btn-primary mt-2">
            {{ __('common.change_statuses') }}
        </button>
    </div>

    <table id="instances-table" class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ __('client.name') }}</th>
                <th>{{ __('client.dns') }}</th>
                <th>{{ __('client.code') }}</th>
                <th>{{ __('instance.db_id') }}</th>
                <th>{{ __('common.status') }}</th>
                <th>{{ __('service.service') }}</th>
                <th>
                    <input type="checkbox" id="select-all">
                </th>
            </tr>
        </thead>
    </table>
</div>

<div id="confirmation-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('common.confirm_changes') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ __('common.are_you_sure_to_change_statuses') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" id="confirm-apply" class="btn btn-primary">{{ __('common.yes') }}</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.no') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let table = $('#instances-table').DataTable({
            processing: true,
            serverSide: true,
            language: {
                url: '{{ url('/datatable/ca.json') }}'
            },
            lengthMenu: [10, 25, 50, 100, 250, 500],
            pageLength: 100,
            ajax: '{{ route('instances.list') }}',
            columns: [
                { data: 'id' },
                { data: 'client_name' },
                { data: 'client_dns' },
                { data: 'client_code' },
                { data: 'db_id' },
                { data: 'status' },
                { data: 'service_id' },
                { data: 'checkbox', orderable: false, searchable: false }
            ]
        });

        $('#select-all').on('click', function() {
            let rows = table.rows({ 'search': 'applied' }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });

        $('#apply-changes').on('click', function() {
            let selected = [];
            table.$('input[type="checkbox"]:checked').each(function() {
                selected.push($(this).data('id'));
            });

            let newStatus = $('#new-status').val();
            if (selected.length && newStatus) {
                $('#confirmation-modal').modal('show');
            } else {
                console.log('{{ __('status.select_instance_and_status') }}');
            }

            $('#confirm-apply').on('click', function() {
                $.ajax({
                    url: '{{ route('batch.instances.updateStatus') }}',
                    method: 'POST',
                    data: {
                        ids: selected,
                        status: newStatus,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        table.ajax.reload();
                        $('#confirmation-modal').modal('hide');
                        $('#select-all').prop('checked', false);
                    },
                    error: function() {
                        console.log('{{ __('common.error_occurred') }}');
                        console.log(response.message);
                    }
                });
            });
        });
    });
</script>

@endsection

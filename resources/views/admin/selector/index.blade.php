<div class="col-md-4">
    <div class="form-group">
        <label for="service-sel">{{ __('service.service') }}</label>
        <select class="form-control" id="service-sel" name="service-sel" style="width:100%;">
            @foreach($viewData['services'] as $service)
                <option value="{{ $service['id'] }}"
                        @if($service['selected']) selected="selected" @endif>
                    {{ $service['name'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <label for="service-selector">{{ __('batch.client_selection') }}</label>
            <span id="reload"></span>
        </div>
        <div class="panel-body">
            <select class="form-control" id="service-selector" name="service-selector" style="width:100%;">
                <option value="all" selected="selected">{{ __('batch.all_clients') }}</option>
                <option value="selected">{{ __('batch.only_selected') }}</option>
            </select>
            <br/>

            <div id="search-engine" style="display:none;">
                <div id="clientslist" name="clients-list" style="width:100%;">
                    @include('admin.selector.client-select')
                </div>

                <div class="form-group">
                    <label for="order">{{ __('batch.order_by') }}</label>
                    <select class="form-control" id="order" name="order" style="width:100%;">
                        <option value="clientname" selected="selected">{{ __('client.name') }}</option>
                        <option value="dbid">{{ __('instance.db_id') }}</option>
                        <option value="clientcode">{{ __('client.code') }}</option>
                        <option value="dns">{{ __('client.dns') }}</option>
                    </select>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading"><label for="search">{{ __('batch.search_engine') }}</label></div>
                    <div class="panel-body">
                        <div class="form-inline form-group">
                            <select class="form-control" id="search" name="search">
                                <option value="code">{{ __('client.code') }}</option>
                                <option value="clientname">{{ __('client.name') }}</option>
                                <option value="town">{{ __('common.town') }}</option>
                                <option value="dns">{{ __('client.dns') }}</option>
                                <option value="dbid">{{ __('instance.db_id') }}</option>
                            </select>
                            <input class="form-control" id="textToSearch" name="textToSearch" type="text" value="" size="20">
                        </div>
                        <div class="form-control btn btn-primary" id="submit-search">
                            <span class="glyphicon glyphicon-filter" aria-hidden="true"></span> {{ __('common.search') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Show/Hide the client selector and the search box.
        $('#service-selector').on('change', function () {
            let selectedOption = $(this).val();
            if (selectedOption === 'selected') {
                $('#search-engine').show();
            } else {
                $('#search-engine').hide();
            }
        });

        // Update the client list using AJAX. Honors the selected service, the order and the search box.
        function filter_client_list() {
            let serviceSel = $('#service-sel').val();
            let order = $('#order').val();
            let search = $('#search').val();
            let textToSearch = $('#textToSearch').val();

            if (serviceSel !== '' && order !== '' && search !== '') {
                $.ajax({
                    url: '{{ route('search') }}',
                    method: 'GET',
                    data: {
                        servicesel: serviceSel,
                        order: order,
                        search: search,
                        texttosearch: textToSearch
                    },
                    success: function (response) {
                        $('#clientslist').html(response.html);
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                    }
                });
            }
        }

        // Bind the events to the elements.
        $('#order').on('change', filter_client_list);
        $('#submit-search').on('click', filter_client_list);
        $('#service-sel').on('change', filter_client_list);

        // If the user selects service "portal" then hide the client list and the search box.
        $('#service-sel').on('change', function () {
            let serviceSel = $('#service-sel').val();
            if (serviceSel === '0') {
                $('#search-engine').hide();
                $('#service-selector').val('all');
            }
        });
    });
</script>

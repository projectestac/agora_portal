<div class="col-md-4">
    <div class="form-group">
        <label for="serviceSel">{{ __('service.service') }}</label>
        <select class="form-control" id="serviceSel" name="serviceSel">
            @foreach($viewData['services'] as $service)
                <option value="{{ $service['id'] }}"
                    @if(isset($serviceSel) && $serviceSel == $service['id']) selected="selected" @endif>
                    {{ $service['name'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <label for="serviceSelector">{{ __('batch.client_selection') }}</label>
            <span id="reload"></span>
        </div>
        <div class="panel-body">
            <select class="form-control" id="serviceSelector" name="serviceSelector">
                <option value="all" selected="selected">{{ __('batch.all_clients') }}</option>
                <option value="selected">{{ __('batch.only_selected') }}</option>
            </select>
            <br/>

            <div id="searchEngine" style="display:none;">
                <div id="clientslist" name="clients-list">
                    @include('admin.selector.client-select')
                </div>

                <div class="form-group">
                    <label for="order">{{ __('batch.order_by') }}</label>
                    <select class="form-control" id="order" name="order">
                        <option value="clientname" selected="selected">{{ __('client.name') }}</option>
                        <option value="dbid">{{ __('instance.db_id') }}</option>
                        <option value="clientcode">{{ __('client.code') }}</option>
                        <option value="dns">{{ __('client.dns') }}</option>
                    </select>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <label for="search">{{ __('batch.search_engine') }}</label>
                    </div>
                    <div class="panel-body">
                        <div class="form-inline form-group">
                            <select class="form-control" id="search" name="search">
                                <option value="code">{{ __('client.code') }}</option>
                                <option value="clientname">{{ __('client.name') }}</option>
                                <option value="city">{{ __('common.town') }}</option>
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
        $('#serviceSelector').on('change', function () {
            let selectedOption = $(this).val();
            if (selectedOption === 'selected') {
                $('#searchEngine').show();
            } else {
                $('#searchEngine').hide();
            }
        });

        // Update the client list using AJAX. Honors the selected service, the order and the search box.
        function filter_client_list() {
            $('#reload').html('<span class="glyphicon glyphicon-refresh"></span>');

            let serviceSel = $('#serviceSel').val();
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
                        $('#reload').html('');

                        // Binding file selector to logic
                        $('#fileInput').on('change', function() {
                            const file = this.files[0];
                            if (file) {
                                selectClientsFromFile(file);
                            }
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                        $('#reload').html('');
                    }
                });
            }
        }

        // Bind the events to the elements.
        $('#order').on('change', filter_client_list);
        $('#submit-search').on('click', filter_client_list);
        $('#serviceSel').on('change', filter_client_list);

        // If the user selects service "portal" then hide the client list and the search box.
        $('#serviceSel').on('change', function () {
            let serviceSel = $('#serviceSel').val();
            if (serviceSel === '0') {
                $('#searchEngine').hide();
                $('#serviceSelector').val('all');
            }
        });

        function selectClientsFromFile(file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                const text = e.target.result;

                // Split into lines, remove empty lines
                const lines = text.split(/\r?\n/).filter(line => line.trim().length > 0);

                // Remove the header line
                lines.shift();

                // Extract the 'code' column (2nd column, index 1)
                const codesFromFile = lines.map(line => line.split(',')[1].trim());

                console.log(codesFromFile);

                let selectedCount = 0;

                // Select options in the #clientslist select whose value matches one of the codes from the file
                // Assuming each <option>'s value is the client code
                $('#clientslist option').each(function() {
                    const optionText = $(this).text().trim();

                    // Extract the code from text like "1 - a0000001 - Client 1 - centre-1"
                    // We split by ' - ' and take the second part (index 1)
                    const codeInText = optionText.split(' - ')[1];

                    console.log(codeInText);

                    if (codesFromFile.includes(codeInText)) {
                        $(this).prop('selected', true);
                        selectedCount++;
                    } else {
                        $(this).prop('selected', false);
                    }
                });

                $('#selectedClientsCount').text(`S'han seleccionat ${selectedCount} client(s).`);
            };

            reader.readAsText(file);
        }

        // Binding file selector to logic
        $('#fileInput').on('change', function() {
            const file = this.files[0];
            if (file) {
                selectClientsFromFile(file);
            }
        });
    });
</script>

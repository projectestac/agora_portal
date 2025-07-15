<div class="form-group">
    <input type="file" id="fileInput" accept=".txt,.csv" style="display:none;"/>

    <button type="button" class="btn btn-primary" id="btnSelectFile">
        {{ __('batch.select_file_clients') }}
    </button>

    <button type="button" class="btn btn-secondary mt-2" id="btnDeselectAll">
        {{ __('batch.deselect_all') }}
    </button>

    <p class="italic margin-top-1" id="selectedClientsCount"></p>
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

<script>
    function selectClientsFromFile(file) {
        const reader = new FileReader();

        reader.onload = function (e) {
            const text = e.target.result;

            // Split the text into lines and remove empty lines
            const lines = text.split(/\r?\n/).filter(line => line.trim().length > 0);

            // Get the header and normalize it
            let headerLine = lines.shift().trim();

            // Remove optional quotes around header
            const header = headerLine.replace(/^"(.*)"$/, '$1').toLowerCase();

            // Validate the header
            if (!['db_id', 'code', 'dns'].includes(header)) {
                alert("{!! __('batch.file_header_check') !!}");
                return;
            }

            // Extract values from each line, removing optional quotes.
            const valuesFromFile = lines.map(line => {
                const trimmed = line.trim();
                // Remove quotes if present
                return trimmed.replace(/^"(.*)"$/, '$1');
            }).filter(val => val);

            let selectedCount = 0;

            // Iterate over each <option> in the select element
            $('#clientslist option').each(function () {
                const optionText = $(this).text().trim();

                // Split the option text like "1 - a0000001 - Client 1 - centre-1".
                const parts = optionText.split(' - ').map(p => p.trim());

                // Ensure the option has the expected format
                if (parts.length < 4) {
                    $(this).prop('selected', false);
                    return;
                }

                // Choose the correct part of the option based on the CSV header.
                let valueToCompare;
                switch (header) {
                    case 'db_id':
                        valueToCompare = parts[0];
                        break;
                    case 'code':
                        valueToCompare = parts[1];
                        break;
                    case 'dns':
                        valueToCompare = parts[3];
                        break;
                }

                // Select the option if its value is in the list from the file
                if (valuesFromFile.includes(valueToCompare)) {
                    $(this).prop('selected', true);
                    selectedCount++;
                } else {
                    $(this).prop('selected', false);
                }
            });

            // Update the UI with the number of selected clients.
            $('#selectedClientsCount').text(`{!! __('batch.selected') !!} ${selectedCount} {{ __('batch.clients') }}`);
        };

        reader.readAsText(file);
    }

    // Button to select file.
    $('#btnSelectFile').on('click', function () {
        $('#fileInput').click();
    });

    // Binding file selector to logic.
    $('#fileInput').on('change', function () {
        const file = this.files[0];
        if (file) {
            selectClientsFromFile(file);
        }

        // To be able to reselect the same file if needed.
        this.value = '';
    });

    // Button to deselect all.
    $('#btnDeselectAll').on('click', function () {
        $('#clientsSel option').prop('selected', false);
        $('#selectedClientsCount').text('');
    });
</script>

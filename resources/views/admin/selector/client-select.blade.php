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

<script>
    function selectClientsFromFile(file) {
        const reader = new FileReader();

        reader.onload = function(e) {
            const text = e.target.result;

            // Split into lines, remove empty lines
            const lines = text.split(/\r?\n/).filter(line => line.trim().length > 0);

            // Remove the header line
            lines.shift();

            // Extract the 'code' column (2nd column, index 1)

            const codesFromFile = lines.map(line => {
                // Regex to match CSV fields, including quoted fields
                const regex = /(?:\"([^\"]*)\")|([^,]+)/g;
                const values = [];
                let match;

                while ((match = regex.exec(line)) !== null) {
                    values.push(match[1] || match[2]);
                }

                return values[1]?.trim(); // 2nd column
            }).filter(code => code); // Removes invalid or incomplete lines

            let selectedCount = 0;

            // Select options in the #clientslist select whose value matches one of the codes from the file
            // Assuming each <option>'s value is the client code
            $('#clientslist option').each(function() {
                const optionText = $(this).text().trim();

                // Extract the code from text like "1 - a0000001 - Client 1 - centre-1"
                // We split by ' - ' and take the second part (index 1)
                const codeInText = optionText.split(' - ')[1];

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

    // Button to select file
    $('#btnSelectFile').on('click', function() {
        $('#fileInput').click();
    });

    // Binding file selector to logic
    $('#fileInput').on('change', function() {
        const file = this.files[0];
        if (file) {
            selectClientsFromFile(file);
        }

        // To be able to reselect the same file if needed
        this.value = '';
    });

    // Button to deselect all
    $('#btnDeselectAll').on('click', function() {
        $('#clientsSel option').prop('selected', false);
        $('#selectedClientsCount').text('');
    });
</script>

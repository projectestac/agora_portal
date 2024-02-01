
<div class="controls-container" style="margin: 10px 0;">
    <form id="stats-form" method="get" action="{{ route('stats.' . $service . '.' . $periodicity) }}" class="form-inline">
        @csrf

        @if($periodicity == 'monthly')

            <label for="month" class="visually-hidden">{{ __('common.month') }}:</label>
            <select name="month" class="form-control" id="month">
                @foreach (range(1, 12) as $month)
                    <option value="{{ $month }}" {{ $month == request('month') ? 'selected' : '' }}>{{ $month }}</option>
                @endforeach
            </select>

            &nbsp;

            <label for="year" class="visually-hidden">{{ __('common.year') }}:</label>
            <select name="year" class="form-control" id="year">
                @foreach (range(date('Y'), date('Y') - 10, -1) as $year)
                    <option value="{{ $year }}" {{ $year == request('year') ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>

        @else

            <label for="date" class="visually-hidden">{{ __('common.date') }}</label>
            <input name="date" type="date" class="form-control" value="{{ request('date') ? request('date') : '2024-01-01' }}">

        @endif

        &nbsp;

        <label>{{ __('stats.center_selector') }}</label>

        <input type="text" name="client_name" id="client_name" class="form-control" style="width:300px" placeholder="{{ __('stats.start_typing_a_center_name') }}" value="{{ request('client_name') }}" autocomplete="off">
        <button type="button" class="btn btn-danger" onclick="$('#client_name').val('') ; $('#stats-form').submit()">{{ __('stats.clear_filter') }}</button>

        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

        <script>
            $(function() {
                $("#client_name").autocomplete({
                    source: function(request, response) {
                        if (request.term.length >= 3) {
                            $.ajax({
                                url: '{{ route("clients.search") }}',
                                dataType: 'json',
                                data: {
                                    keyword: request.term
                                },
                                success: function(data) {
                                    response(data.map(function(client) {
                                                return {
                                                    label: client.name + ' - ' + client.code,
                                                    value: client.code
                                                }}));
                                }
                            });
                        } else {
                            response([]);
                        }
                    },
                    minLength: 3,
                    select: function(event, ui) {
                        $("#client_name").val(ui.item.label);
                        return false;
                    }
                });
            });
        </script>


        <button type="submit" class="btn btn-primary">{{ __('stats.show_stats') }}</button>
    </form>

    <br>

    <a href="{{ route('stats.exportTabStats', ['service' => $service, 'periodicity' => $periodicity]) }}" class="btn btn-success">{{ __('stats.export_csv_button') }}</a>
</div>

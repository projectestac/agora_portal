<div class="controls-container" style="margin: 10px 0;">
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form id="stats-form" method="get" action="{{ $request->getBaseUrl() . '/stats/' . $service . '/' . $periodicity }}" class="form-inline">
        @csrf

        @if ($periodicity === 'monthly')

            <label for="month" class="visually-hidden">{{ __('common.month') }}:</label>
            <select name="month" class="form-control" id="month">
                @foreach (range(1, 12) as $month)
                    <option value="{{ $month }}" {{ $month === request('month') ? 'selected' : '' }}>{{ $month }}</option>
                @endforeach
            </select>

            <label for="year" class="visually-hidden">{{ __('common.year') }}:</label>
            <select name="year" class="form-control" id="year">
                @foreach (range(date('Y'), date('Y') - 10, -1) as $year)
                    <option value="{{ $year }}" {{ $year === request('year') ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>

        @elseif ($periodicity === 'daily')

            <label for="start_date" class="visually-hidden">{{ __('common.start_date') }}</label>
            <input name="start_date" type="date" class="form-control" value="{{ request('start_date') ?: '2024-01-01' }}">

            <label for="end_date" class="visually-hidden">{{ __('common.end_date') }}</label>
            <input name="end_date" type="date" class="form-control" value="{{ request('end_date') ?: date('Y-m-d') }}">

        @else

            <label for="date" class="visually-hidden">{{ __('common.date') }}</label>
            <input name="date" type="date" class="form-control" value="{{ request('date') ?: '2024-01-01' }}">

        @endif

        &nbsp;

        <label>{{ __('stats.center_selector') }}</label>

        <input type="text" name="client_name" id="client_name" class="form-control" style="width:300px"
               placeholder="{{ __('stats.start_typing_a_center_name') }}" value="{{ request('client_name') }}" autocomplete="off">
        <button type="button" class="btn btn-danger"
                onclick="$('#client_name').val('') ; $('#stats-form').submit()">{{ __('stats.clear_filter') }}</button>

        <x-client-autocomplete></x-client-autocomplete>

        <button type="submit" class="btn btn-primary">{{ __('stats.show_stats') }}</button>
    </form>

    <br>

    <a href="{{ route('stats.exportTabStats', ['service' => $service, 'periodicity' => $periodicity, 'filter_export' => 0]) }}" class="btn btn-success">
        {{ __('stats.export_all_data') }}
    </a>

    <form method="get" action="{{ route('stats.exportTabStats', ['service' => $service, 'periodicity' => $periodicity]) }}" style="display: inline-block;">

        <input type="hidden" name="client_name" value="{{ request('client_name') }}">

        @if($periodicity === 'monthly')
            <input type="hidden" name="month" value="{{ request('month') }}">
            <input type="hidden" name="year" value="{{ request('year') }}">
        @elseif($periodicity === 'daily')
            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
        @else
            <input type="hidden" name="date" value="{{ request('date') }}">
        @endif

        <button type="submit" name="filter_export" value="1" class="btn btn-warning">
            {{ __('stats.export_filtered_data') }}
        </button>

    </form>


</div>

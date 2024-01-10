
<div class="controls-container">
    <form method="get" action="{{ route('stats.' . $service . '.' . $periodicity) }}" class="form-inline">
        @csrf

        @if($periodicity == 'monthly')

            <label for="month" class="visually-hidden">{{ __('common.month') }}</label>
            <select name="month" class="form-control" id="month">
                @foreach (range(1, 12) as $month)
                    <option value="{{ $month }}" {{ $month == request('month') ? 'selected' : '' }}>{{ $month }}</option>
                @endforeach
            </select>

            <label for="year" class="visually-hidden">{{ __('common.year') }}:</label>
            <select name="year" class="form-control" id="year">
                @foreach (range(date('Y'), date('Y') - 10, -1) as $year)
                    <option value="{{ $year }}" {{ $year == request('year') ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>

        @else

            <label for="date" class="visually-hidden">{{ __('common.date') }}</label>
            <input name="date" type="date" class="form-control" value="{{ request('date') }}">

        @endif

        <label>{{ __('stats.center_selector') }}</label>
        <select name="client_code" class="form-control">
            @foreach ($clients as $client)
                <option value="{{ $client->code }}">{{ $client->name }}</option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-primary">{{ __('stats.show_stats') }}</button>
    </form>

    <a href="{{ route('stats.exportTabStats', ['service' => $service, 'periodicity' => $periodicity]) }}" class="btn btn-success">{{ __('stats.export_csv_button') }}</a>
    <button disabled class="btn btn-info">{{ __('stats.show_graph_button') }}</button>
</div>

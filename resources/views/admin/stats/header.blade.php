<h3>{{ __('stats.stats') }}</h3>

<ul class="nav nav-tabs">
    @foreach([
        'stats' => 'stats.monthly_summaries',
        'stats/moodle/monthly' => 'stats.moodle_monthly_summary',
        'stats/moodle/daily' => 'stats.moodle_daily_summary',
        'stats/moodle/weekly' => 'stats.moodle_weekly_summary',
        'stats/nodes/monthly' => 'stats.nodes_monthly_summary',
        'stats/nodes/daily' => 'stats.nodes_daily_summary'
    ] as $path => $translationKey)
        <li class="{{ $path === $request->path() ? 'active' : '' }}">
            <a href="{{ $request->getBaseUrl() . '/' . $path }}">{{ __($translationKey) }}</a>
        </li>
    @endforeach
</ul>

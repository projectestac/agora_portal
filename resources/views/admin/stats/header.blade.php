<p class="h3">{{ __('stats.stats') }}</p>

<ul class="nav nav-tabs">
    @foreach([
        'stats.show' => 'stats.monthly_summaries',
        'stats.moodle.monthly' => 'stats.moodle_monthly_summary',
        'stats.moodle.daily' => 'stats.moodle_daily_summary',
        'stats.moodle.weekly' => 'stats.moodle_weekly_summary',
        'stats.nodes.monthly' => 'stats.nodes_monthly_summary',
        'stats.nodes.daily' => 'stats.nodes_daily_summary'
    ] as $route => $translationKey)
        <li class="{{  $route == $view ? 'active' : '' }}">
            <a href="{{ route($route) }}">{{ __($translationKey) }}</a>
        </li>
    @endforeach
</ul>

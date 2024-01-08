@extends('layout.default')

@section('content')
    <div class="admin-menu-container">
        @include('menu.adminmenu')
    </div>

    <div class="content stats">

        <p class="h3">{{ __('stats.stats') }}</p>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th colspan="2">{{ __('Nombre') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2">
                        <form method="get" action="{{ route('stats.show') }}" class="form-inline">
                            @csrf

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

                            <button type="submit" class="btn btn-primary">{{ __('stats.show_stats') }}</button>
                        </form>

                    </td>
                </tr>


                @if(isset($results))
                    <tr>
                        <td>{{ __('stats.centres_count') }}</td>
                        <td>{{ $results['centresCount'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('stats.active_users_sum') }}</td>
                        <td>{{ $results['activeUsersSum'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('stats.courses_sum') }}</td>
                        <td>{{ $results['coursesSum'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('stats.activities_sum') }}</td>
                        <td>{{ $results['activitiesSum'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('stats.total_access_sum') }}</td>
                        <td>{{ $results['totalAccessSum'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('stats.invalid_portals_active_users_sum') }}</td>
                        <td>{{ $results['invalidPortalsActiveUsersSum'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('stats.centres_nodes_count') }}</td>
                        <td>{{ $results['centresNodesCount'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('stats.posts_sum') }}</td>
                        <td>{{ $results['postsSum'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('stats.access_nodes_sum') }}</td>
                        <td>{{ $results['accessNodesSum'] }}</td>
                    </tr>

                @else
                    <tr>
                        <td colspan="2">{{ __('common.please_select_a_date') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

    </div>
@endsection

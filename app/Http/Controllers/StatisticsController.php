<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Client;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Pagination\LengthAwarePaginator;

class StatisticsController extends Controller {
    protected $clients;

    public function __construct() {
        $this->middleware('auth');
        $this->clients = Client::all();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View {
        return view('admin.stats.index');
    }

    public function getTable(string $service, string $periodicity) {
        return 'agoraportal_' . ($service == 'moodle' ? 'moodle2' : 'nodes') . '_stats_' . ($periodicity == 'daily' ? 'day' : str_replace('ly', '', $periodicity));
    }

    public function showStats(Request $request) {
        $month = $request->input('month');
        $year = $request->input('year');
        $yearMonth = $year . str_pad($month, 2, '0', STR_PAD_LEFT);

        // Noodle
        $moodleStatsQuery = DB::table('agoraportal_moodle2_stats_month')->where('yearmonth', $yearMonth);

        $centresCount = $moodleStatsQuery->count();
        $activeUsersSum = $moodleStatsQuery->sum('usersactive');
        $coursesSum = $moodleStatsQuery->sum('courses');
        $activitiesSum = $moodleStatsQuery->sum('activities');
        $totalAccessSum = $moodleStatsQuery->sum('total_access');

        $invalidPortalsActiveUsersSum = $moodleStatsQuery
            ->whereIn('clientDNS', ['analytics1', 'analytics2', 'analytics3', 'analytics4', 'analytics5', 'analytics6', 'analytics7', 'analytics8', 'analytics9', 'analytics10', 'analytics11', 'analytics12', 'prova2', 'suport', 'esc-vegeta', 'esc-gregal', 'esc-llevant', 'culturadigital', 'demoescola', 'demoinstitut', 'demo', 'monitor', 'eixapps', 'demo-moodle'])
            ->sum('usersactive');

        // Nodes
        $nodesStatsQuery = DB::table('agoraportal_nodes_stats_month')->where('yearmonth', $yearMonth);

        $centresNodesCount = $nodesStatsQuery->count();
        $postsSum = $nodesStatsQuery->sum('posts');
        $accessNodesSum = $nodesStatsQuery->sum('total');

        // Pass the results to the view
        return view('admin.stats.index', [
            'results' => [
                'centresCount' => $centresCount,
                'activeUsersSum' => $activeUsersSum,
                'coursesSum' => $coursesSum,
                'activitiesSum' => $activitiesSum,
                'totalAccessSum' => $totalAccessSum,
                'invalidPortalsActiveUsersSum' => $invalidPortalsActiveUsersSum,
                'centresNodesCount' => $centresNodesCount,
                'postsSum' => $postsSum,
                'accessNodesSum' => $accessNodesSum],
            'view' => 'stats.show'
        ]);
    }

    // Example: showTabStats('daily', 'moodle')
    public function showTabStats(Request $request, string $service, string $periodicity) {
        $daily_stats = null;
        $table = $this->getTable($service, $periodicity);

        $client_name = $request->input('client_name');
        $client_code = NULL;

        $column_names =
            $service == 'moodle' ? ['total', 'usersactive']
                                 : ['total', 'posts', 'userstotal'];

        if($client_name != '')
        {
            $client_code = explode(' - ', $client_name)[1];
        }

        if($periodicity == 'monthly')
        {
            $month = $request->input('month');
            $year = $request->input('year');
            $yearMonth = $year . str_pad($month, 2, '0', STR_PAD_LEFT);

            $columns = '';

            for($i = 0; $i < count($column_names); $i++)
            {
                $columns .= 'SUM(' . $column_names[$i] . ') AS ' . $column_names[$i] . ($i < count($column_names) - 1 ? ', ' : '');
            }

            // getting results
            $daily_stats = DB::select("SELECT SUBSTRING(date, 1, 8) AS day," . $columns . "
                                              FROM agoraportal_" . ($service == 'moodle' ? 'moodle2' : 'nodes') . "_stats_day
                                              WHERE SUBSTRING(date, 1, 6) = '" . $yearMonth . "' " . ($client_code != NULL ? " AND clientcode LIKE '%$client_code%'" : "") . "
                                              GROUP BY SUBSTRING(date, 1, 8)");
        }

        $view = 'stats.' . $service . '.' . $periodicity;

        if($service == 'moodle') $column_names[0] = 'total_access';


        // passing results to matching tab view
        return view('admin.stats.results', ['column_names' => $column_names, 'daily_stats' => $daily_stats, 'view' => $view, 'service' => $service, 'periodicity' => $periodicity, 'clients' => $this->clients]);

    }

    public function getTabStats(Request $request, string $service, string $periodicity) {
        $perPage = $request->input('length', 25);
        $page = $request->input('page', 1);

        $table = $this->getTable($service, $periodicity);

        $client_name = $request->input('client_name');
        $client_code = NULL;

        if($client_name != '')
        {
            $client_code = explode(' - ', $client_name)[1];
        }

        if($periodicity == 'monthly')
        {
            $month = $request->input('month');
            $year = $request->input('year');
            $yearMonth = $year . str_pad($month, 2, '0', STR_PAD_LEFT);

            $columns = $service == 'moodle'
                       ? 'SUM(total_access) AS total_access, SUM(usersactive) AS usersactive, SUM(courses) AS courses, SUM(activites) AS activites'
                       : 'SUM(total) AS total, SUM(posts) AS posts';

            // getting results
            $results = DB::table($table)->where('yearmonth', $yearMonth);
        }

        else
        {
            $date = str_replace('-', '', $request->input('date'));

            // getting results
            $results = DB::table($table)->where('date', $date);
        }

        if($client_code != NULL)
        {
            $results = $results->where('clientcode', $client_code);
        }

        return Datatables::make($results)->make(true);

    }

    public function exportTabStats(Request $request, string $service, string $periodicity) {
        $table = $this->getTable($service, $periodicity);

        $data = DB::table($table)->get();

        $formattedDateTime = strftime('%d%m%Y_%H%M%S');

        $csvFileName = 'stats_' . $service. '_' . $periodicity . '_' . $formattedDateTime . '.csv';

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        );

        foreach ($headers as $header => $value) {
            header("$header: $value");
        }

        $handle = fopen('php://output', 'w');

        // column names
        $columnNames = array_keys((array) $data->first());

        $translatedColumnNames = array_map(function ($columnName) {
            return __('database-table.' . $columnName);
        }, $columnNames);

        fputcsv($handle, $translatedColumnNames);

        // full table data
        foreach ($data as $row) {
            fputcsv($handle, get_object_vars($row));
        }

        fclose($handle);
    }
}

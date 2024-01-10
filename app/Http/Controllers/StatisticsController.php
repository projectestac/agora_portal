<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Client;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

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
        $table = $this->getTable($service, $periodicity);

        $client_code = $request->input('client_code');

        if($periodicity == 'monthly')
        {
            $month = $request->input('month');
            $year = $request->input('year');
            $yearMonth = $year . str_pad($month, 2, '0', STR_PAD_LEFT);

            // getting results
            $results = DB::table($table)->where('yearmonth', $yearMonth);
        }

        else
        {
            $date = str_replace('-', '', $request->input('date'));

            // getting results
            $results = DB::table($table)->where('date', $date);
        }

        $results = $results->where('clientcode', $client_code)->get();

        $view = 'stats.' . $service . '.' . $periodicity;

        // passing results to matching tab view
        return view('admin.stats.results', ['results' => $results, 'view' => $view, 'service' => $service, 'periodicity' => $periodicity, 'clients' => $this->clients]);

    }

    public function exportTabStats(Request $request, string $service, string $periodicity) {
        $table = $this->getTable($service, $periodicity);

        $data = DB::table($table)->get();

        $csvFileName = 'statistics.csv';
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        );

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

        return response()->make(rtrim(ob_get_clean()), 200, $headers);
    }
}

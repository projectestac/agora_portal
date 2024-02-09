<?php

namespace App\Http\Controllers;

use App\Models\Client;
use DateTime;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller {

    protected Collection $clients;
    protected const IGNORE_SITES = [
        'analytics1', 'analytics2', 'analytics3', 'analytics4', 'analytics5', 'analytics6', 'analytics7',
        'analytics8', 'analytics9', 'analytics10', 'analytics11', 'analytics12', 'prova2', 'suport',
        'esc-vegeta', 'esc-gregal', 'esc-llevant', 'culturadigital', 'demoescola', 'demoinstitut', 'demo',
        'monitor', 'eixapps', 'demo-moodle',
    ];

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

    public function getTable(string $service, string $periodicity): string {
        return 'agoraportal_' . ($service === 'moodle' ? 'moodle2' : 'nodes') . '_stats_' . ($periodicity === 'daily' ? 'day' : str_replace('ly', '', $periodicity));
    }

    public function showStats(Request $request): View {

        $month = $request->input('month');
        $year = $request->input('year');
        $yearMonth = $year . str_pad($month, 2, '0', STR_PAD_LEFT);

        // Moodle.
        $moodleStatsQuery = DB::table('agoraportal_moodle2_stats_month')->where('yearmonth', $yearMonth);

        $centresCount = $moodleStatsQuery->count();
        $activeUsersSum = $moodleStatsQuery->sum('usersactive');
        $coursesSum = $moodleStatsQuery->sum('courses');
        $activitiesSum = $moodleStatsQuery->sum('activities');
        $totalAccessSum = $moodleStatsQuery->sum('total_access');

        $invalidPortalsActiveUsersSum = $moodleStatsQuery
            ->whereIn('clientDNS', self::IGNORE_SITES)
            ->sum('usersactive');

        // Nodes.
        $nodesStatsQuery = DB::table('agoraportal_nodes_stats_month')->where('yearmonth', $yearMonth);

        $centresNodesCount = $nodesStatsQuery->count();
        $postsSum = $nodesStatsQuery->sum('posts');
        $accessNodesSum = $nodesStatsQuery->sum('total');

        // Pass the results to the view.
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
            'view' => 'stats.show',
            'request' => $request,
        ]);
    }

    // Example: showTabStats('daily', 'moodle')
    public function showTabStats(Request $request, string $service, string $periodicity): View {

        $daily_stats = null;
        $table = $this->getTable($service, $periodicity);

        $client_name = $request->input('client_name');
        $client_code = (!empty($client_name)) ? explode(' - ', $client_name)[1] : null;

        if ($periodicity === 'monthly') {
            $month = $request->input('month');
            $year = $request->input('year');
            $yearMonth = $year . str_pad($month, 2, '0', STR_PAD_LEFT);

            $columns = $service === 'moodle'
                ? 'SUM(total) AS total, SUM(userstotal) AS userstotal, SUM(usersactive) AS usersactive'
                : 'SUM(total) AS total, SUM(posts) AS posts';

            // Get the results.
            $results = DB::table($table)->where('yearmonth', $yearMonth);
            $daily_stats = DB::select(
                "SELECT SUBSTRING(date, 1, 8) AS day," . $columns . "
                FROM agoraportal_" . ($service === 'moodle' ? 'moodle2' : 'nodes') . "_stats_day
                WHERE SUBSTRING(date, 1, 6) = '" . $yearMonth . "' " . ($client_code !== NULL ? " AND clientcode LIKE '%$client_code%'" : '') . "
                GROUP BY SUBSTRING(date, 1, 8)"
            );
        } else {
            $date = str_replace('-', '', $request->input('date'));
            // Get the results.
            $results = DB::table($table)->where('date', $date);
        }

        if (!empty($client_code)) {
            $results = $results->where('clientcode', $client_code);
        }

        $results = $results->get();
        $view = 'stats.' . $service . '.' . $periodicity;

        // passing results to matching tab view
        return view('admin.stats.results', [
            'results' => $results,
            'daily_stats' => $daily_stats,
            'view' => $view,
            'service' => $service,
            'periodicity' => $periodicity,
            'clients' => $this->clients,
            'request' => $request,
        ]);

    }

    public function exportTabStats(Request $request, string $service, string $periodicity): void {
        $table = $this->getTable($service, $periodicity);

        $data = DB::table($table)->get();

        $dateTime = new DateTime();
        $formattedDateTime = $dateTime->format('dmY_His');

        $csvFileName = 'stats_' . $service . '_' . $periodicity . '_' . $formattedDateTime . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        ];

        foreach ($headers as $header => $value) {
            header("$header: $value");
        }

        $handle = fopen('php://output', 'wb');

        // Column names.
        $columnNames = array_keys((array)$data->first());

        $translatedColumnNames = array_map(static function ($columnName) {
            return __('database-table.' . $columnName);
        }, $columnNames);

        // Send column names.
        fputcsv($handle, $translatedColumnNames);

        // Send full table data.
        foreach ($data as $row) {
            fputcsv($handle, get_object_vars($row));
        }

        fclose($handle);

    }

}

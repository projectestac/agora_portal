<?php

namespace App\Http\Controllers;

use App\Models\Client;
use DateTime;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    protected Collection $clients;
    protected const IGNORE_SITES = [
        'analytics1', 'analytics2', 'analytics3', 'analytics4', 'analytics5', 'analytics6', 'analytics7',
        'analytics8', 'analytics9', 'analytics10', 'analytics11', 'analytics12', 'prova2', 'suport',
        'esc-vegeta', 'esc-gregal', 'esc-llevant', 'culturadigital', 'demoescola', 'demoinstitut', 'demo',
        'monitor', 'eixapps', 'demo-moodle',
    ];

    // Maximum interval in days for daily stats.
    public const MAX_DAYS_FOR_DAILY_STATS = 5000;

    public function __construct()
    {
        $this->middleware('auth');
        $this->clients = Client::all();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.stats.index');
    }

    public function getTable(string $service, string $periodicity): string
    {
        return 'agoraportal_' . ($service === 'moodle' ? 'moodle2' : 'nodes') . '_stats_' . ($periodicity === 'daily' ? 'day' : str_replace('ly', '', $periodicity));
    }

    public function showStats(Request $request): View
    {
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
    public function showTabStats(Request $request, string $service, string $periodicity): View|RedirectResponse
    {
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

            // For the graphs, we need daily stats.
            $daily_stats = DB::select(
                "SELECT date AS day, " . $columns . "
                    FROM agoraportal_" . ($service === 'moodle' ? 'moodle2' : 'nodes') . "_stats_day
                    WHERE date >= '" . $yearMonth . "01'
                    AND date <= '" . $yearMonth . "31' " .
                    ($client_code !== NULL ? " AND clientcode LIKE '%$client_code%'" : '') . "
                    GROUP BY day"
            );
        } else if ($periodicity === 'daily') {

            if (!$request->input('start_date') || !$request->input('end_date')) {
                $endDate = now()->format('Y-m-d');
                $startDate = now()->subDays(4)->format('Y-m-d'); // Default to 5 days interval.

                $request->merge([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);
            }

            $startDate = str_replace('-', '', $request->input('start_date'));
            $endDate = str_replace('-', '', $request->input('end_date'));

            // 5 days maximum interval check.
            $start = \DateTime::createFromFormat('Ymd', $startDate);
            $end = \DateTime::createFromFormat('Ymd', $endDate);
            $interval = $start->diff($end)->days;

            if ($interval > $this::MAX_DAYS_FOR_DAILY_STATS) {
                return redirect()->back()->with('error', __('common.max_interval_error', [
                    'days' => $this::MAX_DAYS_FOR_DAILY_STATS,
                ]));
            }

            $results = DB::table($table)
                ->whereBetween('date', [$startDate, $endDate]);

            if (!empty($client_code)) {
                $results = $results->where('clientcode', $client_code);
            }
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

    public function exportTabStats(Request $request, string $service, string $periodicity)
    {
        $table = $this->getTable($service, $periodicity);

        $client_name = $request->input('client_name');
        $client_code = (!empty($client_name)) ? explode(' - ', $client_name)[1] : null;

        $filter_export = $request->input('filter_export');

        // Build the query with the same filters as in showTabStats
        $query = DB::table($table);

        if ($filter_export === '1') { // If exporting filtered data
            if ($periodicity === 'monthly') {
                $month = $request->input('month');
                $year = $request->input('year');
                $yearMonth = $year . str_pad($month, 2, '0', STR_PAD_LEFT);

                // Apply the same condition on yearmonth
                $query->where('yearmonth', $yearMonth);

                if ($client_code !== null) {
                    $query->where('clientcode', 'like', "%$client_code%");
                }
            } elseif ($periodicity === 'daily') {
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                if ($startDate && $endDate) {
                    $startDate = str_replace('-', '', $startDate);
                    $endDate = str_replace('-', '', $endDate);
                    $query->whereBetween('date', [$startDate, $endDate]);
                }

                if ($client_code !== null) {
                    $query->where('clientcode', $client_code);
                }
            } else {
                $date = $request->input('date');
                if ($date) {
                    $date = str_replace('-', '', $date);
                    $query->where('date', $date);
                }

                if ($client_code !== null) {
                    $query->where('clientcode', $client_code);
                }
            }

            // Export filtered data directly to output (no file)
            $data = $query->get();

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

            // Send headers to force download
            foreach ($headers as $header => $value) {
                header("$header: $value");
            }

            $handle = fopen('php://output', 'wb');

            if ($data->isEmpty()) {
                // If no data, send an empty CSV with only the column headers (empty here)
                $columnNames = [];
            } else {
                // Get column names from first data row
                $columnNames = array_keys((array)$data->first());
            }

            // Translate column names for CSV header
            $translatedColumnNames = array_map(static function ($columnName) {
                return __('database-table.' . $columnName);
            }, $columnNames);

            // Write the CSV header row
            fputcsv($handle, $translatedColumnNames);

            // Write the data rows
            foreach ($data as $row) {
                fputcsv($handle, get_object_vars($row));
            }

            fclose($handle);
            exit; // Stop further script execution after download
        }

        // Otherwise, export full data without filters

        // Create filename and path
        $dateTime = new DateTime();
        $formattedDateTime = $dateTime->format('dmY_His');

        $filename = 'stats_' . $service . '_' . $periodicity . '_' . $formattedDateTime . '.csv';
        $filepath = public_path('exports/' . $filename);

        if (!file_exists(public_path('exports')) &&
            !mkdir($concurrentDirectory = public_path('exports'), 0755, true) &&
            !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        $handle = fopen($filepath, 'w');

        // Use cursor to avoid memory exhaustion
        $columnNames = \Schema::getColumnListing($table);

        // Then translate columns
        $translatedColumnNames = array_map(static function ($columnName) {
            return __('database-table.' . $columnName);
        }, $columnNames);

        fputcsv($handle, $translatedColumnNames);

        // Then stream all data
        foreach ($query->cursor() as $row) {
            fputcsv($handle, get_object_vars($row));
        }

        fclose($handle);

        // Generate public URL
        $downloadUrl = asset('exports/' . $filename);

        // Return back with link
        return redirect()->back()->with('export_file_url', $downloadUrl);
    }

}

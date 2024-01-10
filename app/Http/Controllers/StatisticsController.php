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

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role) {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role) {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role) {
    }

    public function showStats(Request $request) {
        $month = $request->input('month');
        $year = $request->input('year');
        $yearMonth = $year . str_pad($month, 2, '0', STR_PAD_LEFT);

        // MOODLE QUERIES
        $centresCount = DB::table('agoraportal_moodle2_stats_month')
            ->where('yearmonth', $yearMonth)
            ->count();

        $activeUsersSum = DB::table('agoraportal_moodle2_stats_month')
            ->where('yearmonth', $yearMonth)
            ->sum('usersactive');

        $coursesSum = DB::table('agoraportal_moodle2_stats_month')
            ->where('yearmonth', $yearMonth)
            ->sum('courses');

        $activitiesSum = DB::table('agoraportal_moodle2_stats_month')
            ->where('yearmonth', $yearMonth)
            ->sum('activities');

        $totalAccessSum = DB::table('agoraportal_moodle2_stats_month')
            ->where('yearmonth', $yearMonth)
            ->sum('total_access');

        $invalidPortalsActiveUsersSum = DB::table('agoraportal_moodle2_stats_month')
            ->where('yearmonth', $yearMonth)
            ->whereIn('clientDNS', ['analytics1', 'analytics2', 'analytics3', 'analytics4', 'analytics5', 'analytics6', 'analytics7', 'analytics8', 'analytics9', 'analytics10', 'analytics11', 'analytics12', 'prova2', 'suport', 'esc-vegeta', 'esc-gregal', 'esc-llevant', 'culturadigital', 'demoescola', 'demoinstitut', 'demo', 'monitor', 'eixapps', 'demo-moodle'])
            ->sum('usersactive');

        // NODES QUERIES
        $centresNodesCount = DB::table('agoraportal_nodes_stats_month')
            ->where('yearmonth', $yearMonth)
            ->count();

        $postsSum = DB::table('agoraportal_nodes_stats_month')
            ->where('yearmonth', $yearMonth)
            ->sum('posts');

        $accessNodesSum = DB::table('agoraportal_nodes_stats_month')
            ->where('yearmonth', $yearMonth)
            ->sum('total');

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

        // getting matching stats table
        $table = 'agoraportal_' . ($service == 'moodle' ? 'moodle2' : 'nodes') . '_stats_' . ($periodicity == 'daily' ? 'day' : str_replace('ly', '', $periodicity));

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

    public function exportTabStats(Request $request, string $service, string $periodicity)
    {
        $table = 'agoraportal_' . ($service == 'moodle' ? 'moodle2' : 'nodes') . '_stats_' . ($periodicity == 'daily' ? 'day' : str_replace('ly', '', $periodicity));

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
        fputcsv($handle, array_keys((array) $data->first()));

        // full table data
        foreach ($data as $row) {
            fputcsv($handle, get_object_vars($row));
        }

        fclose($handle);

        return response()->make(rtrim(ob_get_clean()), 200, $headers);
    }

    public function getMoodleMonthly()
    {
        return view('admin.stats.moodle.monthly', ['clients' => $this->clients]);
    }

    public function getMoodleDaily()
    {
        return view('admin.stats.moodle.daily', ['clients' => $this->clients]);
    }

    public function getMoodleWeekly()
    {
        return view('admin.stats.moodle.weekly', ['clients' => $this->clients]);
    }

    public function getNodesMonthly()
    {
        return view('admin.stats.nodes.monthly', ['clients' => $this->clients]);
    }

    public function getNodesDaily()
    {
        return view('admin.stats.nodes.daily', ['clients' => $this->clients]);
    }

    public function getNodesWeekly()
    {
        return view('admin.stats.nodes.weekly', ['clients' => $this->clients]);
    }
}

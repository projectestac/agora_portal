<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller {
    public function __construct() {
        $this->middleware('auth');
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
                'accessNodesSum' => $accessNodesSum]
        ]);
    }
}

<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'id' => 4,
                'name' => 'Moodle',
                'description' => 'Entorn virtual d\'aprenentatge fet amb Moodle',
                'slug' => 'moodle',
            ],
            [
                'id' => 5,
                'name' => 'Nodes',
                'description' => 'Web de centre fet amb WordPress',
                'slug' => '',
            ],
        ];

        foreach ($services as $service) {
            DB::table('services')->insert([
                'id' => $service['id'],
                'name' => $service['name'],
                'status' => 'active',
                'description' => $service['description'],
                'slug' => $service['slug'],
                'quota' => 5 * 1024 * 1024 * 1024,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

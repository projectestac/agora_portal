<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            1 => 'Baix Llobregat',
            2 => 'Barcelonès',
            3 => 'Catalunya Central',
            4 => 'Consorci d\'Educació de Barcelona',
            5 => 'Girona',
            6 => 'Lleida',
            7 => 'Maresme-Vallès Oriental',
            8 => 'Tarragona',
            9 => 'Terres de l\'Ebre',
            10 => 'Vallès Occidental',
            11 => 'Penedès',
            12 => 'Alt Pirineu i Aran',
            13 => 'No definit',
        ];

        foreach ($locations as $id => $name) {
            DB::table('locations')->insert([
                'id' => $id,
                'name' => $name,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ClientTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $client_types = [
            1 => 'Escola',
            2 => 'Institut',
            3 => 'Institut-Escola',
            4 => 'Adults',
            5 => 'Servei educatiu',
            6 => 'Escola Oficial d\'Idiomes',
            7 => 'Altres',
            8 => 'CEE',
            9 => 'Centre concertat',
            10 => 'ECA',
            11 => 'ZER',
            12 => 'Projecte',
            13 => 'Formació',
            14 => 'Llar d\infants',
            15 => 'No definit',
        ];

        foreach ($client_types as $id => $name) {
            DB::table('client_types')->insert([
                'id' => $id,
                'name' => $name,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

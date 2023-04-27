<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $managers = [
            1 => 12,
            2 => 13,
            3 => 14,
            4 => 15,
            5 => 16,
            6 => 17,
            7 => 18,
            8 => 19,
            9 => 20,
            10 => 21,
        ];

        foreach ($managers as $client_id => $user_id) {
            DB::table('managers')->insert([
                'client_id' => $client_id,
                'user_id' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

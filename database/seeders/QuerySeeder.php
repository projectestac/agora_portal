<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class QuerySeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $queries = [
            [
                'service_id' => 5,
                'query' => 'UPDATE wp_users SET user_pass = MD5(\'agora\') WHERE user_login = \'admin\';',
                'description' => 'Canvia la contrasenya a l\'usuari admin',
                'type' => 'update',
            ],
            [
                'service_id' => 4,
                'query' => 'UPDATE m2user SET password = MD5(\'agora\') WHERE username = \'admin\';',
                'description' => 'Canvia la contrasenya a l\'usuari admin',
                'type' => 'update',
            ],

        ];

        foreach ($queries as $query) {
            DB::table('queries')->insert([
                'service_id' => $query['service_id'],
                'query' => $query['query'],
                'description' => $query['description'],
                'type' => $query['type'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

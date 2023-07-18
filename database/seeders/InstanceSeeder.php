<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class InstanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $client_services = [
            [
                'client_id' => 1,
                'service_id' => 4,
                'db_id' => 1,
                'model_type_id' => 1,
                'contact_name' => 'Manager 1',
            ],
            [
                'client_id' => 2,
                'service_id' => 4,
                'db_id' => 2,
                'model_type_id' => 1,
                'contact_name' => 'Manager 2',
            ],
            [
                'client_id' => 3,
                'service_id' => 4,
                'db_id' => 3,
                'model_type_id' => 2,
                'contact_name' => 'Manager 3',
            ],
            [
                'client_id' => 4,
                'service_id' => 4,
                'db_id' => 4,
                'model_type_id' => 2,
                'contact_name' => 'Manager 4',
            ],
            [
                'client_id' => 1,
                'service_id' => 5,
                'db_id' => 1,
                'model_type_id' => 3,
                'contact_name' => 'Manager 1',
            ],
            [
                'client_id' => 2,
                'service_id' => 5,
                'db_id' => 2,
                'model_type_id' => 4,
                'contact_name' => 'Manager 2',
            ],
            [
                'client_id' => 3,
                'service_id' => 5,
                'db_id' => 3,
                'model_type_id' => 5,
                'contact_name' => 'Manager 3',
            ],
            [
                'client_id' => 4,
                'service_id' => 5,
                'db_id' => 4,
                'model_type_id' => 6,
                'contact_name' => 'Manager 4',
            ],
            [
                'client_id' => 5,
                'service_id' => 5,
                'db_id' => 5,
                'model_type_id' => 7,
                'contact_name' => 'Manager 5',
            ],
            [
                'client_id' => 6,
                'service_id' => 5,
                'db_id' => 6,
                'model_type_id' => 8,
                'contact_name' => 'Manager 6',
            ],
            [
                'client_id' => 7,
                'service_id' => 5,
                'db_id' => 7,
                'model_type_id' => 9,
                'contact_name' => 'Manager 7',
            ],
            [
                'client_id' => 8,
                'service_id' => 5,
                'db_id' => 8,
                'model_type_id' => 10,
                'contact_name' => 'Manager 8',
            ],
            [
                'client_id' => 9,
                'service_id' => 5,
                'db_id' => 9,
                'model_type_id' => 11,
                'contact_name' => 'Manager 9',
            ],
        ];

        foreach ($client_services as $client_service) {
            DB::table('instances')->insert([
                'client_id' => $client_service['client_id'],
                'service_id' => $client_service['service_id'],
                'status' => 'active',
                'db_id' => $client_service['db_id'],
                'db_host' => 'localhost',
                'quota' => 5 * 1024 * 1024 * 1024,
                'used_quota' => 0,
                'model_type_id' => $client_service['model_type_id'],
                'contact_name' => $client_service['contact_name'],
                'contact_profile' => 'Coordinador TAC',
                'observations' => '',
                'annotations' => '',
                'requested_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}

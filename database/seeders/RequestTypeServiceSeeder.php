<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class RequestTypeServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $associations = [
            [
                'request_type_id' => 1,
                'service_id' => 5,
            ],
            [
                'request_type_id' => 2,
                'service_id' => 5,
            ],
            [
                'request_type_id' => 4,
                'service_id' => 5,
            ],
            [
                'request_type_id' => 3,
                'service_id' => 5,
            ],
            [
                'request_type_id' => 3,
                'service_id' => 4,
            ],
            [
                'request_type_id' => 1,
                'service_id' => 4,
            ],
            [
                'request_type_id' => 2,
                'service_id' => 4,
            ],
        ];

        foreach ($associations as $association) {
            DB::table('request_type_service')->insert([
                'request_type_id' => $association['request_type_id'],
                'service_id' => $association['service_id'],
            ]);
        }
    }
}

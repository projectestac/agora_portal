<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'code' => 'a0000001',
                'name' => 'Client 1',
                'dns' => 'centre-1',
                'old_dns' => 'usu1',
                'type_id' => 1,
            ],
            [
                'code' => 'a0000002',
                'name' => 'Client 2',
                'dns' => 'centre-2',
                'old_dns' => 'usu2',
                'type_id' => 2,
            ],
            [
                'code' => 'a0000003',
                'name' => 'Client 3',
                'dns' => 'centre-3',
                'old_dns' => 'usu3',
                'type_id' => 4,
            ],
            [
                'code' => 'a0000004',
                'name' => 'Client 4',
                'dns' => 'centre-4',
                'old_dns' => 'usu4',
                'type_id' => 6,
            ],
            [
                'code' => 'a0000005',
                'name' => 'Client 5',
                'dns' => 'centre-5',
                'old_dns' => 'usu5',
                'type_id' => 11,
            ],
            [
                'code' => 'a0000006',
                'name' => 'Client 6',
                'dns' => 'centre-6',
                'old_dns' => 'usu6',
                'type_id' => 8,
            ],
            [
                'code' => 'a0000007',
                'name' => 'Client 7',
                'dns' => 'centre-7',
                'old_dns' => 'usu7',
                'type_id' => 7,
            ],
            [
                'code' => 'a0000008',
                'name' => 'Client 8',
                'dns' => 'centre-8',
                'old_dns' => 'usu8',
                'type_id' => 5,
            ],
            [
                'code' => 'a0000009',
                'name' => 'Client 9',
                'dns' => 'centre-9',
                'old_dns' => 'usu9',
                'type_id' => 12,
            ],
            [
                'code' => 'a0000010',
                'name' => 'Client 10',
                'dns' => 'centre-10',
                'old_dns' => 'usu10',
                'type_id' => 1,
            ],
        ];

        foreach ($clients as $client) {
            DB::table('clients')->insert([
                'code' => $client['code'],
                'name' => $client['name'],
                'dns' => $client['dns'],
                'old_dns' => $client['old_dns'],
                'url_type' => 'standard',
                'host' => null,
                'old_host' => null,
                'address' => 'C/ Via Làctia, 584',
                'city' => 'Barcelona',
                'postal_code' => '08012',
                'description' => '',
                'status' => 'active',
                'location_id' => 1,
                'type_id' => $client['type_id'],
                'visible' => 'yes',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

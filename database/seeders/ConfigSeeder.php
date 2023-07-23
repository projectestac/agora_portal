<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {

        $params = [
            'notify_address_quota' => 'admin@xtec.invalid',
            'notify_address_request' => 'admin@xtec.invalid',
            'notify_address_user_cco' => 'admin@xtec.invalid',
            'quota_usage_to_request' => 0.75,
            'quota_free_to_request' => 3,
            'quota_usage_to_notify' => 0.85,
            'quota_free_to_notify' => 3,
            'xtecadmin_hash' => '',
            'max_file_size_for_large_upload' => 800,
            'nodes_create_db' => 1,
            'min_db_id' => 1,
            'google_client_id' => '',
            'google_client_secret' => '',
            'google_redirect_uri' => '',
        ];

        foreach ($params as $name => $value) {
            DB::table('configs')->insert([
                'name' => $name,
                'value' => $value,
            ]);
        }

    }
}

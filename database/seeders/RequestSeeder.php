<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequestSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $requests = [
            [
                'request_type_id' => 1,
                'service_id' => 4,
                'client_id' => 1,
                'user_id' => 1,
                'status' => 'pending',
                'user_comment' => 'This is a user comment',
                'admin_comment' => 'This is an admin comment',
                'private_note' => 'This is a private note',
            ],
            [
                'request_type_id' => 1,
                'service_id' => 5,
                'client_id' => 2,
                'user_id' => 2,
                'status' => 'pending',
                'user_comment' => 'This is a user comment',
                'admin_comment' => 'This is an admin comment',
                'private_note' => 'This is a private note',
            ],
        ];

        foreach ($requests as $request) {
            DB::table('requests')->insert([
                'request_type_id' => $request['request_type_id'],
                'service_id' => $request['service_id'],
                'client_id' => $request['client_id'],
                'user_id' => $request['user_id'],
                'status' => $request['status'],
                'user_comment' => $request['user_comment'],
                'admin_comment' => $request['admin_comment'],
                'private_note' => $request['private_note'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

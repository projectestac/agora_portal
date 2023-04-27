<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            'admin' => 'admin@xtec.invalid',
            'a0000001' => 'a0000001@xtec.invalid',
            'a0000002' => 'a0000002@xtec.invalid',
            'a0000003' => 'a0000003@xtec.invalid',
            'a0000004' => 'a0000004@xtec.invalid',
            'a0000005' => 'a0000005@xtec.invalid',
            'a0000006' => 'a0000006@xtec.invalid',
            'a0000007' => 'a0000007@xtec.invalid',
            'a0000008' => 'a0000008@xtec.invalid',
            'a0000009' => 'a0000009@xtec.invalid',
            'a0000010' => 'a0000010@xtec.invalid',
            'manager1' => 'manager1@xtec.invalid',
            'manager2' => 'manager2@xtec.invalid',
            'manager3' => 'manager3@xtec.invalid',
            'manager4' => 'manager4@xtec.invalid',
            'manager5' => 'manager5@xtec.invalid',
            'manager6' => 'manager6@xtec.invalid',
            'manager7' => 'manager7@xtec.invalid',
            'manager8' => 'manager8@xtec.invalid',
            'manager9' => 'manager9@xtec.invalid',
            'manager10' => 'manager10@xtec.invalid',
        ];

        foreach ($users as $name => $email) {
            DB::table('users')->insert([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt('agora'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

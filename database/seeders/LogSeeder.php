<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $records = [
            [
                'client_id' => 1,
                'user_id' => 12,
                'action_type' => 1,
                'action_description' => 'S\'ha fet la sol·licitud de "Ampliació de quota" del servei moodle2',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'client_id' => 1,
                'user_id' => 1,
                'action_type' => 2,
                'action_description' => 'S\'ha atès la vostra sol·licitud i ha quedat com a <strong>Solucionada</strong>. Podeu trobar més informació <a href=\"index.php?module=Agoraportal&type=user&func=requests\">aquí</a>',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($records as $record) {
            DB::table('standard_logs')->insert($record);
        }
    }
}

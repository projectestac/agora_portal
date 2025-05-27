<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Migration can be long... be patient !
// vagrant@default:/dades/html/portal$ php artisan migrate --path=database/migrations/2025_05_27_132745_add_id_to_stats_tables.php
// INFO  Running migrations.
// 2025_05_27_132745_add_id_to_stats_tables ........................................................................................... 58,239ms DONE

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'agoraportal_moodle2_stats_day',
            'agoraportal_moodle2_stats_month',
            'agoraportal_moodle2_stats_week',
            'agoraportal_nodes_stats_day',
            'agoraportal_nodes_stats_month',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasColumn($table, 'id')) {
                DB::statement("ALTER TABLE `$table` ADD `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'agoraportal_moodle2_stats_day',
            'agoraportal_moodle2_stats_month',
            'agoraportal_moodle2_stats_week',
            'agoraportal_nodes_stats_day',
            'agoraportal_nodes_stats_month',
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'id')) {
                DB::statement("ALTER TABLE `$table` DROP COLUMN `id`");
            }
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // agoraportal_moodle2_stats_day
        if (!Schema::hasTable('agoraportal_moodle2_stats_day')) {
            Schema::create('agoraportal_moodle2_stats_day', static function (Blueprint $table) {
                $table->string('clientcode', 10)->nullable();
                $table->string('clientDNS', 50);
                $table->integer('date');
                for ($h = 0; $h <= 23; $h++) {
                    $table->integer("h{$h}")->default(0);
                }
                $table->integer('total')->default(0);
                $table->integer('userstotal')->default(0);
                $table->integer('usersnodelsus')->default(0);
                $table->integer('usersactive')->default(0);
                $table->integer('userlogin')->default(0);
                $table->integer('usersactivelast90days')->default(0);
                $table->integer('usersactivelast30days')->default(0);
                $table->integer('coursesactive')->default(0);
                $table->string('diskConsume', 15)->default('0');

                $table->index('date');
            });
        }

        // agoraportal_moodle2_stats_month
        if (!Schema::hasTable('agoraportal_moodle2_stats_month')) {
            Schema::create('agoraportal_moodle2_stats_month', static function (Blueprint $table) {
                $table->string('clientcode', 10)->nullable();
                $table->string('clientDNS', 50);
                $table->integer('yearmonth')->nullable();
                $table->integer('usersactive')->default(0);
                $table->integer('userlogin')->default(0);
                $table->integer('courses')->default(0);
                $table->integer('coursesactive')->default(0);
                $table->integer('activities')->default(0);
                $table->string('lastaccess', 50)->nullable();
                $table->string('lastaccess_date', 50)->nullable();
                $table->string('lastaccess_user', 50)->nullable();
                $table->integer('total_access')->default(0);
                $table->integer('usersactivelast30days')->default(0);
                $table->string('diskConsume', 15)->default('0');
            });
        }

        // agoraportal_moodle2_stats_week
        if (!Schema::hasTable('agoraportal_moodle2_stats_week')) {
            Schema::create('agoraportal_moodle2_stats_week', static function (Blueprint $table) {
                $table->string('clientcode', 10)->nullable();
                $table->string('clientDNS', 50);
                $table->unsignedBigInteger('date')->nullable();
                $table->integer('usersactive')->default(0);
                $table->integer('userlogin')->default(0);
                $table->integer('courses')->default(0);
                $table->integer('coursesactive')->default(0);
                $table->integer('activities')->default(0);
                $table->string('lastaccess', 50)->nullable();
                $table->string('lastaccess_date', 50)->nullable();
                $table->string('lastaccess_user', 50)->nullable();
                $table->integer('total_access')->default(0);

                $table->index('date');
            });
        }

        // agoraportal_nodes_stats_day
        if (!Schema::hasTable('agoraportal_nodes_stats_day')) {
            Schema::create('agoraportal_nodes_stats_day', static function (Blueprint $table) {
                $table->string('clientcode', 10);
                $table->string('clientDNS', 50);
                $table->integer('date')->default(0);
                $table->integer('total')->default(0);
                $table->integer('posts')->default(0);
                $table->integer('userstotal')->default(0);
                $table->integer('usersactive')->default(0);
                $table->integer('usersactivelast30days')->default(0);
                $table->integer('usersactivelast90days')->default(0);
                $table->integer('diskConsume')->default(0);

                $table->index(['clientcode', 'date'], 'clientcodedate');
            });
        }

        // agoraportal_nodes_stats_month
        if (!Schema::hasTable('agoraportal_nodes_stats_month')) {
            Schema::create('agoraportal_nodes_stats_month', static function (Blueprint $table) {
                $table->string('clientcode', 10);
                $table->string('clientDNS', 50);
                $table->integer('yearmonth')->nullable();
                $table->integer('total')->default(0);
                $table->integer('posts')->default(0);
                $table->integer('userstotal')->default(0);
                $table->integer('usersactive')->default(0);
                $table->dateTime('lastactivity')->default('1970-01-01 00:00:00');
                $table->integer('diskConsume')->default(0);

                $table->index(['clientcode', 'yearmonth'], 'clientcodeyearmonth');
                $table->index('clientcode');
                $table->index('yearmonth');
            });
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
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
            }
        }
    }
};

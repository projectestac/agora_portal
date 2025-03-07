<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable(); // Si el migrate falla: ALTER TABLE roles ADD COLUMN deleted_at TIMESTAMP NULL;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};

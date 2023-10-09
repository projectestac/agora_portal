<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('batch_logs', static function (Blueprint $table) {
            $table->id();
            $table->integer('instance_id')->unsigned()->default('0');
            $table->string('password', 100)->nullable();
            $table->timestamps();

            $table->foreign('instance_id')->references('id')->on('instances');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('batch_logs');
    }
};

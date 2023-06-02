<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('standard_logs', static function (Blueprint $table) {
            $table->id();
            $table->integer('client_id')->unsigned()->default('0');
            $table->bigInteger('user_id')->unsigned()->default('0');
            $table->tinyInteger('action_type')->default(0);
            $table->text('action_description')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('standard_logs');
    }
};

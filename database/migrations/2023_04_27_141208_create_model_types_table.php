<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('model_types', static function (Blueprint $table) {
            $table->increments('id');
            $table->string('short_code', 10)->unique();
            $table->string('description')->default('');
            $table->string('url')->default('');
            $table->string('db', 25)->default('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('model_types');
    }
};
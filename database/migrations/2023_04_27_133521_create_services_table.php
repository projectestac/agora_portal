<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('services', static function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->default('');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('description', 255)->default('');
            $table->string('slug', 50)->default('');
            $table->bigInteger('quota')->unsigned()->default(5 * 1024 * 1024 * 1024); // 5 GB
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('services');
    }
};

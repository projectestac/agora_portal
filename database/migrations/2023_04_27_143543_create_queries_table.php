<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('queries', static function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id')->unsigned();
            $table->text('query')->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['select', 'insert', 'update', 'delete', 'alter', 'drop'])->default('select');
            $table->timestamps();
         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('queries');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('request_type_service', static function (Blueprint $table) {
            $table->increments('id');
            $table->integer('request_type_id')->unsigned();
            $table->integer('service_id')->unsigned();

            $table->foreign('request_type_id')->references('id')->on('request_types');
            $table->foreign('service_id')->references('id')->on('services');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('request_type_service');
    }
};

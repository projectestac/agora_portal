<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('clients', static function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 8)->unique()->nullable();
            $table->string('name', 200)->default('');
            $table->string('dns', 50)->unique()->nullable();
            $table->string('old_dns', 50)->nullable();
            $table->enum('url_type', ['standard', 'subdomain'])->default('standard');
            $table->string('host', 100)->unique()->nullable();
            $table->string('old_host', 100)->unique()->nullable();
            $table->string('address', 200)->default('');
            $table->string('city', 100)->default('');
            $table->string('postal_code', 5)->default('00000');
            $table->string('description')->default('');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('location_id')->unsigned()->default('0');
            $table->integer('type_id')->unsigned()->default('0');
            $table->enum('visible', ['yes', 'no'])->default('yes');
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('type_id')->references('id')->on('client_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('clients');
    }
};

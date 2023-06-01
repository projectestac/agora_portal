<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('requests', static function (Blueprint $table) {
            $table->increments('id');
            $table->integer('request_type_id')->unsigned();
            $table->integer('service_id')->unsigned();
            $table->integer('client_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->enum('status', ['pending', 'under_study', 'solved', 'denied'])->default('pending');
            $table->text('user_comment')->nullable();
            $table->text('admin_comment')->nullable();
            $table->text('private_note')->nullable();
            $table->timestamps();

            $table->foreign('request_type_id')->references('id')->on('request_types');
            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('requests');
    }
};

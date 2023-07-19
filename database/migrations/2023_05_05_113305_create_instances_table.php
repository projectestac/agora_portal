<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('instances', static function (Blueprint $table) {
            $status = [
                \App\Models\Instance::STATUS_PENDING,
                \App\Models\Instance::STATUS_ACTIVE,
                \App\Models\Instance::STATUS_INACTIVE,
                \App\Models\Instance::STATUS_DENIED,
                \App\Models\Instance::STATUS_WITHDRAWN,
                \App\Models\Instance::STATUS_BLOCKED,
            ];

            $table->increments('id');
            $table->integer('client_id')->unsigned()->default('0');
            $table->integer('service_id')->unsigned()->default('0');
            $table->enum('status', $status)->default('pending');
            $table->integer('db_id')->unsigned()->default('0');
            $table->string('db_host')->default('');
            $table->bigInteger('quota')->unsigned()->default(0);
            $table->bigInteger('used_quota')->unsigned()->default(0);
            $table->integer('model_type_id')->unsigned()->default('0');
            $table->string('contact_name', 150)->default('');
            $table->string('contact_profile', 150)->default('');
            $table->text('observations')->nullable();
            $table->text('annotations')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('model_type_id')->references('id')->on('model_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('client_services');
    }
};

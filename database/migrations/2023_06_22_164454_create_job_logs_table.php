<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('success_jobs', static function (Blueprint $table) {
            $table->id();
            $table->string('job_id');
            $table->string('queue');
            $table->string('connection');
            $table->text('payload');
            $table->text('result')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('success_jobs');
    }
};

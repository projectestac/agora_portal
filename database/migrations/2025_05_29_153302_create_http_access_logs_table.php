<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('http_access_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('accessed_at')->useCurrent();
            $table->string('ip', 45); // IPv6 support
            $table->text('user_agent');
            $table->text('url');
            $table->string('method', 10);
            $table->text('payload')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('http_access_logs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_key_id')->nullable()->constrained('api_keys')->nullOnDelete();
            $table->string('client_name')->nullable();
            $table->string('ip_address', 45);
            $table->string('method', 10);
            $table->string('endpoint');
            $table->json('request_params')->nullable();
            $table->unsignedSmallInteger('status_code');
            $table->unsignedInteger('records_count')->default(0);
            $table->float('duration_ms', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};

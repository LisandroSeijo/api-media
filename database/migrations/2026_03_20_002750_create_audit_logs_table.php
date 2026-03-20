<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('service', 500)->index();
            $table->string('method', 10)->index();
            $table->json('request_body')->nullable();
            $table->integer('response_code')->index();
            $table->json('response_body')->nullable();
            $table->string('ip_address', 45)->index();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // Índices compuestos para consultas comunes
            $table->index(['user_id', 'created_at']);
            $table->index(['response_code', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

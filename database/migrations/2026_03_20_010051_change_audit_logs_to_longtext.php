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
        Schema::table('audit_logs', function (Blueprint $table) {
            // Cambiar de JSON a LONGTEXT para permitir responses grandes
            $table->longText('request_body')->nullable()->change();
            $table->longText('response_body')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Revertir a JSON
            $table->json('request_body')->nullable()->change();
            $table->json('response_body')->nullable()->change();
        });
    }
};

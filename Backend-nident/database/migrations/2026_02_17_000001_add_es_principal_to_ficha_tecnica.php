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
        Schema::table('administracion_producto_ficha_tecnica', function (Blueprint $table) {
            $table->char('es_principal', 1)->default('N')->after('orden')->comment('S = mostrada en web como ficha principal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('administracion_producto_ficha_tecnica', function (Blueprint $table) {
            $table->dropColumn('es_principal');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizacion_config', function (Blueprint $table) {
            $table->json('hosting_detalles')->nullable()->after('info_bloques');
        });
    }

    public function down(): void
    {
        Schema::table('cotizacion_config', function (Blueprint $table) {
            $table->dropColumn('hosting_detalles');
        });
    }
};

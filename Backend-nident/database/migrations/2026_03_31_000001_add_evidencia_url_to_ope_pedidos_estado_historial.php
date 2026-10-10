<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Evidencia fotográfica opcional al cambiar estado (repartidor).
     */
    public function up(): void
    {
        Schema::table('ope_pedidos_estado_historial', function (Blueprint $table) {
            $table->string('evidencia_url', 512)->nullable()->after('observacion');
        });
    }

    public function down(): void
    {
        Schema::table('ope_pedidos_estado_historial', function (Blueprint $table) {
            $table->dropColumn('evidencia_url');
        });
    }
};

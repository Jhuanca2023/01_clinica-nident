<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('administracion_producto', function (Blueprint $table) {
            if (! Schema::hasColumn('administracion_producto', 'oferta_maxima_cantidad')) {
                $table->unsignedInteger('oferta_maxima_cantidad')->nullable()->after('precio_yape');
            }
            if (! Schema::hasColumn('administracion_producto', 'oferta_maxima_cantidad_por_precio')) {
                $table->decimal('oferta_maxima_cantidad_por_precio', 10, 2)->nullable()->after('oferta_maxima_cantidad');
            }
        });
    }

    public function down(): void
    {
        Schema::table('administracion_producto', function (Blueprint $table) {
            if (Schema::hasColumn('administracion_producto', 'oferta_maxima_cantidad_por_precio')) {
                $table->dropColumn('oferta_maxima_cantidad_por_precio');
            }
            if (Schema::hasColumn('administracion_producto', 'oferta_maxima_cantidad')) {
                $table->dropColumn('oferta_maxima_cantidad');
            }
        });
    }
};

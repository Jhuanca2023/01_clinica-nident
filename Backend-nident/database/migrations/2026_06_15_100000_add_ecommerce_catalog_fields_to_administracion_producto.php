<?php

use App\Support\ProductoEcommerceCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('administracion_producto', function (Blueprint $table) {
            if (!Schema::hasColumn('administracion_producto', 'color')) {
                $table->string('color', 20)->nullable()->after('url_imagen');
            }
            if (!Schema::hasColumn('administracion_producto', 'tallas_disponibles')) {
                $table->string('tallas_disponibles', 120)
                    ->default('S,M,L,XL,XXL')
                    ->after('color');
            }
            if (!Schema::hasColumn('administracion_producto', 'colores_disponibles')) {
                $table->json('colores_disponibles')->nullable()->after('tallas_disponibles');
            }
        });

        $paleta = json_encode(ProductoEcommerceCatalog::paletaColoresPorDefecto(), JSON_UNESCAPED_UNICODE);
        DB::table('administracion_producto')
            ->whereNull('colores_disponibles')
            ->update(['colores_disponibles' => $paleta]);

        DB::table('administracion_producto')
            ->where(function ($q) {
                $q->whereNull('tallas_disponibles')->orWhere('tallas_disponibles', '');
            })
            ->update(['tallas_disponibles' => 'S,M,L,XL,XXL']);
    }

    public function down(): void
    {
        Schema::table('administracion_producto', function (Blueprint $table) {
            if (Schema::hasColumn('administracion_producto', 'colores_disponibles')) {
                $table->dropColumn('colores_disponibles');
            }
            if (Schema::hasColumn('administracion_producto', 'tallas_disponibles')) {
                $table->dropColumn('tallas_disponibles');
            }
            if (Schema::hasColumn('administracion_producto', 'color')) {
                $table->dropColumn('color');
            }
        });
    }
};

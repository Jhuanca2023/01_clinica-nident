<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('administracion_producto_categoria') && ! Schema::hasColumn('administracion_producto_categoria', 'url_imagen')) {
            Schema::table('administracion_producto_categoria', function (Blueprint $table) {
                $table->string('url_imagen', 500)->nullable()->after('nombre');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('administracion_producto_categoria') && Schema::hasColumn('administracion_producto_categoria', 'url_imagen')) {
            Schema::table('administracion_producto_categoria', function (Blueprint $table) {
                $table->dropColumn('url_imagen');
            });
        }
    }
};

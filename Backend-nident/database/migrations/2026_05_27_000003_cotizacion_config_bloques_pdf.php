<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizacion_config', function (Blueprint $table) {
            $table->json('tecnologias_detalles')->nullable()->after('hosting_detalles');
            $table->json('condiciones_detalles')->nullable()->after('tecnologias_detalles');
            $table->json('web_paginas_incluidas')->nullable()->after('condiciones_detalles');
            $table->json('web_panel_admin')->nullable()->after('web_paginas_incluidas');
        });
    }

    public function down(): void
    {
        Schema::table('cotizacion_config', function (Blueprint $table) {
            $table->dropColumn([
                'tecnologias_detalles',
                'condiciones_detalles',
                'web_paginas_incluidas',
                'web_panel_admin',
            ]);
        });
    }
};

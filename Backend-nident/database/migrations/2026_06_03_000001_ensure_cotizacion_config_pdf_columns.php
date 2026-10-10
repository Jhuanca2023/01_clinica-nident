<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Asegura columnas del editor PDF de cotización (por si 2026_06_02_000003 no corrió en producción).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cotizacion_config')) {
            return;
        }

        Schema::table('cotizacion_config', function (Blueprint $table) {
            if (!Schema::hasColumn('cotizacion_config', 'hosting_titulo')) {
                $table->string('hosting_titulo', 180)->nullable();
            }
            if (!Schema::hasColumn('cotizacion_config', 'doc_titulo')) {
                $table->string('doc_titulo', 180)->nullable();
            }
            if (!Schema::hasColumn('cotizacion_config', 'doc_acerca_titulo')) {
                $table->string('doc_acerca_titulo', 180)->nullable();
            }
            if (!Schema::hasColumn('cotizacion_config', 'doc_acerca_items')) {
                $table->json('doc_acerca_items')->nullable();
            }
            if (!Schema::hasColumn('cotizacion_config', 'doc_tipo')) {
                $table->string('doc_tipo', 180)->nullable();
            }
            if (!Schema::hasColumn('cotizacion_config', 'doc_numero')) {
                $table->string('doc_numero', 180)->nullable();
            }
            if (!Schema::hasColumn('cotizacion_config', 'doc_moneda')) {
                $table->string('doc_moneda', 180)->nullable();
            }
            if (!Schema::hasColumn('cotizacion_config', 'doc_vigencia')) {
                $table->string('doc_vigencia', 180)->nullable();
            }
            if (!Schema::hasColumn('cotizacion_config', 'doc_elaborado_por')) {
                $table->string('doc_elaborado_por', 180)->nullable();
            }
            if (!Schema::hasColumn('cotizacion_config', 'plan_pagos_config')) {
                $table->json('plan_pagos_config')->nullable();
            }
            if (!Schema::hasColumn('cotizacion_config', 'plan_pagos_footer')) {
                $table->string('plan_pagos_footer', 300)->nullable();
            }
            if (!Schema::hasColumn('cotizacion_config', 'hosting_detalles')) {
                $table->json('hosting_detalles')->nullable();
            }
            if (!Schema::hasColumn('cotizacion_config', 'tecnologias_detalles')) {
                $table->json('tecnologias_detalles')->nullable();
            }
            if (!Schema::hasColumn('cotizacion_config', 'condiciones_detalles')) {
                $table->json('condiciones_detalles')->nullable();
            }
            if (!Schema::hasColumn('cotizacion_config', 'web_paginas_incluidas')) {
                $table->json('web_paginas_incluidas')->nullable();
            }
            if (!Schema::hasColumn('cotizacion_config', 'web_panel_admin')) {
                $table->json('web_panel_admin')->nullable();
            }
        });
    }

    public function down(): void
    {
        // No revertir: columnas pueden tener datos en producción.
    }
};

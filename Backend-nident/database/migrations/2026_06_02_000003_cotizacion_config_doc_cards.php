<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizacion_config', function (Blueprint $table) {
            if (!Schema::hasColumn('cotizacion_config', 'hosting_titulo')) {
                $table->string('hosting_titulo', 180)->nullable()->after('plan_pagos_footer');
            }
            if (!Schema::hasColumn('cotizacion_config', 'doc_titulo')) {
                $table->string('doc_titulo', 180)->nullable()->after('hosting_titulo');
            }
            if (!Schema::hasColumn('cotizacion_config', 'doc_acerca_titulo')) {
                $table->string('doc_acerca_titulo', 180)->nullable()->after('doc_titulo');
            }
            if (!Schema::hasColumn('cotizacion_config', 'doc_acerca_items')) {
                $table->json('doc_acerca_items')->nullable()->after('doc_acerca_titulo');
            }
            if (!Schema::hasColumn('cotizacion_config', 'doc_tipo')) {
                $table->string('doc_tipo', 180)->nullable()->after('doc_acerca_items');
            }
            if (!Schema::hasColumn('cotizacion_config', 'doc_numero')) {
                $table->string('doc_numero', 180)->nullable()->after('doc_tipo');
            }
            if (!Schema::hasColumn('cotizacion_config', 'doc_moneda')) {
                $table->string('doc_moneda', 180)->nullable()->after('doc_numero');
            }
            if (!Schema::hasColumn('cotizacion_config', 'doc_vigencia')) {
                $table->string('doc_vigencia', 180)->nullable()->after('doc_moneda');
            }
            if (!Schema::hasColumn('cotizacion_config', 'doc_elaborado_por')) {
                $table->string('doc_elaborado_por', 180)->nullable()->after('doc_vigencia');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cotizacion_config', function (Blueprint $table) {
            $drop = [];
            foreach ([
                'hosting_titulo',
                'doc_titulo',
                'doc_acerca_titulo',
                'doc_acerca_items',
                'doc_tipo',
                'doc_numero',
                'doc_moneda',
                'doc_vigencia',
                'doc_elaborado_por',
            ] as $col) {
                if (Schema::hasColumn('cotizacion_config', $col)) {
                    $drop[] = $col;
                }
            }
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};


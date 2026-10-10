<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * S: visible en web | N: inactivo (solo admin / pestaña archivados)
     */
    public function up(): void
    {
        Schema::table('metadatos_paginas', function (Blueprint $table) {
            $table->char('activo', 1)->default('S')->after('descripcion_pagina');
        });

        DB::table('metadatos_paginas')->whereNull('activo')->update(['activo' => 'S']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('metadatos_paginas', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
};

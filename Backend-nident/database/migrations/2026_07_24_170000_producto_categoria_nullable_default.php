<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('administracion_producto')) {
            return;
        }

        // Sin categoría seleccionada → NULL (no default forzado a 1 / Camisero)
        DB::statement('ALTER TABLE administracion_producto MODIFY id_producto_categoria INT NULL DEFAULT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('administracion_producto')) {
            return;
        }

        DB::statement('ALTER TABLE administracion_producto MODIFY id_producto_categoria INT NULL DEFAULT 1');
    }
};

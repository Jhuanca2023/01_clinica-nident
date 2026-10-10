<?php

use App\Support\DatabaseAutoIncrementFixer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'cotizacion_modulo',
            'cotizacion_tipo_proyecto',
            'cotizacion_presupuesto',
            'cotizacion_presupuesto_detalle',
        ] as $table) {
            $column = DatabaseAutoIncrementFixer::TABLES[$table] ?? null;
            if ($column) {
                DatabaseAutoIncrementFixer::fix($table, $column);
            }
        }
    }

    public function down(): void
    {
        //
    }
};

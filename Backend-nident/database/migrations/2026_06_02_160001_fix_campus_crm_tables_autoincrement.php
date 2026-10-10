<?php

use App\Support\DatabaseAutoIncrementFixer;
use Illuminate\Database\Migrations\Migration;

/**
 * Corrige AUTO_INCREMENT en tablas campus (notas, código, archivos, etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'campus_cliente_notas'     => 'id_nota',
            'campus_cliente_codigo'    => 'id_archivo',
            'campus_archivos'          => 'id_archivo',
            'campus_enlaces'           => 'id_enlace',
            'campus_actividad'         => 'id_actividad',
            'campus_cliente_proyecto'  => 'id',
            'campus_proyecto_paquetes' => 'id_paquete',
            'campus_agenda_fases'      => 'id_fase',
            'campus_agenda_tareas'     => 'id_tarea',
        ] as $table => $column) {
            DatabaseAutoIncrementFixer::fix($table, $column);
        }
    }

    public function down(): void
    {
        //
    }
};

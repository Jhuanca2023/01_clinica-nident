<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Título de "Líneas Valento" en mayúsculas/minúsculas como la plantilla temp02:
 * la fuente Cinzel Decorative adorna solo las mayúsculas.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_home_coleccion_seccion')) {
            return;
        }

        DB::table('web_home_coleccion_seccion')
            ->where('seccion_titulo', 'LÍNEAS rossety')
            ->update([
                'seccion_titulo'    => 'Líneas Valento',
                'seccion_subtitulo' => 'Mayorista · stock permanente',
            ]);
    }

    public function down(): void
    {
        //
    }
};

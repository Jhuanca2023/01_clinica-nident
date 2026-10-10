<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lookbook del home con el contenido de la plantilla temp02 (.lookbok-section).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_lookbook')) {
            return;
        }

        DB::table('web_lookbook')->update([
            'subtitulo'          => 'Desde Zaragoza, 1994',
            'titulo'             => '#Valento',
            'titulo_accent'      => 'Stile',
            'descripcion'        => 'Valento Textile S.L. nació en Zaragoza y hoy es uno de los mayores proveedores europeos de ropa laboral, textil promocional y vestuario deportivo. En Perú, Valento Stile S.A.C. atiende venta mayorista, distribución a profesionales y colectividades: uniformes, polos, camisetas y prendas listas para personalizar con el logo de tu empresa, con stock permanente.',
            'url_imagen_lateral' => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?auto=format&fit=crop&w=1200&q=80',
            'imagen_lateral_texto' => 'Valento',
        ]);
    }

    public function down(): void
    {
        //
    }
};

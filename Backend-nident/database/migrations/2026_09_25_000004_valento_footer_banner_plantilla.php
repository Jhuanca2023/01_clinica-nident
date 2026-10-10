<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Banner previo al footer con el contenido de la plantilla temp02 (.lv-footer-banner).
 * En el título, *palabra* se muestra resaltada en dorado.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_footer_banner')) {
            return;
        }

        DB::table('web_footer_banner')->update([
            'subtitulo'  => 'Semplicemente Valento',
            'titulo'     => "Ropa *laboral*\nlista para tu logo",
            'btn_texto'  => 'Ver Colección',
            'btn_url'    => '/tienda',
            'url_imagen' => 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=1800&q=80',
        ]);
    }

    public function down(): void
    {
        //
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('web_pagina_contacto')) {
            DB::table('web_pagina_contacto')->where('id', 1)->update([
                'banner_titulo'   => 'Contáctanos',
                'form_titulo'     => 'Escríbenos un mensaje',
                'form_subtitulo'  => 'Cuéntanos qué producto necesitas y te responderemos a la brevedad.',
                'url_imagen_form' => 'temp02/assets/img/jh_importaciones/inicio/jh_importaciones_img_5.jpg',
                'telefono'        => '981 629 466 · 958 444 413',
                'email'           => 'jyhimportaciones@hotmil.com',
                'ubicacion'       => 'Jr. Junín 1776 interior 20, Cercado de Lima',
                'updated_at'      => now(),
            ]);
        }

        if (Schema::hasTable('web_pagina_nosotros')) {
            DB::table('web_pagina_nosotros')->where('id', 1)->update([
                'banner_titulo' => 'Nosotros',
                'updated_at'    => now(),
            ]);
        }

        if (Schema::hasTable('web_pagina_contacto') && Schema::hasColumn('web_pagina_contacto', 'banner_url_imagen')) {
            DB::table('web_pagina_contacto')->where('id', 1)->update([
                'banner_url_imagen' => 'temp02/assets/img/jh_importaciones/inicio/jh_importaciones_img_5.jpg',
            ]);
        }

        if (Schema::hasTable('web_contacto_landing')) {
            DB::table('web_contacto_landing')->updateOrInsert(
                ['id' => 1],
                [
                    'email_destino' => env('MAIL_CONTACT_TO', env('MAIL_FROM_ADDRESS', 'jhovani@jvalverde.com')),
                    'updated_at'    => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        //
    }
};

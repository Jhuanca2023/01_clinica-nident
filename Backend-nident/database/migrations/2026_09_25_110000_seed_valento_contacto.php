<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Página Contacto: reemplaza los textos heredados de Liam por los del template
 * Valento (texto exacto de temp02/contact.html).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_pagina_contacto')) {
            return;
        }

        $datos = [
            'banner_titulo'      => 'Cotiza tu <span>uniforme</span>',
            'hero_subtitulo'     => 'Mayorista y colectividades — te respondemos en el día',
            'banner_url_imagen'  => 'https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?w=1600&q=80',
            // Etiqueta pequeña sobre el título del hero
            'form_subtitulo'     => 'Valento Stile S.A.C. — La Victoria',
            'form_titulo'        => 'Envíanos un mensaje',
            'form_descripcion'   => 'Te responderemos en menos de 24 horas hábiles.',
            'btn_texto'          => 'Enviar Mensaje',
            'ubicacion_etiqueta' => 'Dirección',
            'ubicacion'          => "Calle Los Flamencos 318, Urb. Santa Catalina\nLa Victoria, Lima — Perú",
            'telefono_etiqueta'  => 'Teléfono',
            'telefono'           => '+51 964 381 275',
            'telefonos_texto'    => 'WhatsApp disponible',
            'email_etiqueta'     => 'Correo',
            'email'              => 'ventas@rossety.pe',
            'emails_texto'       => 'ventas@rossety.pe',
            'horario_etiqueta'   => 'Horario',
            'horario_linea1'     => 'Lun–Vie: 9am – 7pm',
            'horario_linea2'     => 'Sáb: 9am – 5pm',
            'horario_dias'       => 'Dom: 10am – 2pm',
            'mapa_embed_url'     => '<iframe src="https://maps.google.com/maps?q=La%20Victoria%2C%20Lima%2C%20Per%C3%BA&z=15&output=embed" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Ubicación Valento"></iframe>',
            'updated_at'         => now(),
        ];

        $datos = array_filter(
            $datos,
            fn ($col) => Schema::hasColumn('web_pagina_contacto', $col),
            ARRAY_FILTER_USE_KEY
        );

        $id = DB::table('web_pagina_contacto')->value('id');
        if ($id) {
            DB::table('web_pagina_contacto')->where('id', $id)->update($datos);
        } else {
            DB::table('web_pagina_contacto')->insert($datos + ['created_at' => now()]);
        }
    }

    public function down(): void
    {
        // Datos de contenido: se editan desde el admin; no se revierte automáticamente.
    }
};

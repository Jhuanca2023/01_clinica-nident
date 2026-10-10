<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Página Nosotros: reemplaza los textos heredados de Liam por los del template
 * Valento (texto exacto de temp02/about.html). Sin línea de tiempo: el template no la tiene.
 */
return new class extends Migration
{
    private function svg(string $paths): string
    {
        return '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">'
            . $paths . '</svg>';
    }

    public function up(): void
    {
        if (Schema::hasTable('web_pagina_nosotros')) {
            $valores = [
                [
                    'icon'   => $this->svg('<path d="M21 8L12 3L3 8l9 5l9-5z"/><path d="M3 12l9 5l9-5"/><path d="M3 16l9 5l9-5"/>'),
                    'titulo' => 'Stock',
                    'texto'  => 'Millones de prendas en stock permanente para que el uniforme de tu equipo no espere una temporada.',
                ],
                [
                    'icon'   => $this->svg('<rect x="1" y="4" width="14" height="11" rx="1"/><path d="M15 8h4l3 3v4h-7V8z"/><circle cx="5.5" cy="17.5" r="2.5"/><circle cx="18.5" cy="17.5" r="2.5"/>'),
                    'titulo' => 'Mayorista',
                    'texto'  => 'Venta orientada a distribuidores, profesionales y empresas que compran por volumen, con despacho ágil.',
                ],
                [
                    'icon'   => $this->svg('<path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="9" cy="9" r="2"/>'),
                    'titulo' => 'Personalización',
                    'texto'  => 'Camisetas, polos y prendas listas para bordar o estampar el logotipo de tu empresa.',
                ],
                [
                    'icon'   => $this->svg('<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'),
                    'titulo' => 'Colectividades',
                    'texto'  => 'Uniformamos equipos de industria, servicios, deporte y promoción, con tallas y colores en serie.',
                ],
                [
                    'icon'   => $this->svg('<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/>'),
                    'titulo' => 'Confianza',
                    'texto'  => 'Precios de distribución, comprobante y atención directa por WhatsApp para cotizar tu pedido.',
                ],
                [
                    'icon'   => $this->svg('<path d="M9 18h6"/><path d="M10 22h4"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A6 6 0 1 0 7.5 11.5c.76.76 1.23 1.52 1.41 2.5"/>'),
                    'titulo' => 'Innovación',
                    'texto'  => 'Vestuario técnico y soluciones de protección personal pensadas para el uso intensivo.',
                ],
            ];

            $datos = [
                'titulo_hero'         => 'Ropa laboral,<br>con <span>stock real</span>',
                'subtitulo_hero'      => 'Uniformes, textil promocional y vestuario deportivo para empresas, profesionales y colectividades.',
                'url_imagen_hero'     => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=1600&q=80',
                'titulo_seccion'      => 'De Zaragoza al taller de tu empresa',
                'descripcion_seccion' => '<p>La casa matriz, Valento Textile S.L., nació en Zaragoza, España, en 1994. Hoy Valento es una de las marcas europeas de referencia en ropa laboral, textil promocional y vestuario deportivo, con un catálogo masivo y millones de prendas en stock permanente.</p>'
                    . '<p style="margin-top:14px;">Valento Stile S.A.C. opera la marca en Perú: venta mayorista, distribución ágil a profesionales y atención a colectividades. Polos, camisetas, uniformes y protección personal, listos para personalizar con el logotipo de tu empresa.</p>',
                'url_imagen_seccion'  => 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=800&q=80',
                'historia_badge'      => 'Desde 1994',
                'valores_json'        => json_encode($valores, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'timeline_json'       => json_encode([]),
                'cta_titulo'          => '¿Cotizamos el uniforme <em>de tu equipo</em>?',
                'cta_texto'           => 'Revisa el catálogo o escríbenos: ropa laboral, promocional y deportiva Valento.',
                'cta_btn_texto'       => 'Ver colección',
                'cta_btn_url'         => '/tienda',
                'updated_at'          => now(),
            ];

            $datos = array_filter(
                $datos,
                fn ($col) => Schema::hasColumn('web_pagina_nosotros', $col),
                ARRAY_FILTER_USE_KEY
            );

            $id = DB::table('web_pagina_nosotros')->value('id');
            if ($id) {
                DB::table('web_pagina_nosotros')->where('id', $id)->update($datos);
            } else {
                DB::table('web_pagina_nosotros')->insert($datos + ['created_at' => now()]);
            }
        }

        if (Schema::hasTable('web_contadores') && Schema::hasColumn('web_contadores', 'contexto')) {
            DB::table('web_contadores')->where('contexto', 'nosotros')->update(['Activo' => 'N']);

            $contadores = [
                ['valor' => '32',   'sufijo' => '',   'etiqueta' => 'Años desde Zaragoza'],
                ['valor' => '3',    'sufijo' => '',   'etiqueta' => 'Líneas: laboral, promo y sport'],
                ['valor' => '1994', 'sufijo' => '',   'etiqueta' => 'Año de fundación'],
                ['valor' => '24',   'sufijo' => ' h', 'etiqueta' => 'Atención a colectividades'],
            ];
            foreach ($contadores as $i => $c) {
                DB::table('web_contadores')->insert($c + [
                    'contexto'   => 'nosotros',
                    'orden'      => $i + 1,
                    'Activo'     => 'S',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('web_nosotros_quienes_beneficio')) {
            DB::table('web_nosotros_quienes_beneficio')->update(['Activo' => 'N']);

            $items = [
                'Ropa laboral para todos los sectores',
                'Textil promocional listo para tu logo',
                'Vestuario deportivo y técnico',
                'Despacho mayorista desde La Victoria, Lima',
            ];
            foreach ($items as $i => $texto) {
                DB::table('web_nosotros_quienes_beneficio')->insert([
                    'texto'      => $texto,
                    'orden'      => $i + 1,
                    'Activo'     => 'S',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Datos de contenido: se editan desde el admin; no se revierte automáticamente.
    }
};

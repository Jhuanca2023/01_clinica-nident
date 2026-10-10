<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rebrand: reemplaza los datos de marca de Liam Vercceli por Valento.
 * Textos tomados de la plantilla public/temp02. Teléfonos y WhatsApp quedan
 * con el marcador 51999999999 (PENDIENTE) hasta tener los números de Valento.
 */
return new class extends Migration
{
    private const EMAIL = 'informacion@valento.com';
    private const WA_NUMERO = '51999999999';
    private const WA_MENSAJE = '¡Hola Valento! Me gustaría recibir más información sobre sus productos.';
    private const DESCRIPCION = 'Valento Textile S.L. nació en Zaragoza y hoy es uno de los mayores proveedores europeos de ropa laboral, textil promocional y vestuario deportivo. En Perú, Valento Stile S.A.C. atiende venta mayorista, distribución a profesionales y colectividades: uniformes, polos, camisetas y prendas listas para personalizar con el logo de tu empresa, con stock permanente.';

    public function up(): void
    {
        $this->header();
        $this->footer();
        $this->whatsapp();
        $this->paginaContacto();
        $this->contenidoCms();
        $this->metadatos();
        $this->fichaTecnica();
    }

    public function down(): void
    {
        // Rebrand de datos: no se revierte.
    }

    private function set(string $table, array $values): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }
        $values = array_filter(
            $values,
            fn ($col) => Schema::hasColumn($table, $col),
            ARRAY_FILTER_USE_KEY
        );
        if ($values) {
            DB::table($table)->update($values);
        }
    }

    private function header(): void
    {
        $this->set('web_header', [
            'top_email'         => self::EMAIL,
            'telefono_numero'   => '999 999 999',
            'telefono_href'     => 'tel:+' . self::WA_NUMERO,
            'top_telefonos'     => '999 999 999',
            'topbar_wa_numero'  => '999 999 999',
            'url_whatsapp_top'  => 'https://wa.me/' . self::WA_NUMERO,
        ]);
    }

    private function footer(): void
    {
        $this->set('web_footer', [
            'contacto_email'               => self::EMAIL,
            'footer_emails'                => self::EMAIL,
            'footer_cta_titulo'            => 'rossety',
            'texto_copyright'              => '© ' . date('Y') . ' Valento Stile S.A.C. Todos los derechos reservados.',
            'contacto_telefono'            => '+' . self::WA_NUMERO,
            'contacto_telefono_secundario' => null,
            'footer_telefonos'             => '+' . self::WA_NUMERO,
            'whatsapp_numero'              => '+51 999 999 999',
            'whatsapp_mensaje'             => self::WA_MENSAJE,
            'url_whatsapp'                 => 'https://wa.me/' . self::WA_NUMERO . '?text=' . rawurlencode(self::WA_MENSAJE),
            'footer_redes'                 => json_encode([
                ['tipo' => 'whatsapp', 'url' => 'https://wa.me/' . self::WA_NUMERO, 'etiqueta' => 'WhatsApp', 'orden' => 1],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
    }

    private function whatsapp(): void
    {
        $this->set('web_whatsapp_config', [
            'wa_numero'         => self::WA_NUMERO,
            'wa_mensaje'        => self::WA_MENSAJE,
            'wa_burbuja_linea1' => '👕 ¿Buscas ropa laboral o uniformes?',
        ]);

        $this->set('web_carrito_config', [
            'asesor_mensaje' => 'Hola! Estoy en el carrito de Valento y me gustaría recibir asesoría para completar mi compra.',
        ]);
    }

    private function paginaContacto(): void
    {
        $this->set('web_pagina_contacto', [
            'email'           => self::EMAIL,
            'telefono'        => '+51 999 999 999',
            'telefonos_texto' => '+51 999 999 999',
        ]);

        if (Schema::hasTable('web_pagina_contacto') && Schema::hasColumn('web_pagina_contacto', 'frase_texto')) {
            DB::table('web_pagina_contacto')
                ->where('frase_texto', 'like', '%982311335%')
                ->update(['frase_texto' => DB::raw("REPLACE(frase_texto, '982311335', '999 999 999')")]);
        }

        if (Schema::hasTable('web_popup_promo') && Schema::hasColumn('web_popup_promo', 'whatsapp_numero')) {
            DB::table('web_popup_promo')
                ->where('whatsapp_numero', '51970088031')
                ->update(['whatsapp_numero' => self::WA_NUMERO]);
        }
    }

    private function contenidoCms(): void
    {
        $this->set('web_pagina_nosotros', ['descripcion_seccion' => '<p>' . self::DESCRIPCION . '</p>']);

        $this->set('web_lookbook', [
            'titulo'               => '#Valento',
            'titulo_accent'        => 'Stile',
            'imagen_lateral_texto' => 'Valento',
            'descripcion'          => self::DESCRIPCION,
        ]);

        $this->set('web_cliente_seccion', [
            'descripcion' => 'Empresas y colectividades que eligen la calidad Valento en todo el Perú.',
        ]);

        if (Schema::hasTable('web_promo_bloque') && Schema::hasColumn('web_promo_bloque', 'hero_subtitulo')) {
            DB::table('web_promo_bloque')
                ->where('hero_subtitulo', 'like', '%Liam%')
                ->update(['hero_subtitulo' => 'Valento · Ropa laboral · Textil']);
        }

        if (Schema::hasTable('web_slider') && Schema::hasColumn('web_slider', 'subtitulo')) {
            DB::table('web_slider')
                ->where('subtitulo', 'like', '%Liam%')
                ->update(['subtitulo' => 'Valento Stile']);
        }
    }

    private function metadatos(): void
    {
        if (! Schema::hasTable('metadatos_paginas')) {
            return;
        }

        $general = 'Valento Stile S.A.C. — ropa laboral, textil promocional y vestuario deportivo. Stock permanente y personalización con logo.';

        $paginas = [
            'home'                     => ['Valento | Ropa laboral y textil', $general],
            'web_gallery'              => ['Valento | Catálogo', 'Catálogo textil Valento: uniformes, polos, camisetas, vestuario deportivo y textil promocional.'],
            'web_productos'            => ['Valento | Catálogo', 'Catálogo textil Valento: uniformes, polos, camisetas, vestuario deportivo y textil promocional.'],
            'web_shopDetail_lista'     => ['Valento | Mi Carrito', 'Carrito de Compras - Valento'],
            'web_contact'              => ['Valento | Contacto', 'Contáctanos - Valento'],
            'web_about'                => ['Valento | Nosotros', 'Nosotros - Valento, Ropa laboral y textil'],
            'web_servicio_cliente'     => ['Valento | Atención al Cliente', 'Atención al cliente de Valento: consultas sobre pedidos, tallas, personalización y envíos.'],
            'web_ubicacion'            => ['Valento | Ubicación', 'Ubicación y horarios de atención de Valento.'],
            'web_politica_privacidad'  => ['Valento | Políticas de Privacidad', 'Políticas de privacidad de Valento Stile S.A.C.'],
            'web_terminos_condiciones' => ['Valento | Términos y Condiciones', 'Términos y condiciones de compra en Valento Stile S.A.C.'],
            'web_libro_reclamos'       => ['Valento | Libro de Reclamaciones', 'Libro de reclamaciones de Valento Stile S.A.C.'],
            'web_delivery_informacion' => ['Valento | Envíos', 'Envíos de ropa laboral y textil Valento a todo el Perú.'],
            'web_devoluciones'         => ['Valento | Cambios y Devoluciones', 'Políticas de cambios y devoluciones de Valento.'],
            'web_garantia'             => ['Valento | Garantía', 'Garantía de calidad en las prendas Valento.'],
            'web_videos'               => ['Valento | Videos', 'Videos de Valento.'],
            'web_maquinarias'          => ['Valento | Confección', 'Proceso de confección y calidad Valento.'],
            'blogs'                    => ['Valento | Blog', 'Blog de Valento.'],
        ];

        foreach ($paginas as $nombre => [$titulo, $descripcion]) {
            DB::table('metadatos_paginas')
                ->where('nombre_pagina', $nombre)
                ->update(['titulo_pagina' => $titulo, 'descripcion_pagina' => $descripcion]);
        }
    }

    private function fichaTecnica(): void
    {
        $table = 'administracion_producto_ficha_tecnica';
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'nuevo_caracteristicas')) {
            return;
        }

        DB::table($table)
            ->where('nuevo_caracteristicas', 'like', '%LIAM VERCCELI%')
            ->update([
                'nuevo_caracteristicas' => DB::raw("REPLACE(REPLACE(nuevo_caracteristicas, 'LIAM VERCCELI123', 'rossety'), 'LIAM VERCCELI', 'rossety')"),
            ]);
    }
};

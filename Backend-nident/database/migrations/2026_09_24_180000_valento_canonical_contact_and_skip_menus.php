<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Completa el rebrand: datos canónicos de temp02 (tel/WA/correos/Lima)
 * y desactiva menús SKIP. No edita 2026_09_24_000001.
 */
return new class extends Migration
{
    private const EMAIL_TOPBAR = 'ventas@rossety.pe';
    private const EMAIL_FOOTER = 'ventas@rossety.pe';
    private const WA_NUMERO = '51964381275';
    private const TELEFONO_DISPLAY = '+51 964 381 275';
    private const TELEFONO_SHORT = '964 381 275';
    private const WA_MENSAJE = '¡Hola Valento! Me gustaría recibir más información sobre sus productos.';
    private const DESCRIPCION = 'Valento Stile S.A.C. (RUC 20608473159) atiende venta mayorista y distribución a profesionales y colectividades en Perú: uniformes, polos, camisetas y prendas listas para personalizar con el logo de tu empresa, con stock permanente. Domicilio: Calle Los Flamencos 318, Urb. Santa Catalina, La Victoria, Lima.';
    private const HORARIO = 'Lun – Sáb: 9:00 – 18:00';

    public function up(): void
    {
        $this->header();
        $this->footer();
        $this->whatsapp();
        $this->paginaContacto();
        $this->contenidoCms();
        $this->deactivateSkipMenus();
    }

    public function down(): void
    {
        // Datos de marca: no se revierte.
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
            'top_email'        => self::EMAIL_TOPBAR,
            'telefono_numero'  => self::TELEFONO_SHORT,
            'telefono_href'    => 'tel:+' . self::WA_NUMERO,
            'top_telefonos'    => self::TELEFONO_SHORT,
            'topbar_wa_numero' => self::TELEFONO_SHORT,
            'url_whatsapp_top' => 'https://wa.me/' . self::WA_NUMERO,
            'top_ubicacion'    => 'Lima, Perú',
            'topbar_promo'     => self::HORARIO,
        ]);
    }

    private function footer(): void
    {
        $waMsg = self::WA_MENSAJE;
        $this->set('web_footer', [
            'contacto_email'               => self::EMAIL_FOOTER,
            'footer_emails'                => self::EMAIL_FOOTER,
            'footer_cta_titulo'            => 'rossety',
            'texto_copyright'              => '© ' . date('Y') . ' Valento Stile S.A.C. Todos los derechos reservados.',
            'contacto_telefono'            => self::TELEFONO_DISPLAY,
            'contacto_telefono_secundario' => null,
            'footer_telefonos'             => self::TELEFONO_DISPLAY,
            'whatsapp_numero'              => self::TELEFONO_DISPLAY,
            'whatsapp_mensaje'             => $waMsg,
            'url_whatsapp'                 => 'https://wa.me/' . self::WA_NUMERO . '?text=' . rawurlencode($waMsg),
            'contacto_direccion'           => 'Calle Los Flamencos 318, Urb. Santa Catalina, La Victoria, Lima – Perú',
            'footer_horario'               => self::HORARIO,
            'footer_redes'                 => json_encode([
                ['tipo' => 'instagram', 'url' => 'https://instagram.com', 'etiqueta' => 'Instagram', 'orden' => 1],
                ['tipo' => 'facebook', 'url' => 'https://facebook.com', 'etiqueta' => 'Facebook', 'orden' => 2],
                ['tipo' => 'tiktok', 'url' => 'https://tiktok.com', 'etiqueta' => 'TikTok', 'orden' => 3],
                ['tipo' => 'youtube', 'url' => 'https://youtube.com', 'etiqueta' => 'YouTube', 'orden' => 4],
                ['tipo' => 'whatsapp', 'url' => 'https://wa.me/' . self::WA_NUMERO, 'etiqueta' => 'WhatsApp', 'orden' => 5],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
    }

    private function whatsapp(): void
    {
        $this->set('web_whatsapp_config', [
            'wa_numero'         => self::WA_NUMERO,
            'wa_mensaje'        => self::WA_MENSAJE,
            'wa_burbuja_linea1' => '¿Buscas ropa laboral o uniformes?',
        ]);

        $this->set('web_carrito_config', [
            'asesor_mensaje' => 'Hola! Estoy en el carrito de Valento y me gustaría recibir asesoría para completar mi compra.',
        ]);
    }

    private function paginaContacto(): void
    {
        $this->set('web_pagina_contacto', [
            'email'           => self::EMAIL_FOOTER,
            'emails_texto'    => self::EMAIL_FOOTER,
            'telefono'        => self::TELEFONO_DISPLAY,
            'telefonos_texto' => self::TELEFONO_DISPLAY . "\nWhatsApp disponible",
            'direccion'       => 'Calle Los Flamencos 318, Urb. Santa Catalina, La Victoria, Lima – Perú',
            'horario'         => self::HORARIO,
        ]);

        if (Schema::hasTable('web_popup_promo') && Schema::hasColumn('web_popup_promo', 'whatsapp_numero')) {
            DB::table('web_popup_promo')
                ->whereIn('whatsapp_numero', ['51999999999', '51970088031', '999 999 999'])
                ->update(['whatsapp_numero' => self::WA_NUMERO]);
        }
    }

    private function contenidoCms(): void
    {
        $this->set('web_pagina_nosotros', [
            'descripcion_seccion' => '<p>' . self::DESCRIPCION . '</p>',
            'subtitulo_hero'      => 'Valento — Lima, Perú',
        ]);

        $this->set('web_lookbook', [
            'titulo'               => '#Valento',
            'titulo_accent'        => 'Stile',
            'imagen_lateral_texto' => 'Valento',
            'descripcion'          => self::DESCRIPCION,
        ]);

        if (Schema::hasTable('web_pagina_nosotros') && Schema::hasColumn('web_pagina_nosotros', 'descripcion_seccion')) {
            DB::table('web_pagina_nosotros')
                ->where('descripcion_seccion', 'like', '%Zaragoza%')
                ->update(['descripcion_seccion' => '<p>' . self::DESCRIPCION . '</p>']);
        }

        if (Schema::hasTable('web_lookbook') && Schema::hasColumn('web_lookbook', 'descripcion')) {
            DB::table('web_lookbook')
                ->where('descripcion', 'like', '%Zaragoza%')
                ->update(['descripcion' => self::DESCRIPCION]);
        }
    }

    private function deactivateSkipMenus(): void
    {
        if (! Schema::hasTable('sistema_menu')) {
            return;
        }

        $patterns = [
            '%/planes/%',
            '%/videos/%',
            '%/pagina-maquinarias/%',
            '%/blog%',
            '%/spa%',
            '%/campus%',
            '%/imprenta%',
            '%/rodantes%',
            '%galeria%maquin%',
        ];

        $activoCol = Schema::hasColumn('sistema_menu', 'Activo')
            ? 'Activo'
            : (Schema::hasColumn('sistema_menu', 'activo') ? 'activo' : null);

        if (! $activoCol) {
            return;
        }

        foreach ($patterns as $like) {
            $q = DB::table('sistema_menu')->where(function ($w) use ($like) {
                $w->where('url', 'like', $like);
                if (Schema::hasColumn('sistema_menu', 'nombre')) {
                    $w->orWhere('nombre', 'like', $like);
                }
                if (Schema::hasColumn('sistema_menu', 'descripcion')) {
                    $w->orWhere('descripcion', 'like', $like);
                }
            });
            $q->update([$activoCol => 'N']);
        }

        // Por nombre explícito (SKIP del prompt)
        $nombres = ['Planes', 'Videos', 'Maquinarias', 'Blog', 'Spa', 'Campus', 'Imprenta', 'Rodantes', 'Galería de maquinarias'];
        if (Schema::hasColumn('sistema_menu', 'nombre')) {
            DB::table('sistema_menu')
                ->whereIn('nombre', $nombres)
                ->update([$activoCol => 'N']);
        }
    }
};

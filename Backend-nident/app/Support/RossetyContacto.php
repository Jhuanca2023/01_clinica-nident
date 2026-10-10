<?php

namespace App\Support;

use App\Models\WebPaginaContacto;
use Illuminate\Support\Facades\Schema;

/** Contenido editable de /contacto (admin ▸ Página Contacto). Los valores por defecto son los textos originales de la web. */
class RossetyContacto
{
    public static function defaults(): array
    {
        return [
            'hero' => [
                'crumb' => 'Contacto',
                'titulo' => 'Contacto',
                'subtitulo' => 'Pide el tuyo y haz sonreír a alguien especial.',
            ],
            'cards' => [
                ['icono' => 'ph-whatsapp-logo', 'titulo' => 'WhatsApp Business', 'texto' => "964 381 275\nRespondemos en minutos.", 'url' => 'tel:+51964381275'],
                ['icono' => 'ph-phone', 'titulo' => 'Teléfono de contacto', 'texto' => "964 381 275\nPatricia Hancco", 'url' => 'tel:+51964381275'],
                ['icono' => 'ph-map-pin', 'titulo' => 'Dirección', 'texto' => "Lima, Perú\nCoordina tu recojo o delivery por WhatsApp.", 'url' => ''],
                ['icono' => 'ph-clock', 'titulo' => 'Horario de atención', 'texto' => "Lunes a sábado: 9:00 - 20:00\nDomingos y feriados con pedido previo.", 'url' => ''],
            ],
            'redes_titulo' => 'Síguenos',
            'form' => [
                'titulo' => 'Cuéntanos qué quieres regalar',
                'subtitulo' => 'Completa el formulario y se abrirá WhatsApp con tu mensaje listo para enviar.',
                'label_nombre' => 'Tu nombre *',
                'ph_nombre' => 'Ej. María Quispe',
                'label_telefono' => 'Tu teléfono',
                'ph_telefono' => 'Ej. 999 999 999',
                'label_motivo' => 'Motivo *',
                'motivos' => ['Cumpleaños', 'Aniversario', 'San Valentín', 'Día de la Madre', 'Día del Padre', 'Graduación', 'Pedido por mayor (cajas)', 'Otro'],
                'label_fecha' => 'Fecha de entrega',
                'label_mensaje' => 'Mensaje *',
                'ph_mensaje' => 'Cuéntanos qué detalle te gustó, colores, frase para el listón, etc.',
                'boton' => 'Enviar por WhatsApp',
                'hint' => 'Tus datos solo se usan para atender tu pedido.',
                'wa_saludo' => 'Hola Detalles Rossety, soy {nombre}.',
            ],
            // Datos del proveedor en el Libro de Reclamaciones (domicilio vacío = dirección del footer).
            'reclamos' => ['razon_social' => 'Detalles Rossety', 'ruc' => '', 'domicilio' => ''],
            'mapa' => [
                'titulo' => 'Ubicación de Detalles Rossety',
                'src' => 'https://www.google.com/maps?q=Relave,Ayacucho,Peru&output=embed',
            ],
        ];
    }

    /** Configuración guardada mezclada con los valores por defecto. */
    public static function config(): array
    {
        $saved = [];
        if (Schema::hasTable('web_pagina_contacto') && Schema::hasColumn('web_pagina_contacto', 'rossety_contacto')) {
            $raw = WebPaginaContacto::query()->value('rossety_contacto');
            $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
            $saved = is_array($decoded) ? $decoded : [];
        }

        $d = self::defaults();
        $cfg = $d;
        foreach (['hero', 'form', 'mapa', 'reclamos'] as $k) {
            $cfg[$k] = array_merge($d[$k], array_filter((array) ($saved[$k] ?? []), static fn ($v) => $v !== null));
        }
        if (isset($saved['redes_titulo'])) {
            $cfg['redes_titulo'] = (string) $saved['redes_titulo'];
        }
        if (isset($saved['cards']) && is_array($saved['cards'])) {
            $cfg['cards'] = array_values(array_filter($saved['cards'], 'is_array'));
        }
        if (! is_array($cfg['form']['motivos'])) {
            $cfg['form']['motivos'] = $d['form']['motivos'];
        }

        return $cfg;
    }

    /** Redes de "Síguenos": las mismas del footer (admin ▸ Footer). */
    public static function redes(): array
    {
        $f = (array) (\Helpers::footer_()[0] ?? []);
        $raw = $f['footer_redes'] ?? null;
        $redes = is_array($raw) ? $raw : json_decode((string) $raw, true);
        if (! is_array($redes)) {
            $redes = [];
            foreach (['facebook', 'tiktok', 'instagram', 'twitter', 'linkedin'] as $tipo) {
                if (! empty($f['red_social_'.$tipo])) {
                    $redes[] = ['tipo' => $tipo, 'url' => $f['red_social_'.$tipo]];
                }
            }
            $redes[] = ['tipo' => 'whatsapp', 'url' => \Helpers::whatsappUrl((object) $f)];
        }
        usort($redes, static fn ($a, $b) => ($a['orden'] ?? 0) <=> ($b['orden'] ?? 0));

        return array_values(array_filter($redes, static fn ($r) => trim((string) ($r['url'] ?? '')) !== ''));
    }
}

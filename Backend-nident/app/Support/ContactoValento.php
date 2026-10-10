<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de contacto canónicos de Valento resueltos desde el CMS
 * (web_footer / web_whatsapp_config) con fallback a los valores oficiales.
 */
class ContactoValento
{
    public const EMAIL_VENTAS = 'ventas@rossety.pe';
    public const WA_NUMERO = '51964381275';

    private static ?string $ventasEmailCache = null;
    private static ?string $whatsappNumeroCache = null;

    /** Correo de ventas: web_footer.contacto_email → primer footer_emails → fallback. */
    public static function ventasEmail(): string
    {
        if (self::$ventasEmailCache !== null) {
            return self::$ventasEmailCache;
        }

        $email = '';

        try {
            if (Schema::hasTable('web_footer')) {
                $row = DB::table('web_footer')->orderBy('id_footer')->first();
                if ($row) {
                    $email = self::firstEmail($row->contacto_email ?? null);
                    if ($email === '') {
                        $email = self::firstEmail($row->footer_emails ?? null);
                    }
                }
            }
        } catch (\Throwable $e) {
            $email = '';
        }

        return self::$ventasEmailCache = $email !== '' ? $email : self::EMAIL_VENTAS;
    }

    /** Número de WhatsApp (solo dígitos): web_whatsapp_config.wa_numero → web_footer → fallback. */
    public static function whatsappNumero(): string
    {
        if (self::$whatsappNumeroCache !== null) {
            return self::$whatsappNumeroCache;
        }

        $numero = '';

        try {
            if (Schema::hasTable('web_whatsapp_config')) {
                $cfg = DB::table('web_whatsapp_config')->orderBy('id')->first();
                $numero = self::soloDigitos($cfg->wa_numero ?? null);
            }

            if ($numero === '' && Schema::hasTable('web_footer')) {
                $row = DB::table('web_footer')->orderBy('id_footer')->first();
                $numero = self::soloDigitos($row->whatsapp_numero ?? null);
                if ($numero === '') {
                    $numero = self::soloDigitos($row->contacto_telefono ?? null);
                }
            }
        } catch (\Throwable $e) {
            $numero = '';
        }

        return self::$whatsappNumeroCache = $numero !== '' ? $numero : self::WA_NUMERO;
    }

    /** Teléfono legible (+51 964 381 275) derivado del número de WhatsApp. */
    public static function telefonoDisplay(): string
    {
        $digits = self::whatsappNumero();

        return preg_match('/^51(\d{3})(\d{3})(\d{3})$/', $digits, $m)
            ? '+51 ' . $m[1] . ' ' . $m[2] . ' ' . $m[3]
            : '+' . $digits;
    }

    /** URL wa.me con mensaje opcional. */
    public static function whatsappUrl(string $mensaje = ''): string
    {
        $url = 'https://wa.me/' . self::whatsappNumero();

        return $mensaje === '' ? $url : $url . '?text=' . rawurlencode($mensaje);
    }

    /** Limpia la caché estática (tests / cambios en caliente desde el CMS). */
    public static function flush(): void
    {
        self::$ventasEmailCache = null;
        self::$whatsappNumeroCache = null;
    }

    /** Primer correo válido de un campo que puede traer varios separados por coma/salto de línea. */
    private static function firstEmail(?string $raw): string
    {
        foreach (preg_split('/[\s,;]+/', (string) $raw) ?: [] as $candidato) {
            $candidato = trim($candidato);
            if ($candidato !== '' && filter_var($candidato, FILTER_VALIDATE_EMAIL)) {
                return $candidato;
            }
        }

        return '';
    }

    private static function soloDigitos(?string $raw): string
    {
        $digits = preg_replace('/\D/', '', (string) $raw);

        return ltrim((string) $digits, '0');
    }
}

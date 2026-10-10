<?php

namespace App\Services;

use App\Models\PagoPasarela;
use App\Models\PagoPasarelaConfig;

/**
 * PaymentGatewayManager
 *
 * Servicio centralizado para obtener la pasarela de pago activa
 * y sus claves de configuración desde la base de datos.
 *
 * Uso en CheckoutController:
 *   if (PaymentGatewayManager::isCulqi())  { ... lógica Culqi ... }
 *   if (PaymentGatewayManager::isIzipay()) { ... lógica Izipay ... }
 *   $publicKey = PaymentGatewayManager::getConfig('CULQI_PUBLIC_KEY');
 */
class PaymentGatewayManager
{
    /**
     * Retorna el código de la pasarela activa (culqi | izipay | stripe | paypal | whatsapp).
     * Si no hay ninguna activa o la tabla no existe, devuelve 'culqi' como fallback seguro.
     */
    public static function getActiveCodigo(): string
    {
        try {
            $pasarela = PagoPasarela::where('is_activo', 1)->first();
            return $pasarela ? $pasarela->codigo : 'culqi';
        } catch (\Exception $e) {
            return 'culqi';
        }
    }

    /**
     * Retorna el objeto PagoPasarela activo con sus configs cargadas.
     */
    public static function getActivePasarela(): ?PagoPasarela
    {
        try {
            return PagoPasarela::with('configs')
                               ->where('is_activo', 1)
                               ->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Retorna el valor de una clave de configuración de la pasarela activa.
     * Origen: 1) Base de datos (pago_pasarela_config), 2) Fallback config/.env.
     *
     * @param  string $clave  Ej: 'CULQI_PUBLIC_KEY', 'IZIPAY_USERNAME'
     * @return string|null
     */
    public static function getConfig(string $clave): ?string
    {
        try {
            $pasarela = self::getActivePasarela();

            if (!$pasarela) {
                return null;
            }

            $config = $pasarela->configs->firstWhere('clave', $clave);
            if (! $config) {
                return null;
            }

            // Modo activo de la pasarela: 'test' usa valor_test, 'produccion' usa valor
            $esTest = ($pasarela->modo ?? 'produccion') === 'test';
            $valor  = $esTest ? ($config->valor_test ?? '') : ($config->valor ?? '');

            return $valor !== '' ? $valor : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Indica de dónde se toma la configuración de Culqi.
     * Útil para depuración: "database" = tabla pago_pasarela_config, "env" = .env / config.
     *
     * @return 'database'|'env'
     */
    public static function getCulqiConfigSource(): string
    {
        $fromDb = self::getConfig('CULQI_PUBLIC_KEY');
        return ($fromDb !== null && $fromDb !== '') ? 'database' : 'env';
    }

    /**
     * Retorna un array con todas las configuraciones de la pasarela activa.
     * Solo incluye claves con valor definido.
     *
     * @return array<string, string>
     */
    public static function getAllConfigs(): array
    {
        try {
            $pasarela = self::getActivePasarela();

            if (!$pasarela) {
                return [];
            }

            $esTest = ($pasarela->modo ?? 'produccion') === 'test';

            $out = [];
            foreach ($pasarela->configs as $c) {
                $valor = $esTest ? ($c->valor_test ?? '') : ($c->valor ?? '');
                if ($valor !== null && $valor !== '') {
                    $out[$c->clave] = $valor;
                }
            }

            return $out;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * ¿La pasarela activa es Culqi?
     */
    public static function isCulqi(): bool
    {
        return self::getActiveCodigo() === 'culqi';
    }

    /**
     * ¿La pasarela activa es Izipay?
     */
    public static function isIzipay(): bool
    {
        return self::getActiveCodigo() === 'izipay';
    }

    /**
     * ¿La pasarela activa es Stripe?
     */
    public static function isStripe(): bool
    {
        return self::getActiveCodigo() === 'stripe';
    }

    // ─── Helpers específicos de Stripe ────────────────────────────────────────

    /**
     * Retorna la Public Key de Stripe de la BD.
     * Útil para pasarla al frontend sin instanciar el servicio completo.
     */
    public static function getStripePublishableKey(): ?string
    {
        return self::getConfig('STRIPE_PUBLIC_KEY');
    }

    /**
     * Indica si Stripe tiene sus credenciales mínimas configuradas en BD.
     */
    public static function isStripeConfigured(): bool
    {
        try {
            $pasarela = PagoPasarela::with('configs')->where('codigo', 'stripe')->first();
            if (!$pasarela) return false;

            $pub = $pasarela->configs->firstWhere('clave', 'STRIPE_PUBLIC_KEY');
            $sec = $pasarela->configs->firstWhere('clave', 'STRIPE_SECRET_KEY');

            return !empty($pub?->valor) && !empty($sec?->valor);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * ¿La pasarela activa es PayPal?
     */
    public static function isPaypal(): bool
    {
        return self::getActiveCodigo() === 'paypal';
    }

    /**
     * ¿Los pedidos se envían por WhatsApp (sin pago en línea)?
     */
    public static function isWhatsapp(): bool
    {
        return self::getActiveCodigo() === 'whatsapp';
    }

    /**
     * Número de WhatsApp de ventas para pedidos: el configurado en la card
     * de WhatsApp o, si está vacío, el número general del sitio.
     */
    public static function getWhatsappNumero(): string
    {
        $numero = ltrim((string) preg_replace('/\D/', '', (string) self::getConfig('WHATSAPP_NUMERO')), '0');

        return $numero !== '' ? $numero : \App\Support\ContactoValento::whatsappNumero();
    }
}

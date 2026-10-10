<?php

namespace App\Services;

use App\Exceptions\CulqiPaymentRejectedException;
use Culqi\Culqi;
use Illuminate\Support\Facades\Log;

/**
 * CulqiService
 *
 * Encapsula toda la lógica de integración con Culqi.
 */
class CulqiService
{
    protected string $publicKey;
    protected string $secretKey;
    protected string $rsaId;
    protected string $rsaPublicKey;

    /** @var array<string, string> */
    private const ERROR_MESSAGES = [
        'insufficient_funds'      => 'Fondos insuficientes en la tarjeta. Usa otra tarjeta o método de pago.',
        'incorrect_cvv'           => 'El código de seguridad (CVV) es incorrecto. Verifica los datos de tu tarjeta.',
        'lost_card'               => 'La tarjeta fue reportada como perdida. Contacta a tu banco.',
        'stolen_card'             => 'La tarjeta fue reportada como robada. Contacta a tu banco.',
        'contact_issuer'          => 'Tu banco rechazó la operación. Contacta a la entidad emisora de tu tarjeta.',
        'issuer_not_available'    => 'El emisor de la tarjeta no está disponible en este momento. Intenta más tarde.',
        'issuer_decline_operation'=> 'El banco rechazó la operación. Intenta con otra tarjeta.',
        'invalid_card'            => 'La tarjeta no es válida o está bloqueada.',
        'processing_error'        => 'Error al procesar el pago. Intenta nuevamente en unos minutos.',
        'expired_card'            => 'La tarjeta está vencida. Usa otra tarjeta.',
        'fraudulent'              => 'La transacción fue rechazada por seguridad. Contacta a tu banco.',
        'card_velocity_exceeded'  => 'Se excedió el límite de intentos de la tarjeta. Intenta más tarde.',
    ];

    public function __construct()
    {
        $this->publicKey    = PaymentGatewayManager::getConfig('CULQI_PUBLIC_KEY')
                           ?? config('services.culqi.public_key',    env('CULQI_PUBLIC_KEY',    ''));
        $this->secretKey    = PaymentGatewayManager::getConfig('CULQI_SECRET_KEY')
                           ?? config('services.culqi.secret_key',    env('CULQI_SECRET_KEY',    ''));
        $rawRsaId        = PaymentGatewayManager::getConfig('CULQI_RSA_ID')
                        ?? config('services.culqi.rsa_id',        env('CULQI_RSA_ID',        ''));
        $rawRsaPublicKey = PaymentGatewayManager::getConfig('CULQI_RSA_PUBLIC_KEY')
                        ?? config('services.culqi.rsa_public_key', env('CULQI_RSA_PUBLIC_KEY', ''));

        $this->rsaId        = is_string($rawRsaId) ? trim($rawRsaId) : '';
        $this->rsaPublicKey = self::normalizeRsaPublicKey($rawRsaPublicKey);
    }

    protected static function normalizeRsaPublicKey(?string $key): string
    {
        if ($key === null || $key === '') {
            return '';
        }
        $key = trim($key);
        $key = str_replace(["\r\n", "\\n", '\\n'], ["\n", "\n", "\n"], $key);

        return $key;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getRsaId(): string
    {
        return $this->rsaId;
    }

    public function getRsaPublicKey(): string
    {
        return $this->rsaPublicKey;
    }

    public function hasRsaConfigured(): bool
    {
        return $this->rsaId !== '' && $this->rsaPublicKey !== '';
    }

    public function generateOrderId(): string
    {
        $prefix = app()->environment('production') ? 'ord_live_' : 'ord_test_';

        return $prefix . time() . '_' . bin2hex(random_bytes(4));
    }

    /**
     * @param array{
     *   token: string,
     *   amount: int,
     *   email: string,
     *   codigo_pedido: string,
     *   metadata?: array,
     *   order_temp?: string
     * } $data
     */
    public function createCharge(array $data): array
    {
        $culqi = new Culqi([
            'api_key' => $this->secretKey,
        ]);

        $result = $culqi->Charges->create([
            'amount'        => $data['amount'],
            'currency_code' => 'PEN',
            'email'         => $data['email'],
            'source_id'     => $data['token'],
            'description'   => 'Compra de productos - Pedido #' . $data['codigo_pedido'],
            'capture'       => true,
            'metadata'      => array_merge($data['metadata'] ?? [], [
                'codigo_pedido' => $data['codigo_pedido'],
            ]),
        ]);

        // El SDK de Culqi devuelve errores como string JSON en lugar de lanzar excepción.
        if (is_string($result)) {
            $this->rejectFromPayload($result);
        }

        if (! is_object($result) || empty($result->id)) {
            Log::warning('CULQI_CHARGE_UNEXPECTED', ['result' => $result]);
            throw new CulqiPaymentRejectedException(
                'No se pudo procesar el pago. Verifica los datos de tu tarjeta e intenta de nuevo.',
                'unexpected_response'
            );
        }

        $outcomeType = $result->outcome->type ?? null;
        if ($outcomeType && $outcomeType !== 'venta_exitosa') {
            $this->rejectFromPayload(json_encode($result));
        }

        return [
            'metodo'         => 'culqi',
            'estado'         => 'completado',
            'transaccion_id' => $result->id,
            'fecha_pago'     => now(),
            'codigo_culqi'   => $data['order_temp'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>|string  $payload
     */
    public static function messageFromCulqiPayload(array|string $payload): array
    {
        $data = is_array($payload) ? $payload : json_decode($payload, true);

        if (! is_array($data)) {
            return [
                'code'              => 'unknown',
                'user_message'      => 'No se pudo procesar el pago. Intenta con otra tarjeta.',
                'merchant_message'  => is_string($payload) ? $payload : null,
            ];
        }

        $code = (string) (
            $data['decline_code']
            ?? $data['charge_decline_code']
            ?? $data['outcome']['type'] ?? null
            ?? $data['type']
            ?? 'unknown'
        );

        $merchant = $data['merchant_message']
            ?? $data['user_message']
            ?? ($data['outcome']['merchant_message'] ?? null)
            ?? ($data['outcome']['user_message'] ?? null);

        if ($code === 'parameter_error' && is_string($merchant) && stripos($merchant, 'token yape') !== false) {
            $user = 'El monto del pago no coincide con el autorizado en Yape. Cierra el modal, verifica el total (incluye envío) e intenta de nuevo.';
        } else {
            $user = self::ERROR_MESSAGES[$code]
                ?? $data['user_message']
                ?? $merchant
                ?? 'No se pudo procesar el pago. Verifica los datos de tu tarjeta.';
        }

        return [
            'code'             => $code,
            'user_message'     => $user,
            'merchant_message' => $merchant,
        ];
    }

    private function rejectFromPayload(array|string $payload): void
    {
        $parsed = self::messageFromCulqiPayload($payload);

        Log::info('CULQI_CHARGE_REJECTED', $parsed);

        throw new CulqiPaymentRejectedException(
            $parsed['user_message'],
            $parsed['code'],
            $parsed['merchant_message']
        );
    }
}

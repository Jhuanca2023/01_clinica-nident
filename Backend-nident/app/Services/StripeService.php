<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;

/**
 * StripeService
 * Encapsula toda la lógica de integración con Stripe.
 */
class StripeService
{
    protected string $publickey;
    protected string $secretKey;
    protected string $webhookSecret;

    public function __construct()
    {
        $this->publickey = PaymentGatewayManager::getConfig('STRIPE_PUBLIC_KEY')
                             ?? config('services.stripe.public_key', env('STRIPE_PUBLIC_KEY', ''));

        $this->secretKey      = PaymentGatewayManager::getConfig('STRIPE_SECRET_KEY')
                             ?? config('services.stripe.secret_key',      env('STRIPE_SECRET_KEY',      ''));

        $this->webhookSecret  = PaymentGatewayManager::getConfig('STRIPE_WEBHOOK_SECRET')
                             ?? config('services.stripe.webhook_secret',  env('STRIPE_WEBHOOK_SECRET',  ''));

        // Inicializar el SDK de Stripe con la secret key
        if ($this->secretKey !== '') {
            Stripe::setApiKey($this->secretKey);
        }
    }

    public function getPublicKey(): string
    {
        return $this->publickey;
    }

    public function isConfigured(): bool
    {
        return $this->publickey !== '' && $this->secretKey !== '';
    }

    // ─── Crear PaymentIntent ──────────────────────────────────────────────────

    /**
     * Crea un PaymentIntent en Stripe para iniciar el proceso de pago en monto en centimos.
     
     * @param array
     * @return array  
     */
    public function createPaymentIntent(array $data): array
    {
        $intent = PaymentIntent::create([
            'amount'               => $data['amount'],       // en céntimos
            'currency'             => 'pen',                 // en soles 
            'payment_method_types' => ['card'],
            'receipt_email'        => $data['email'],
            'description'          => 'Compra de productos - Pedido #' . $data['codigo_pedido'],
            'metadata'             => array_merge($data['metadata'] ?? [], [
                'codigo_pedido' => $data['codigo_pedido'],
                'email'         => $data['email'],
            ]),
        ]);

        return [
            'client_secret'     => $intent->client_secret,
            'payment_intent_id' => $intent->id,
            'public_key'   => $this->publickey,
        ];
    }
    
    public function confirmPayment(string $paymentIntentId): array
    {
        $intent = PaymentIntent::retrieve($paymentIntentId);

        if ($intent->status !== 'succeeded') {
            throw new \Exception("El pago no fue completado. Estado: {$intent->status}");
        }

        return [
            'metodo'            => 'stripe',
            'estado'            => 'completado',
            'transaccion_id'    => $intent->id,
            'fecha_pago'        => now(),
            'codigo_culqi'      => null, 
        ];
    }

    // ─── Webhook ──────────────────────────────────────────────────────────────

    /**
     * Verifica y procesa el evento de webhook enviado por Stripe.
     * Stripe firma cada petición con el Webhook Secret para evitar fraudes.
     *
     * @param string $payload    Cuerpo raw de la petición HTTP
     * @param string $signature  Header 'Stripe-Signature'
     * @return array  ['success' => bool, 'event_type' => string]
     */
    public function handleWebhook(string $payload, string $signature): array
    {
        if ($this->webhookSecret === '') {
            // Sin webhook secret configurado, aceptar pero sin verificar firma
            $event = \Stripe\Event::constructFrom(json_decode($payload, true));
        } else {
            $event = Webhook::constructEvent($payload, $signature, $this->webhookSecret);
        }

        $eventType = $event->type;
        $object    = $event->data->object;

        match ($eventType) {
            'payment_intent.succeeded'      => $this->onPaymentSucceeded($object),
            'payment_intent.payment_failed' => $this->onPaymentFailed($object),
            default                         => null,
        };

        return [
            'success'    => true,
            'event_type' => $eventType,
        ];
    }

    // ─── Eventos internos de webhook ──────────────────────────────────────────

    protected function onPaymentSucceeded(object $paymentIntent): void
    {
        // Aquí puedes buscar el pedido por metadata y marcarlo como pagado
        // $codigoPedido = $paymentIntent->metadata->codigo_pedido ?? null;
        // Pedido::where('codigo', $codigoPedido)->update(['estado_pago' => 'completado']);
        \Illuminate\Support\Facades\Log::info('Stripe pago exitoso', [
            'payment_intent_id' => $paymentIntent->id,
            'amount'            => $paymentIntent->amount,
            'metadata'          => (array) $paymentIntent->metadata,
        ]);
    }

    protected function onPaymentFailed(object $paymentIntent): void
    {
        \Illuminate\Support\Facades\Log::warning('Stripe pago fallido', [
            'payment_intent_id' => $paymentIntent->id,
            'last_error'        => $paymentIntent->last_payment_error?->message ?? 'Sin detalle',
        ]);
    }
}

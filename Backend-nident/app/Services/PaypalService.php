<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * PaypalService
 *
 * Encapsula la lógica para interactuar con la API REST de PayPal (v2).
 * Permite crear órdenes y capturarlas.
 */
class PaypalService
{
    protected string $clientId;
    protected string $secret;
    protected string $mode;
    protected bool $sslValidate;
    protected float $exchangeRate;

    public function __construct()
    {
        // ── Leer credenciales: DB (admin panel) → config/services → .env ──
        $this->clientId = PaymentGatewayManager::getConfig('PAYPAL_CLIENT_ID')
            ?? env('PAYPAL_CLIENT_ID', '');
        $this->secret = PaymentGatewayManager::getConfig('PAYPAL_SECRET')
            ?? env('PAYPAL_SECRET', '');
        $this->mode = PaymentGatewayManager::getConfig('PAYPAL_MODE')
            ?? env('PAYPAL_MODE', 'sandbox');
        $this->exchangeRate = (float) (PaymentGatewayManager::getConfig('PAYPAL_EXCHANGE_RATE')
            ?? env('PAYPAL_EXCHANGE_RATE', 1.0));

        // El SSL en false para desarrollo
        $sslFromDb = PaymentGatewayManager::getConfig('PAYPAL_SSL_VALIDATE');
        if ($sslFromDb !== null) {
            $this->sslValidate = (bool) $sslFromDb;
        } else {
            $this->sslValidate = (bool) env('PAYPAL_SSL_VALIDATE', false);
        }
    }

    /**
     * Obtiene el URL base de la API según el modo.
     */
    protected function getBaseUri(): string
    {
        return $this->mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Obtiene el Access Token de PayPal (OAuth2).
     */
    protected function getAccessToken(): string
    {
        $client = new Client([
            'verify' => $this->sslValidate,
        ]);

        $response = $client->post($this->getBaseUri() . '/v1/oauth2/token', [
            'auth' => [$this->clientId, $this->secret],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['access_token'];
    }

    /**
     * Crea una orden de pago en PayPal.
     * Convierte el monto de PEN a USD usando la tasa de cambio configurada.
     */
    public function createOrder(float $amountPen, string $codigoPedido): array
    {
        $accessToken = $this->getAccessToken();
        $amountUsd = round($amountPen / $this->exchangeRate, 2);

        $client = new Client([
            'verify' => $this->sslValidate,
        ]);

        $response = $client->post($this->getBaseUri() . '/v2/checkout/orders', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $codigoPedido,
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => (string) $amountUsd,
                        ],
                    ],
                ],
                'application_context' => [
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                ],
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Captura una orden de PayPal aprobada por el cliente.
     */
    public function captureOrder(string $paypalOrderId): array
    {
        try {
            $accessToken = $this->getAccessToken();

            $client = new Client([
                'verify' => $this->sslValidate,
            ]);

            $response = $client->post($this->getBaseUri() . "/v2/checkout/orders/{$paypalOrderId}/capture", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (($result['status'] ?? '') === 'COMPLETED') {
                return [
                    'metodo' => 'paypal',
                    'estado' => 'completado',
                    'transaccion_id' => $result['id'],
                    'fecha_pago' => now(),
                ];
            }

            throw new \Exception('El pago de PayPal no fue completado. Estado: ' . ($result['status'] ?? 'UNKNOWN'));
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            Log::error("PAYPAL_CAPTURE_CLIENT_ERROR", ['body' => $responseBody]);
            $errorData = json_decode($responseBody, true);
            $issue = $errorData['details'][0]['issue'] ?? 'UNKNOWN_ISSUE';
            $description = $errorData['details'][0]['description'] ?? 'No description provided';
            
            if ($issue === 'INSTRUMENT_DECLINED') {
                throw new \Exception('Su método de pago fue rechazado por PayPal. Por favor, intente con otra tarjeta o saldo.');
            }
            
            throw new \Exception("Error de PayPal ($issue): $description");
        } catch (\Exception $e) {
            Log::error("PAYPAL_CAPTURE_GENERAL_ERROR", ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getExchangeRate(): float
    {
        return $this->exchangeRate;
    }
}

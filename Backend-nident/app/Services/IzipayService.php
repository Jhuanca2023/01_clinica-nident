<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * IzipayService
 *
 * Lee credenciales desde la tabla pago_pasarela_config (DB) a través
 * de PaymentGatewayManager. Si no existen en DB, hace fallback al .env.
 */
class IzipayService
{
    protected string $apiUrl;
    protected string $username;
    protected string $password;
    protected string $publicKey;
    protected string $sha256Key;

    public function __construct()
    {
        // ── Leer credenciales desde DB (admin panel); fallback a .env ─────
        $this->username  = PaymentGatewayManager::getConfig('IZIPAY_USERNAME')   ?? env('IZIPAY_USERNAME',   '');
        $this->password  = PaymentGatewayManager::getConfig('IZIPAY_PASSWORD')   ?? env('IZIPAY_PASSWORD',   '');
        $this->publicKey = PaymentGatewayManager::getConfig('IZIPAY_PUBLIC_KEY') ?? env('IZIPAY_PUBLIC_KEY', '');
        $this->sha256Key = PaymentGatewayManager::getConfig('IZIPAY_SHA256_KEY') ?? env('IZIPAY_SHA256_KEY', '');

        // Mismo endpoint para sandbox y producción en micuentaweb.pe
        $this->apiUrl = 'https://api.micuentaweb.pe';
    }

    /**
     * Crea un formToken para el checkout embebido (Pop-In / Krypton.js).
     *
     * @param array{
     *   amount:   int,
     *   currency: string,
     *   order_id: string,
     *   email:    string,
     *   user_id?: int|null
     * } $data
     * @return string formToken
     */
    public function createPayment(array $data): string
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->post("{$this->apiUrl}/api-payment/V4/Charge/CreatePayment", [
                // Payload alineado con el ejemplo oficial de Izipay
                // https://github.com/izipay-pe/Embedded-PaymentForm-PHP y su README.
                // Solo se envían los campos mínimos necesarios para generar el formToken;
                // los datos de tarjeta se capturan luego desde Krypton.js en el navegador.
                'amount'   => $data['amount'],
                'currency' => $data['currency'] ?? 'PEN',
                'orderId'  => $data['order_id'],
                'customer' => [
                    'email' => $data['email'],
                ],
            ]);

        if ($response->successful()) {
            $body = $response->json();
            if (($body['status'] ?? '') === 'SUCCESS' && isset($body['answer']['formToken'])) {
                return $body['answer']['formToken'];
            }
            throw new \Exception('Izipay: respuesta inesperada → ' . json_encode($body));
        }

        throw new \Exception('Izipay: error HTTP ' . $response->status() . ' → ' . $response->body());
    }

    /**
     * Valida la firma HMAC-SHA256 que envía Krypton.js al resultado de pago.
     *
     * @param string $krAnswer        Contenido del campo kr-answer (JSON string)
     * @param string $krHash          Contenido del campo kr-hash
     * @param string $krHashAlgorithm Contenido del campo kr-hash-algorithm
     */
    public function validarFirma(string $krAnswer, string $krHash, string $krHashAlgorithm = 'sha256_hmac'): bool
    {
        $algo = strtolower(str_replace('-', '_', $krHashAlgorithm));

        if ($algo !== 'sha256_hmac') {
            throw new \Exception("Algoritmo de firma no soportado: {$krHashAlgorithm}");
        }

        $firma = hash_hmac('sha256', $krAnswer, $this->sha256Key);

        return hash_equals($firma, $krHash);
    }

    /**
     * Public Key para inicializar el SDK Krypton.js en el frontend.
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }
}

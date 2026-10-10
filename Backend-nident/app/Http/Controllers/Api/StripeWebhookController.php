<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * StripeWebhookController
 * Recibe notificaciones automáticas de Stripe (payment_intent.succeeded, etc.)
 * IMPORTANTE: Esta ruta debe estar EXCLUIDA del middleware CSRF y de auth.
 */
class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');

        try {
            $stripe = new StripeService();
            $result = $stripe->handleWebhook($payload, $signature);

            return response('OK', 200);
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook payload inválido', ['error' => $e->getMessage()]);
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook firma inválida', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error', ['error' => $e->getMessage()]);
            return response('Error', 500);
        }
    }
}

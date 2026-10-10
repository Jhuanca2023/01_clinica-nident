<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PagoPasarela;
use App\Models\PagoPasarelaConfig;
use App\Services\StripeService;
use App\Support\PagoPasarelaSchema;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoPasarelaController extends Controller
{
    /**
     * Lista todas las pasarelas con sus configuraciones.
     * Los valores de claves secretas se devuelven enmascarados (••••••••)
     * para no exponer credenciales en el frontend.
     */
    /**
     * @OA\Get(
     *     path="/pasarela/listar",
     *     tags={"Pasarelas de Pago"},
     *     summary="Listar todas las pasarelas con sus configuraciones",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de pasarelas", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar(): JsonResponse
    {
        try {
            PagoPasarelaSchema::ensureModoColumns();
        } catch (\Throwable $e) {
            // Si no se pueden crear aquí, cambiarModo / actualizarConfig devolverán el error claro
        }

        $pasarelas = PagoPasarela::with('configs')
            ->orderBy('orden')
            ->get()
            ->map(function ($p) {
                return [
                    'id_pago_pasarela' => $p->id_pago_pasarela,
                    'codigo'           => $p->codigo,
                    'nombre'           => $p->nombre,
                    'descripcion'      => $p->descripcion,
                    'logo_url'         => $p->logo_url,
                    'is_activo'        => $p->is_activo,
                    'modo'             => $p->modo ?? 'produccion',
                    'orden'            => $p->orden,
                    'configs'          => $p->configs->map(fn ($c) => [
                        'id_pago_pasarela_config' => $c->id_pago_pasarela_config,
                        'clave'      => $c->clave,
                        'etiqueta'   => $c->etiqueta,
                        'valor'      => ($c->es_secreto && $c->valor) ? '••••••••' : ($c->valor ?? ''),
                        'valor_test' => ($c->es_secreto && $c->valor_test) ? '••••••••' : ($c->valor_test ?? ''),
                        'es_secreto' => (bool) $c->es_secreto,
                    ])->values(),
                ];
            });

        return response()->json([
            'success' => true,
            'result'  => $pasarelas,
        ]);
    }

    /**
     * Obtiene la pasarela actualmente activa (sin enmascarar, solo datos básicos).
     * Útil para el checkout frontend para saber cuál pasarela usar.
     */
    /**
     * @OA\Get(
     *     path="/pasarela/activa",
     *     tags={"Pasarelas de Pago"},
     *     summary="Obtener la pasarela de pago actualmente activa",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Pasarela activa obtenida", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function obtenerActiva(): JsonResponse
    {
        $pasarela = PagoPasarela::where('is_activo', 1)->first();

        return response()->json([
            'success' => true,
            'result'  => $pasarela
                ? ['id_pago_pasarela' => $pasarela->id_pago_pasarela, 'codigo' => $pasarela->codigo, 'nombre' => $pasarela->nombre]
                : null,
        ]);
    }

    /**
     * Activa una pasarela y desactiva automáticamente las demás.
     * Solo puede haber UNA pasarela activa a la vez.
     */
    /**
     * @OA\Post(
     *     path="/pasarela/activar",
     *     tags={"Pasarelas de Pago"},
     *     summary="Activar/desactivar una pasarela de pago específica",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_pago_pasarela"},
     *         @OA\Property(property="id_pago_pasarela", type="integer", example=1),
     *         @OA\Property(property="is_activo", type="boolean", example=true)
     *     )),
     *     @OA\Response(response=200, description="Estado de pasarela actualizado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function activar(Request $request): JsonResponse
    {
        $request->validate([
            'id_pago_pasarela' => 'required|integer|exists:pago_pasarela,id_pago_pasarela',
            'is_activo'        => 'nullable|boolean',
        ]);

        $pasarela = PagoPasarela::find($request->id_pago_pasarela);
        
        // Si el request trae is_activo explícitamente, usarlo. Si no, alternar el estado actual.
        $deseaActivar = $request->has('is_activo') ? (bool)$request->is_activo : !$pasarela->is_activo;

        DB::transaction(function () use ($pasarela, $deseaActivar) {
            if ($deseaActivar) {
                // EXCLUSIVIDAD: Al activar una, desactivamos automáticamente todas las demás
                PagoPasarela::query()->update(['is_activo' => 0]);
                $pasarela->update(['is_activo' => 1]);
            } else {
                // Si la acción es desactivar, simplemente lo apagamos
                $pasarela->update(['is_activo' => 0]);
            }
        });

        $pasarela->refresh();
        $statusText = $pasarela->is_activo ? 'activada' : 'desactivada';

        return response()->json([
            'success' => true,
            'message' => "Pasarela '{$pasarela->nombre}' {$statusText} correctamente. " . ($pasarela->is_activo ? "Las demás han sido desactivadas automáticamente." : ""),
            'result'  => $pasarela,
        ]);
    }

    /**
     * Actualiza las claves de configuración de una pasarela.
     * Si el valor enviado es el placeholder '••••••••', ese campo se omite
     * para no sobreescribir el valor real almacenado.
     */
    /**
     * @OA\Post(
     *     path="/pasarela/actualizar-config",
     *     tags={"Pasarelas de Pago"},
     *     summary="Actualizar configuraciones de una pasarela",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_pago_pasarela","configs"},
     *         @OA\Property(property="id_pago_pasarela", type="integer", example=1),
     *         @OA\Property(property="configs", type="array", @OA\Items(
     *             @OA\Property(property="clave", type="string", example="STRIPE_PUBLIC_KEY"),
     *             @OA\Property(property="valor", type="string", example="pk_test_xxxx")
     *         ))
     *     )),
     *     @OA\Response(response=200, description="Configuración actualizada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizarConfig(Request $request): JsonResponse
    {
        $request->validate([
            'id_pago_pasarela'       => 'required|integer|exists:pago_pasarela,id_pago_pasarela',
            'modo'                   => 'nullable|in:test,produccion',
            'configs'                => 'required|array|min:1',
            'configs.*.clave'        => 'required|string|max:100',
            'configs.*.valor'        => 'nullable|string',
        ]);

        if ($error = $this->ensurePasarelaSchema()) {
            return $error;
        }

        $esTest  = $request->input('modo') === 'test';
        $columna = $esTest ? 'valor_test' : 'valor';

        try {
            DB::transaction(function () use ($request, $columna) {
                foreach ($request->configs as $cfg) {
                    $valor = $cfg['valor'] ?? null;

                    // Placeholder de enmascarado: no sobreescribir el secreto real
                    if ($valor === '••••••••') {
                        continue;
                    }

                    $row = PagoPasarelaConfig::where('id_pago_pasarela', $request->id_pago_pasarela)
                        ->where('clave', $cfg['clave'])
                        ->first();

                    if (! $row) {
                        continue;
                    }

                    // Campo secreto vacío: conservar el valor ya guardado
                    if ($row->es_secreto && ($valor === null || $valor === '')) {
                        continue;
                    }

                    $row->update([$columna => $valor]);
                }
            });
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => $esTest
                    ? 'No se pudieron guardar las claves de PRUEBA. Falta la columna valor_test. Abra artisan-clear.php?key=lucdesoft2026 y vuelva a guardar.'
                    : 'No se pudieron guardar las claves de PRODUCCIÓN: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => $esTest
                ? 'Claves de PRUEBA actualizadas correctamente.'
                : 'Claves de PRODUCCIÓN actualizadas correctamente.',
            'result'  => ['modo' => $esTest ? 'test' : 'produccion'],
        ]);
    }

    /**
     * Cambia el modo activo (test / produccion) de una pasarela.
     * El checkout usará las claves del modo seleccionado.
     */
    public function cambiarModo(Request $request): JsonResponse
    {
        $request->validate([
            'id_pago_pasarela' => 'required|integer|exists:pago_pasarela,id_pago_pasarela',
            'modo'             => 'required|in:test,produccion',
        ]);

        if ($error = $this->ensurePasarelaSchema()) {
            return $error;
        }

        $pasarela = PagoPasarela::find($request->id_pago_pasarela);

        try {
            $pasarela->update(['modo' => $request->modo]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo cambiar el modo. Falta la columna pago_pasarela.modo. Abra artisan-clear.php?key=lucdesoft2026 y reintente.',
            ], 500);
        }

        $label = $request->modo === 'test' ? 'PRUEBA (test)' : 'PRODUCCIÓN';

        return response()->json([
            'success' => true,
            'message' => "Pasarela '{$pasarela->nombre}' cambiada a modo {$label}.",
            'result'  => ['id_pago_pasarela' => $pasarela->id_pago_pasarela, 'modo' => $pasarela->modo],
        ]);
    }

    /**
     * Crea modo / valor_test si faltan (cPanel suele marcar la migración como ran
     * sin crear las columnas). Devuelve JsonResponse de error o null si OK.
     */
    private function ensurePasarelaSchema(): ?JsonResponse
    {
        try {
            PagoPasarelaSchema::ensureModoColumns();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudieron crear las columnas de prueba/producción. Ejecute artisan-clear.php?key=lucdesoft2026. ' . $e->getMessage(),
            ], 500);
        }

        $estado = PagoPasarelaSchema::estado();
        if (empty($estado['modo']) || empty($estado['valor_test'])) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan columnas en BD (pago_pasarela.modo y/o pago_pasarela_config.valor_test). Abra artisan-clear.php?key=lucdesoft2026 y vuelva a guardar las claves de PRUEBA.',
            ], 500);
        }

        return null;
    }
    // ─── STRIPE: Crear PaymentIntent ──────────────────────────────────────────

    /**
     * @OA\Post(
     *     path="/pasarela/stripe/payment-intent",
     *     tags={"Pasarelas de Pago"},
     *     summary="Crear un PaymentIntent de Stripe",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"amount","email","codigo_pedido"},
     *         @OA\Property(property="amount", type="integer", description="Monto en céntimos (ej: 1000 = S/ 10.00)", example=1000),
     *         @OA\Property(property="email", type="string", format="email"),
     *         @OA\Property(property="codigo_pedido", type="string", example="PED-0001")
     *     )),
     *     @OA\Response(response=200, description="PaymentIntent creado con éxito", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=422, description="Error de configuración o validación")
     * )
     */
    public function stripeCrearPaymentIntent(Request $request): JsonResponse
    {
        $request->validate([
            'amount'   => 'required|integer|min:100', 
            'email'         => 'required|email',
            'codigo_pedido' => 'required|string',
        ]);

        try {
            $stripe = new StripeService();

            if (!$stripe->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stripe no está configurado. Ingresa las claves en el panel admin.',
                ], 422);
            }

            $result = $stripe->createPaymentIntent([
                'amount'        => $request->amount,
                'email'         => $request->email,
                'codigo_pedido' => $request->codigo_pedido,
                'metadata'      => $request->metadata ?? [],
            ]);

            return response()->json([
                'success' => true,
                'result'  => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/pasarela/stripe/confirmar",
     *     tags={"Pasarelas de Pago"},
     *     summary="Confirmar un pago de Stripe",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"payment_intent_id"},
     *         @OA\Property(property="payment_intent_id", type="string", example="pi_123456789")
     *     )),
     *     @OA\Response(response=200, description="Pago confirmado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=422, description="Error al confirmar el pago")
     * )
     */
    public function stripeConfirmarPago(Request $request): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => 'required|string|starts_with:pi_',
        ]);

        try {
            $stripe = new StripeService();
            $result = $stripe->confirmPayment($request->payment_intent_id);

            return response()->json([
                'success' => true,
                'result'  => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}



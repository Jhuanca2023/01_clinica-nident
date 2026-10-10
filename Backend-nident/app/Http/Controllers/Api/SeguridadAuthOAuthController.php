<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthOAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeguridadAuthOAuthController extends Controller
{
    public function __construct(
        private readonly AuthOAuthService $oauthService
    ) {
    }

    /** Inicia OAuth: devuelve URL de autorización (SPA abre ventana o redirige). */
    public function redirect(string $proveedor, Request $request): JsonResponse
    {
        try {
            $hint = $request->query('login_hint') ?: $request->input('login_hint');
            $data = $this->oauthService->iniciar($proveedor, $request->ip(), $hint ? (string) $hint : null);
            return response()->json(['success' => true, 'result' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** Callback del proveedor (navegador). Redirige al frontend con código de intercambio. */
    public function callback(string $proveedor, Request $request)
    {
        if ($request->has('error')) {
            $msg = $request->input('error_description', $request->input('error', 'OAuth cancelado'));
            return redirect(rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/') . '/session/oauth-callback?error=' . urlencode($msg));
        }

        $request->validate([
            'code' => 'required|string',
            'state' => 'required|string',
        ]);

        try {
            $url = $this->oauthService->callback($proveedor, $request->input('code'), $request->input('state'), $request->ip());
            return redirect($url);
        } catch (\Throwable $e) {
            return redirect(rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/') . '/session/oauth-callback?error=' . urlencode($e->getMessage()));
        }
    }

    /** SPA intercambia código de un solo uso por JWT (mismo contrato que /login). */
    public function exchange(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|min:20']);

        try {
            $payload = $this->oauthService->intercambiarCodigo(
                $request->input('code'),
                $request->ip(),
                $request->input('trusted_device_token')
            );
            return response()->json($payload);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 422,
            ], 422);
        }
    }
}

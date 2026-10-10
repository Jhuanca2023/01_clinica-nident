<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthTwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeguridadAuth2faController extends Controller
{
    public function __construct(
        private readonly AuthTwoFactorService $twoFactor
    ) {
    }

    /** Inicia QR para 2FA obligatorio por rol (sin JWT). */
    public function mandatoryInit(Request $request): JsonResponse
    {
        $request->validate(['setup_token' => 'required|string|min:32']);

        try {
            $data = $this->twoFactor->iniciarSetupObligatorio($request->input('setup_token'));

            return response()->json(['success' => true, 'result' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** Confirma 2FA obligatorio y devuelve JWT (sin sesión previa). */
    public function mandatoryConfirm(Request $request): JsonResponse
    {
        $request->validate([
            'setup_token' => 'required|string|min:32',
            'code' => 'required|string|size:6',
        ]);

        try {
            $payload = $this->twoFactor->confirmarSetupObligatorio(
                $request->input('setup_token'),
                $request->input('code'),
                $request->ip()
            );

            return response()->json($payload);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 422,
            ], 422);
        }
    }

    /** Challenge post-login (sin JWT). */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'challenge_token' => 'required|string|min:32',
            'code' => 'required|string|min:6|max:20',
            'trust_device' => 'nullable|boolean',
        ]);

        try {
            $payload = $this->twoFactor->verificarChallenge(
                $request->input('challenge_token'),
                $request->input('code'),
                $request->ip(),
                $request->boolean('trust_device')
            );

            return response()->json($payload);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 422,
            ], 422);
        }
    }

    public function status(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'result' => $this->twoFactor->estado($request->user()),
        ]);
    }

    public function setup(Request $request): JsonResponse
    {
        if (!app(\App\Services\Seguridad\UsuarioAuthConfigService::class)->puedeConfigurar2faEnPerfil($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Su cuenta no tiene permitido configurar 2FA desde el perfil. Contacte al administrador.',
            ], 403);
        }

        try {
            $data = $this->twoFactor->iniciarSetup($request->user());

            return response()->json(['success' => true, 'result' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function confirm(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|size:6']);

        try {
            $data = $this->twoFactor->confirmarSetup($request->user(), $request->input('code'));

            return response()->json(['success' => true, 'result' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function disable(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|min:6|max:20']);

        try {
            $this->twoFactor->deshabilitar($request->user(), $request->input('code'));

            return response()->json(['success' => true, 'message' => 'Verificación en dos pasos desactivada.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function regenerateRecovery(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|size:6']);

        try {
            $data = $this->twoFactor->regenerarRecoveryCodes($request->user(), $request->input('code'));

            return response()->json(['success' => true, 'result' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthTwoFactorService;
use App\Services\Seguridad\UsuarioAuthConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class SeguridadUsuarioAuthConfigController extends Controller
{
    public function __construct(
        private readonly UsuarioAuthConfigService $configService,
        private readonly AuthTwoFactorService $twoFactorService
    ) {
    }

    public function obtener(Request $request): JsonResponse
    {
        $request->validate(['id_usuario' => 'required|integer|exists:users,id']);
        $idUsuario = (int) $request->id_usuario;

        return response()->json([
            'success' => true,
            'result' => array_merge(
                $this->configService->obtener($idUsuario),
                ['two_factor' => $this->twoFactorService->resumenAdministrador($idUsuario)]
            ),
        ]);
    }

    public function resetear2fa(Request $request): JsonResponse
    {
        $request->validate(['id_usuario' => 'required|integer|exists:users,id']);

        try {
            $admin = JWTAuth::parseToken()->authenticate();
            $result = $this->twoFactorService->resetearPorAdministrador(
                (int) $request->id_usuario,
                $admin ? (int) $admin->id : null
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'result' => array_merge(
                    $this->configService->obtener((int) $request->id_usuario),
                    [
                        'two_factor' => $this->twoFactorService->resumenAdministrador((int) $request->id_usuario),
                        'debe_reconfigurar_en_login' => $result['debe_reconfigurar_en_login'],
                    ]
                ),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function guardar(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario' => 'required|integer|exists:users,id',
            'permitir_local' => 'nullable|boolean',
            'permitir_google' => 'nullable|boolean',
            'permitir_microsoft' => 'nullable|boolean',
            'requiere_2fa' => 'nullable|boolean',
            'permite_2fa_voluntario' => 'nullable|boolean',
            'metodo_predeterminado' => 'nullable|string|in:local,google,microsoft',
        ]);

        try {
            $result = $this->configService->guardar((int) $request->id_usuario, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Configuración de acceso guardada.',
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** Público: métodos permitidos según email (antes del login). */
    public function metodosPorEmail(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        return response()->json([
            'success' => true,
            'result' => $this->configService->metodosLoginPorEmail($request->email),
        ]);
    }
}

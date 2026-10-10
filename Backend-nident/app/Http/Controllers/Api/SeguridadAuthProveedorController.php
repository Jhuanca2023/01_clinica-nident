<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SeguridadAuthProveedor;
use App\Models\SeguridadAuthProveedorConfig;
use App\Services\Auth\AuthProveedorConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeguridadAuthProveedorController extends Controller
{
    public function __construct(
        private readonly AuthProveedorConfigService $configService
    ) {
    }

    public function listar(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'result' => $this->configService->listarParaAdmin(),
        ]);
    }

    public function metodosLogin(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'result' => $this->configService->metodosLoginPublicos(),
        ]);
    }

    public function habilitar(Request $request): JsonResponse
    {
        $request->validate([
            'id_seguridad_auth_proveedor' => 'required|integer|exists:seguridad_auth_proveedor,id_seguridad_auth_proveedor',
            'is_habilitado' => 'nullable|boolean',
        ]);

        $prov = SeguridadAuthProveedor::find($request->id_seguridad_auth_proveedor);
        $habilitar = $request->has('is_habilitado') ? (bool) $request->is_habilitado : !$prov->is_habilitado;

        if (!$habilitar && $prov->is_habilitado) {
            $otrosActivos = SeguridadAuthProveedor::where('is_habilitado', true)
                ->where('id_seguridad_auth_proveedor', '!=', $prov->id_seguridad_auth_proveedor)
                ->exists();
            if (!$otrosActivos) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe haber al menos un método de login activo.',
                ], 422);
            }
        }

        DB::transaction(function () use ($prov, $habilitar) {
            if ($habilitar) {
                SeguridadAuthProveedor::query()->update(['is_habilitado' => false, 'is_predeterminado' => false]);
                $prov->update(['is_habilitado' => true, 'is_predeterminado' => true]);
            } else {
                $eraPredeterminado = (bool) $prov->is_predeterminado;
                $prov->update(['is_habilitado' => false, 'is_predeterminado' => false]);
                if ($eraPredeterminado) {
                    $siguiente = SeguridadAuthProveedor::where('is_habilitado', true)
                        ->orderBy('orden')
                        ->first();
                    if ($siguiente) {
                        $siguiente->update(['is_predeterminado' => true]);
                    }
                }
            }
        });

        $prov->refresh();
        $extra = $habilitar ? ' Los demás métodos se desactivaron automáticamente.' : '';

        return response()->json([
            'success' => true,
            'message' => ($habilitar ? 'Proveedor habilitado para login.' : 'Proveedor deshabilitado.') . $extra,
            'result' => $prov,
        ]);
    }

    public function predeterminado(Request $request): JsonResponse
    {
        $request->validate([
            'id_seguridad_auth_proveedor' => 'required|integer|exists:seguridad_auth_proveedor,id_seguridad_auth_proveedor',
        ]);

        DB::transaction(function () use ($request) {
            SeguridadAuthProveedor::query()->update(['is_habilitado' => false, 'is_predeterminado' => false]);
            SeguridadAuthProveedor::where('id_seguridad_auth_proveedor', $request->id_seguridad_auth_proveedor)
                ->update(['is_predeterminado' => true, 'is_habilitado' => true]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Proveedor predeterminado actualizado. Los demás métodos se desactivaron automáticamente.',
        ]);
    }

    public function actualizarConfig(Request $request): JsonResponse
    {
        $request->validate([
            'id_seguridad_auth_proveedor' => 'required|integer|exists:seguridad_auth_proveedor,id_seguridad_auth_proveedor',
            'configs' => 'required|array|min:1',
            'configs.*.clave' => 'required|string|max:100',
            'configs.*.valor' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->configs as $cfg) {
                if (isset($cfg['valor']) && $cfg['valor'] === AuthProveedorConfigService::MASK) {
                    continue;
                }
                $row = SeguridadAuthProveedorConfig::where('id_seguridad_auth_proveedor', $request->id_seguridad_auth_proveedor)
                    ->where('clave', $cfg['clave'])
                    ->first();
                if (!$row) {
                    continue;
                }
                $valor = SeguridadAuthProveedorConfig::guardarValor((bool) $row->es_secreto, $cfg['valor'] ?? null);
                $row->update(['valor' => $valor]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Configuración de autenticación actualizada.',
        ]);
    }
}

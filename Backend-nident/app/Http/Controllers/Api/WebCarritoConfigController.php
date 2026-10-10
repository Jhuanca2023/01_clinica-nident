<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebCarritoConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebCarritoConfigController extends Controller
{
    /** Campos editables desde el administrador. */
    private const CAMPOS = [
        'resumen_titulo', 'label_subtotal', 'label_igv', 'igv_porcentaje',
        'label_descuento', 'label_total',
        'umbral_envio_gratis', 'envio_gratis_texto', 'envio_falta_texto',
        'nota_titulo', 'nota_texto', 'nota_activa',
        'btn_continuar_texto', 'btn_seguir_texto',
        'asesor_activo', 'asesor_label', 'asesor_titulo', 'asesor_subtitulo', 'asesor_mensaje',
        'Activo',
    ];

    public function obtener(): JsonResponse
    {
        $cfg = WebCarritoConfig::obtenerConfig();

        return response()->json(['success' => true, 'result' => $cfg]);
    }

    public function actualizar(Request $request): JsonResponse
    {
        $cfg = WebCarritoConfig::obtenerConfig();
        $cfg->update($request->only(self::CAMPOS));

        return response()->json(['success' => true, 'result' => $cfg->fresh()]);
    }
}

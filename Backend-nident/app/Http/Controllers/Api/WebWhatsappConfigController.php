<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\WebWhatsappConfig;
use App\Support\ContactoValento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebWhatsappConfigController extends Controller
{
    public function obtener(): JsonResponse
    {
        $cfg = WebWhatsappConfig::firstOrCreate(
            ['id' => 1],
            [
                'wa_numero'         => ContactoValento::WA_NUMERO,
                'wa_mensaje'        => '¡Hola Valento! Me gustaría recibir más información sobre sus productos.',
                'wa_burbuja_linea1' => '👕 ¿Buscas polos de calidad?',
                'wa_burbuja_linea2' => '¡Escríbenos y te asesoramos!',
                'wa_label'          => 'WhatsApp',
                'wa_badge'          => '1',
                'wa_delay_segundos' => 2.5,
                'Activo'            => 'S',
            ]
        );
        return response()->json(['success' => true, 'result' => $cfg]);
    }

    public function actualizar(Request $request): JsonResponse
    {
        $cfg = WebWhatsappConfig::firstOrCreate(['id' => 1]);
        $cfg->update($request->only([
            'wa_numero', 'wa_mensaje',
            'wa_burbuja_linea1', 'wa_burbuja_linea2',
            'wa_label', 'wa_badge', 'wa_delay_segundos', 'Activo',
            // Tarjeta de asesoría del detalle de producto
            'card_activo', 'card_nombre', 'card_rol', 'card_estado',
            'card_titulo', 'card_texto', 'card_boton', 'card_mensaje', 'card_respuesta',
        ]));
        return response()->json(['success' => true, 'result' => $cfg->fresh()]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebContactoMensaje;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebContactoMensajeController extends Controller
{
    public function listar(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar mensajes',
            'result'  => WebContactoMensaje::orderByDesc('created_at')->get(),
        ]);
    }

    public function obtener(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|exists:web_contacto_mensaje,id']);

        return response()->json([
            'success' => true,
            'message' => 'Obtener mensaje',
            'result'  => WebContactoMensaje::findOrFail($request->id),
        ]);
    }

    public function eliminar(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|exists:web_contacto_mensaje,id']);

        $mensaje = WebContactoMensaje::findOrFail($request->id);
        $mensaje->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mensaje eliminado correctamente.',
        ]);
    }
}

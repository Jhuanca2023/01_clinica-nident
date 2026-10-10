<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebCuentaPago;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebCuentaPagoController extends Controller
{
    public function listar(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar cuentas',
            'result' => WebCuentaPago::query()->orderBy('orden')->get(),
        ]);
    }

    public function crear(Request $request): JsonResponse
    {
        $data = $request->validate([
            'banco' => 'required|string|max:40',
            'tipo' => 'nullable|string|max:80',
            'numero' => 'required|string|max:40',
            'titular' => 'required|string|max:160',
            'orden' => 'nullable|integer',
            'Activo' => 'nullable|in:S,N',
        ]);
        $row = WebCuentaPago::query()->create($data + [
            'orden' => $data['orden'] ?? (int) WebCuentaPago::query()->max('orden') + 1,
            'Activo' => $data['Activo'] ?? 'S',
        ]);

        return response()->json(['success' => true, 'message' => 'Cuenta creada', 'result' => $row]);
    }

    public function actualizar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => 'required|integer',
            'banco' => 'required|string|max:40',
            'tipo' => 'nullable|string|max:80',
            'numero' => 'required|string|max:40',
            'titular' => 'required|string|max:160',
            'orden' => 'nullable|integer',
            'Activo' => 'nullable|in:S,N',
        ]);
        $row = WebCuentaPago::query()->findOrFail($data['id']);
        $row->fill($data);
        $row->save();

        return response()->json(['success' => true, 'message' => 'Cuenta actualizada', 'result' => $row]);
    }

    public function eliminar(Request $request): JsonResponse
    {
        WebCuentaPago::query()->where('id', (int) $request->input('id'))->delete();

        return response()->json(['success' => true, 'message' => 'Cuenta eliminada', 'result' => null]);
    }
}

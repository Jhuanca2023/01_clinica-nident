<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebPasoPedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebPasoPedidoController extends Controller
{
    public function listar(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar pasos',
            'result' => WebPasoPedido::query()->orderBy('orden')->get(),
        ]);
    }

    public function crear(Request $request): JsonResponse
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:160',
            'texto' => 'required|string|max:500',
            'orden' => 'nullable|integer',
            'Activo' => 'nullable|in:S,N',
        ]);
        $row = WebPasoPedido::query()->create([
            'titulo' => $data['titulo'],
            'texto' => $data['texto'],
            'orden' => $data['orden'] ?? (int) WebPasoPedido::query()->max('orden') + 1,
            'Activo' => $data['Activo'] ?? 'S',
        ]);

        return response()->json(['success' => true, 'message' => 'Paso creado', 'result' => $row]);
    }

    public function actualizar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => 'required|integer',
            'titulo' => 'required|string|max:160',
            'texto' => 'required|string|max:500',
            'orden' => 'nullable|integer',
            'Activo' => 'nullable|in:S,N',
        ]);
        $row = WebPasoPedido::query()->findOrFail($data['id']);
        $row->fill($data);
        $row->save();

        return response()->json(['success' => true, 'message' => 'Paso actualizado', 'result' => $row]);
    }

    public function eliminar(Request $request): JsonResponse
    {
        $id = (int) $request->input('id');
        WebPasoPedido::query()->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Paso eliminado', 'result' => null]);
    }
}

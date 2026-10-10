<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebContadores;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebContadoresController extends Controller
{
    public function listar(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => WebContadores::where('Activo', 'S')->orderBy('orden')->get(),
        ]);
    }

    public function crear(Request $request): JsonResponse
    {
        $request->validate([
            'valor'   => 'required|string|max:30',
            'etiqueta'=> 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $maxOrden = WebContadores::max('orden') ?? 0;
            $registro = WebContadores::create([
                'valor'       => $request->valor,
                'sufijo'      => $request->input('sufijo', '+'),
                'etiqueta'    => $request->etiqueta,
                'icono_clase' => $request->input('icono_clase'),
                'orden'       => $maxOrden + 1,
                'Activo'      => 'S',
            ]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contador creado correctamente.',
                'result'  => $registro,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear el registro.'], 500);
        }
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'id_contador' => 'required|exists:web_contadores,id_contador',
            'valor'       => 'nullable|string|max:30',
            'sufijo'      => 'nullable|string|max:10',
            'etiqueta'    => 'nullable|string|max:255',
        ]);

        $registro = WebContadores::findOrFail($request->id_contador);

        DB::beginTransaction();
        try {
            $registro->update([
                'valor'       => $request->input('valor', $registro->valor),
                'sufijo'      => $request->input('sufijo', $registro->sufijo),
                'etiqueta'    => $request->input('etiqueta', $registro->etiqueta),
                'icono_clase' => $request->input('icono_clase', $registro->icono_clase),
                'updated_at'  => now(),
            ]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contador actualizado correctamente.',
                'result'  => $registro->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar el registro.'], 500);
        }
    }

    public function eliminar(Request $request): JsonResponse
    {
        $request->validate(['id_contador' => 'required|exists:web_contadores,id_contador']);

        DB::beginTransaction();
        try {
            WebContadores::findOrFail($request->id_contador)->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Contador eliminado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar el registro.'], 500);
        }
    }
}

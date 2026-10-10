<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebLineaProducto;
use App\Models\WebLineaProductoSeccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebLineaProductoController extends Controller
{
    public function listar(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => [
                'seccion' => WebLineaProductoSeccion::firstOrCreate(['id' => 1]),
                'items'   => WebLineaProducto::where('Activo', 'S')->orderBy('orden')->get(),
            ],
        ]);
    }

    public function crear(Request $request): JsonResponse
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'image'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ]);

        DB::beginTransaction();
        try {
            $maxOrden = WebLineaProducto::max('orden') ?? 0;
            $data = [
                'titulo'      => $request->titulo,
                'badge'       => $request->input('badge'),
                'icono_clase' => $request->input('icono_clase'),
                'url_enlace'  => $request->input('url_enlace'),
                'orden'       => $maxOrden + 1,
                'Activo'      => 'S',
            ];

            if ($request->hasFile('image')) {
                $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'linea_producto');
            }

            $registro = WebLineaProducto::create($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Línea creada correctamente.',
                'result'  => $registro,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WebLineaProducto crear: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear el registro.'], 500);
        }
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'id'     => 'required|exists:web_linea_producto,id',
            'titulo' => 'nullable|string|max:255',
            'image'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ]);

        $registro = WebLineaProducto::findOrFail($request->id);

        DB::beginTransaction();
        try {
            $data = [
                'titulo'      => $request->input('titulo', $registro->titulo),
                'badge'       => $request->input('badge', $registro->badge),
                'icono_clase' => $request->input('icono_clase', $registro->icono_clase),
                'url_enlace'  => $request->input('url_enlace', $registro->url_enlace),
                'updated_at'  => now(),
            ];

            if ($request->hasFile('image')) {
                $this->eliminarArchivo($registro->url_imagen);
                $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'linea_producto');
            }

            $registro->update($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Línea actualizada correctamente.',
                'result'  => $registro->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar el registro.'], 500);
        }
    }

    public function actualizar_seccion(Request $request): JsonResponse
    {
        $request->validate([
            'seccion_titulo'      => 'nullable|string|max:500',
            'seccion_descripcion' => 'nullable|string',
        ]);

        $seccion = WebLineaProductoSeccion::firstOrCreate(['id' => 1]);
        $seccion->update([
            'seccion_titulo'      => $request->input('seccion_titulo', $seccion->seccion_titulo),
            'seccion_descripcion' => $request->input('seccion_descripcion', $seccion->seccion_descripcion),
            'updated_at'          => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sección actualizada.',
            'result'  => $seccion->fresh(),
        ]);
    }

    public function eliminar(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|exists:web_linea_producto,id']);
        $registro = WebLineaProducto::findOrFail($request->id);

        DB::beginTransaction();
        try {
            $this->eliminarArchivo($registro->url_imagen);
            $registro->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Línea eliminada correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar el registro.'], 500);
        }
    }

    private function eliminarArchivo(?string $path): void
    {
        if (empty($path) || str_starts_with($path, 'temp02/')) {
            return;
        }
        \App\Support\CmsImageStorage::deleteIfOwned($path);
    }
}

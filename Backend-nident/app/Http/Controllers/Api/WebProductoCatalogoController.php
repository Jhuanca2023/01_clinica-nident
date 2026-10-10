<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebProductoCatalogo;
use App\Models\WebProductoCatalogoSeccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebProductoCatalogoController extends Controller
{
    public function listar(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => [
                'seccion' => WebProductoCatalogoSeccion::firstOrCreate(['id' => 1]),
                'items'   => WebProductoCatalogo::where('Activo', 'S')->orderBy('orden')->get(),
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
            $maxOrden = WebProductoCatalogo::max('orden') ?? 0;
            $data = [
                'titulo'      => $request->titulo,
                'badge'       => $request->input('badge'),
                'descripcion' => $request->input('descripcion'),
                'btn_texto'   => $request->input('btn_texto', 'Me interesa'),
                'btn_url'     => $request->input('btn_url'),
                'orden'       => $maxOrden + 1,
                'Activo'      => 'S',
            ];

            if ($request->hasFile('image')) {
                $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'producto_catalogo');
            }

            $registro = WebProductoCatalogo::create($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto creado correctamente.',
                'result'  => $registro,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WebProductoCatalogo crear: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear el registro.'], 500);
        }
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'id'     => 'required|exists:web_producto_catalogo,id',
            'titulo' => 'nullable|string|max:255',
            'image'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ]);

        $registro = WebProductoCatalogo::findOrFail($request->id);

        DB::beginTransaction();
        try {
            $data = [
                'titulo'      => $request->input('titulo', $registro->titulo),
                'badge'       => $request->input('badge', $registro->badge),
                'descripcion' => $request->input('descripcion', $registro->descripcion),
                'btn_texto'   => $request->input('btn_texto', $registro->btn_texto),
                'btn_url'     => $request->input('btn_url', $registro->btn_url),
                'updated_at'  => now(),
            ];

            if ($request->hasFile('image')) {
                $this->eliminarArchivo($registro->url_imagen);
                $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'producto_catalogo');
            }

            $registro->update($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado correctamente.',
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
            'seccion_titulo'       => 'nullable|string|max:500',
            'seccion_descripcion'  => 'nullable|string',
        ]);

        $seccion = WebProductoCatalogoSeccion::firstOrCreate(['id' => 1]);
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
        $request->validate(['id' => 'required|exists:web_producto_catalogo,id']);
        $registro = WebProductoCatalogo::findOrFail($request->id);

        DB::beginTransaction();
        try {
            $this->eliminarArchivo($registro->url_imagen);
            $registro->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Producto eliminado correctamente.']);
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

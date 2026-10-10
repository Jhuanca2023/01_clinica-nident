<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebAboutCaracteristica;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebAboutCaracteristicaController extends Controller
{
    public function listar(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => WebAboutCaracteristica::where('Activo', 'S')->orderBy('orden')->get(),
        ]);
    }

    public function crear(Request $request): JsonResponse
    {
        $request->validate([
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ]);

        DB::beginTransaction();
        try {
            $maxOrden = WebAboutCaracteristica::max('orden') ?? 0;
            $data = [
                'id_about'     => $request->input('id_about', 1),
                'titulo'       => $request->titulo,
                'descripcion'  => $request->input('descripcion'),
                'url_enlace'   => $request->input('url_enlace'),
                'texto_enlace' => $request->input('texto_enlace', 'Ver más'),
                'orden'        => $maxOrden + 1,
                'Activo'       => 'S',
            ];

            if ($request->hasFile('image')) {
                $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'about_caracteristica');
            }

            $registro = WebAboutCaracteristica::create($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Característica creada correctamente.',
                'result'  => $registro,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WebAboutCaracteristica crear: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear el registro.'], 500);
        }
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'id'          => 'required|exists:web_about_caracteristica,id',
            'titulo'      => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ]);

        $registro = WebAboutCaracteristica::findOrFail($request->id);

        DB::beginTransaction();
        try {
            $data = [
                'titulo'       => $request->input('titulo', $registro->titulo),
                'descripcion'  => $request->input('descripcion', $registro->descripcion),
                'url_enlace'   => $request->input('url_enlace', $registro->url_enlace),
                'texto_enlace' => $request->input('texto_enlace', $registro->texto_enlace),
                'updated_at'   => now(),
            ];

            if ($request->hasFile('image')) {
                $this->eliminarArchivo($registro->url_imagen);
                $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'about_caracteristica');
            }

            $registro->update($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Característica actualizada correctamente.',
                'result'  => $registro->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar el registro.'], 500);
        }
    }

    public function eliminar(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|exists:web_about_caracteristica,id']);
        $registro = WebAboutCaracteristica::findOrFail($request->id);

        DB::beginTransaction();
        try {
            $this->eliminarArchivo($registro->url_imagen);
            $registro->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Característica eliminada correctamente.']);
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

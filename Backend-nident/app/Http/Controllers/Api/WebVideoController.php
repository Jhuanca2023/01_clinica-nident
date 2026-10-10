<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebVideo;
use App\Models\WebVideoSeccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebVideoController extends Controller
{
    public function listar(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => WebVideo::where('Activo', 'S')->orderBy('orden')->get(),
        ]);
    }

    public function obtener_seccion(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Obtener sección',
            'result'  => WebVideoSeccion::firstOrCreate(['id' => 1]),
        ]);
    }

    public function actualizar_seccion(Request $request): JsonResponse
    {
        $request->validate([
            'bloque_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ]);

        DB::beginTransaction();
        try {
            $seccion = WebVideoSeccion::firstOrCreate(['id' => 1]);
            $data = [
                'seccion_titulo'       => $request->input('seccion_titulo', $seccion->seccion_titulo),
                'seccion_descripcion'  => $request->input('seccion_descripcion', $seccion->seccion_descripcion),
                'bloque_titulo'        => $request->input('bloque_titulo', $seccion->bloque_titulo),
                'bloque_descripcion'   => $request->input('bloque_descripcion', $seccion->bloque_descripcion),
                'bloque_btn_texto'     => $request->input('bloque_btn_texto', $seccion->bloque_btn_texto),
                'bloque_btn_url'       => $request->input('bloque_btn_url', $seccion->bloque_btn_url),
                'updated_at'           => now(),
            ];

            if ($request->hasFile('bloque_image')) {
                $this->eliminarArchivo($seccion->bloque_url_imagen);
                $data['bloque_url_imagen'] = \App\Support\CmsImageStorage::store($request->file('bloque_image'), 'video_seccion');
            }

            $seccion->update($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sección actualizada.',
                'result'  => $seccion->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WebVideo actualizar_seccion: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar la sección.'], 500);
        }
    }

    public function crear(Request $request): JsonResponse
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'video'  => 'nullable|file|mimes:mp4|max:51200',
        ]);

        DB::beginTransaction();
        try {
            $maxOrden = WebVideo::max('orden') ?? 0;
            $data = [
                'tag'         => $request->input('tag'),
                'titulo'      => $request->titulo,
                'descripcion' => $request->input('descripcion'),
                'orden'       => $maxOrden + 1,
                'Activo'      => 'S',
            ];

            if ($request->hasFile('video')) {
                $data['url_video'] = $request->file('video')->store('storage_/videos', 'public_imagenes');
            }

            $registro = WebVideo::create($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Video creado correctamente.',
                'result'  => $registro,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WebVideo crear: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear el registro.'], 500);
        }
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'id'     => 'required|exists:web_video,id',
            'titulo' => 'nullable|string|max:255',
            'video'  => 'nullable|file|mimes:mp4|max:51200',
        ]);

        $registro = WebVideo::findOrFail($request->id);

        DB::beginTransaction();
        try {
            $data = [
                'tag'         => $request->input('tag', $registro->tag),
                'titulo'      => $request->input('titulo', $registro->titulo),
                'descripcion' => $request->input('descripcion', $registro->descripcion),
                'updated_at'  => now(),
            ];

            if ($request->hasFile('video')) {
                $this->eliminarArchivo($registro->url_video);
                $data['url_video'] = $request->file('video')->store('storage_/videos', 'public_imagenes');
            }

            $registro->update($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Video actualizado correctamente.',
                'result'  => $registro->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar el registro.'], 500);
        }
    }

    public function eliminar(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|exists:web_video,id']);
        $registro = WebVideo::findOrFail($request->id);

        DB::beginTransaction();
        try {
            $this->eliminarArchivo($registro->url_video);
            $registro->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Video eliminado correctamente.']);
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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebPaginaVideos;
use App\Models\WebVideos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebVideosController extends Controller
{
    public function obtener(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'result'  => [
                'pagina' => WebPaginaVideos::firstOrCreate(['id' => 1]),
                'items'  => WebVideos::orderBy('orden')->get(),
            ],
        ]);
    }

    public function listar(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => WebVideos::where('Activo', 'S')->orderBy('orden')->get(),
        ]);
    }

    public function actualizar_pagina(Request $request): JsonResponse
    {
        $request->validate([
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ]);

        $pagina = WebPaginaVideos::firstOrCreate(['id' => 1]);
        $data = $request->only(['banner_titulo', 'seccion_titulo', 'seccion_descripcion', 'frase_texto']);

        if ($request->hasFile('banner_image')) {
            $this->eliminarArchivo($pagina->banner_url_imagen);
            $data['banner_url_imagen'] = \App\Support\CmsImageStorage::store($request->file('banner_image'), 'pagina_banner');
        }

        $pagina->update(array_merge($data, ['updated_at' => now()]));

        return response()->json([
            'success' => true,
            'message' => 'Página actualizada.',
            'result'  => $pagina->fresh(),
        ]);
    }

    public function crear(Request $request): JsonResponse
    {
        $request->validate([
            'titulo'         => 'required|string|max:255',
            'tipo_video'     => 'required|in:archivo,youtube',
            'video'          => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg,video/quicktime,video/x-msvideo,video/x-matroska|max:102400',
            'url_youtube'    => 'nullable|string|max:500',
            'portada_image'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ]);

        $tipo = $request->input('tipo_video', 'archivo');
        if ($tipo === 'youtube' && !$request->filled('url_youtube')) {
            return response()->json(['success' => false, 'message' => 'Indique la URL de YouTube.'], 422);
        }
        if ($tipo === 'archivo' && !$request->hasFile('video')) {
            return response()->json(['success' => false, 'message' => 'Suba un archivo de video.'], 422);
        }

        DB::beginTransaction();
        try {
            $maxOrden = WebVideos::max('orden') ?? 0;
            $data = [
                'tag'         => $request->input('tag'),
                'titulo'      => $request->titulo,
                'descripcion' => $request->input('descripcion'),
                'tipo_video'  => $tipo,
                'url_youtube' => $tipo === 'youtube' ? trim($request->url_youtube) : null,
                'orden'       => $maxOrden + 1,
                'Activo'      => $request->input('Activo', 'S'),
            ];

            if ($tipo === 'archivo' && $request->hasFile('video')) {
                $data['url_video'] = $request->file('video')->store('storage_/videos', 'public_imagenes');
            }

            if ($request->hasFile('portada_image')) {
                $data['url_imagen_portada'] = \App\Support\CmsImageStorage::store($request->file('portada_image'), 'videos_portada');
            }

            $registro = WebVideos::create($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Video creado correctamente.',
                'result'  => $registro,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WebVideos crear: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear el video.'], 500);
        }
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'id'            => 'required|exists:web_videos,id',
            'titulo'        => 'nullable|string|max:255',
            'tipo_video'    => 'nullable|in:archivo,youtube',
            'video'         => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg,video/quicktime,video/x-msvideo,video/x-matroska|max:102400',
            'url_youtube'   => 'nullable|string|max:500',
            'portada_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
            'remove_portada'=> 'nullable|in:0,1,true,false',
        ]);

        $registro = WebVideos::findOrFail($request->id);
        $tipo = $request->input('tipo_video', $registro->tipo_video);

        DB::beginTransaction();
        try {
            $data = [
                'tag'         => $request->input('tag', $registro->tag),
                'titulo'      => $request->input('titulo', $registro->titulo),
                'descripcion' => $request->input('descripcion', $registro->descripcion),
                'tipo_video'  => $tipo,
                'Activo'      => $request->input('Activo', $registro->Activo),
                'updated_at'  => now(),
            ];

            if ($tipo === 'youtube') {
                $data['url_youtube'] = $request->input('url_youtube', $registro->url_youtube);
                if ($registro->url_video) {
                    $this->eliminarArchivo($registro->url_video);
                    $data['url_video'] = null;
                }
            } else {
                $data['url_youtube'] = null;
                if ($request->hasFile('video')) {
                    $this->eliminarArchivo($registro->url_video);
                    $data['url_video'] = $request->file('video')->store('storage_/videos', 'public_imagenes');
                }
            }

            if ($request->hasFile('portada_image')) {
                $this->eliminarArchivo($registro->url_imagen_portada);
                $data['url_imagen_portada'] = \App\Support\CmsImageStorage::store($request->file('portada_image'), 'videos_portada');
            } elseif (filter_var($request->input('remove_portada'), FILTER_VALIDATE_BOOLEAN)) {
                $this->eliminarArchivo($registro->url_imagen_portada);
                $data['url_imagen_portada'] = null;
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
            return response()->json(['success' => false, 'message' => 'Error al actualizar el video.'], 500);
        }
    }

    public function eliminar(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|exists:web_videos,id']);
        $registro = WebVideos::findOrFail($request->id);

        DB::beginTransaction();
        try {
            $this->eliminarArchivo($registro->url_video);
            $this->eliminarArchivo($registro->url_imagen_portada);
            $registro->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Video eliminado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar el video.'], 500);
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

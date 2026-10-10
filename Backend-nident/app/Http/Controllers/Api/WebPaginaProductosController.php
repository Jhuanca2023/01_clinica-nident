<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebPaginaProductos;
use App\Models\WebProductosPaginaGaleria;
use App\Models\WebProductosPaginaGaleriaSeccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebPaginaProductosController extends Controller
{
    public function obtener(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'result'  => [
                'pagina'  => WebPaginaProductos::first(),
                'seccion' => WebProductosPaginaGaleriaSeccion::first(),
                'galeria' => WebProductosPaginaGaleria::where('Activo', 'S')->orderBy('orden')->get(),
            ],
        ]);
    }

    public function actualizar_pagina(Request $request): JsonResponse
    {
        $pagina = WebPaginaProductos::firstOrCreate(['id' => 1]);
        $data = $request->only([
            'banner_titulo',
            'intro_titulo', 'intro_descripcion', 'intro_btn_texto', 'intro_btn_url',
            'prioridad_etiqueta', 'prioridad_titulo', 'prioridad_descripcion',
            'prioridad_btn_texto', 'prioridad_btn_url',
            'frase_titulo', 'frase_texto',
        ]);

        if ($request->hasFile('banner_image')) {
            $this->del($pagina->banner_url_imagen);
            $data['banner_url_imagen'] = \App\Support\CmsImageStorage::store($request->file('banner_image'), 'pagina_banner');
        }
        if ($request->hasFile('intro_image')) {
            $this->del($pagina->intro_url_imagen);
            $data['intro_url_imagen'] = \App\Support\CmsImageStorage::store($request->file('intro_image'), 'productos_intro');
        }
        if ($request->hasFile('prioridad_image')) {
            $this->del($pagina->prioridad_url_imagen);
            $data['prioridad_url_imagen'] = \App\Support\CmsImageStorage::store($request->file('prioridad_image'), 'productos_prioridad');
        }

        $pagina->update($data);

        return response()->json(['success' => true, 'message' => 'Página actualizada', 'result' => $pagina->fresh()]);
    }

    public function actualizar_galeria_seccion(Request $request): JsonResponse
    {
        $sec = WebProductosPaginaGaleriaSeccion::firstOrCreate(['id' => 1]);
        $sec->update($request->only(['seccion_titulo', 'seccion_descripcion']));

        return response()->json(['success' => true, 'result' => $sec->fresh()]);
    }

    public function crear_galeria(Request $request): JsonResponse
    {
        $request->validate(['titulo' => 'required|string|max:255', 'image' => 'nullable|image|max:6144']);
        $max = WebProductosPaginaGaleria::max('orden') ?? 0;
        $data = [
            'titulo'      => $request->titulo,
            'descripcion' => $request->input('descripcion'),
            'btn_texto'   => $request->input('btn_texto', 'Me interesa'),
            'btn_url'     => $request->input('btn_url', 'https://wa.me/51981629466'),
            'orden'       => $max + 1,
            'Activo'      => 'S',
        ];
        if ($request->hasFile('image')) {
            $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'productos_galeria');
        }
        $item = WebProductosPaginaGaleria::create($data);

        return response()->json(['success' => true, 'result' => $item]);
    }

    public function actualizar_galeria(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|exists:web_productos_pagina_galeria,id', 'image' => 'nullable|image|max:6144']);
        $item = WebProductosPaginaGaleria::findOrFail($request->id);
        $data = [
            'titulo'      => $request->input('titulo', $item->titulo),
            'descripcion' => $request->input('descripcion', $item->descripcion),
            'btn_texto'   => $request->input('btn_texto', $item->btn_texto),
            'btn_url'     => $request->input('btn_url', $item->btn_url),
            'updated_at'  => now(),
        ];
        if ($request->hasFile('image')) {
            $this->del($item->url_imagen);
            $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'productos_galeria');
        }
        $item->update($data);

        return response()->json(['success' => true, 'result' => $item->fresh()]);
    }

    public function eliminar_galeria(Request $request): JsonResponse
    {
        $item = WebProductosPaginaGaleria::findOrFail($request->id);
        $this->del($item->url_imagen);
        $item->delete();

        return response()->json(['success' => true, 'message' => 'Eliminado']);
    }

    private function del(?string $path): void
    {
        if ($path && !str_starts_with($path, 'temp02/')) {
            \App\Support\CmsImageStorage::deleteIfOwned($path);
        }
    }
}

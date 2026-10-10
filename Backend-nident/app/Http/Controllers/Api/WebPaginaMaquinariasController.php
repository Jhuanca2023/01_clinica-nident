<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebMaquinariasItem;
use App\Models\WebPaginaMaquinarias;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebPaginaMaquinariasController extends Controller
{
    public function obtener(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'result'  => [
                'pagina' => WebPaginaMaquinarias::first(),
                'items'  => WebMaquinariasItem::where('Activo', 'S')->orderBy('orden')->get(),
            ],
        ]);
    }

    public function actualizar_pagina(Request $request): JsonResponse
    {
        $pagina = WebPaginaMaquinarias::firstOrCreate(['id' => 1]);
        $data = $request->only(['banner_titulo', 'seccion_titulo', 'seccion_descripcion', 'frase_texto']);

        if ($request->hasFile('banner_image')) {
            $this->del($pagina->banner_url_imagen);
            $data['banner_url_imagen'] = \App\Support\CmsImageStorage::store($request->file('banner_image'), 'pagina_banner');
        }

        $pagina->update($data);

        return response()->json(['success' => true, 'result' => $pagina->fresh()]);
    }

    public function crear_item(Request $request): JsonResponse
    {
        $request->validate([
            'titulo'      => 'required|string|max:255',
            'image_front' => 'required|image|max:6144',
            'image_back'  => 'nullable|image|max:6144',
        ]);

        $max = WebMaquinariasItem::max('orden') ?? 0;
        $data = [
            'titulo'    => $request->titulo,
            'btn_texto' => $request->input('btn_texto', 'Me interesa'),
            'btn_url'   => $request->input('btn_url', 'https://wa.me/51981629466'),
            'orden'     => $max + 1,
            'Activo'    => 'S',
        ];

        if ($request->hasFile('image_front')) {
            $data['url_imagen_front'] = \App\Support\CmsImageStorage::store($request->file('image_front'), 'maquinarias');
        }
        if ($request->hasFile('image_back')) {
            $data['url_imagen_back'] = \App\Support\CmsImageStorage::store($request->file('image_back'), 'maquinarias');
        }

        $item = WebMaquinariasItem::create($data);

        return response()->json(['success' => true, 'result' => $item]);
    }

    public function actualizar_item(Request $request): JsonResponse
    {
        $request->validate([
            'id'          => 'required|exists:web_maquinarias_item,id',
            'titulo'      => 'nullable|string|max:255',
            'image_front' => 'nullable|image|max:6144',
            'image_back'  => 'nullable|image|max:6144',
            'remove_back' => 'nullable|in:0,1,true,false',
        ]);

        $item = WebMaquinariasItem::findOrFail($request->id);
        $data = [
            'titulo'    => $request->input('titulo', $item->titulo),
            'btn_texto' => $request->input('btn_texto', $item->btn_texto),
            'btn_url'   => $request->input('btn_url', $item->btn_url),
            'updated_at'=> now(),
        ];

        if ($request->hasFile('image_front')) {
            $this->del($item->url_imagen_front);
            $data['url_imagen_front'] = \App\Support\CmsImageStorage::store($request->file('image_front'), 'maquinarias');
        }
        if ($request->hasFile('image_back')) {
            $this->del($item->url_imagen_back);
            $data['url_imagen_back'] = \App\Support\CmsImageStorage::store($request->file('image_back'), 'maquinarias');
        } elseif (filter_var($request->input('remove_back'), FILTER_VALIDATE_BOOLEAN)) {
            $this->del($item->url_imagen_back);
            $data['url_imagen_back'] = null;
        }

        if (empty($data['url_imagen_front'] ?? $item->url_imagen_front)) {
            return response()->json(['success' => false, 'message' => 'La imagen principal (frente) es obligatoria.'], 422);
        }

        $item->update($data);

        return response()->json(['success' => true, 'result' => $item->fresh()]);
    }

    public function eliminar_item(Request $request): JsonResponse
    {
        $item = WebMaquinariasItem::findOrFail($request->id);
        $this->del($item->url_imagen_front);
        $this->del($item->url_imagen_back);
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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebPaginaGaleria;
use App\Models\WebPaginaGaleriaSeccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebPaginaGaleriaController extends Controller
{
    public function listar(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'result'  => [
                'seccion' => WebPaginaGaleriaSeccion::find(1),
                'items'   => WebPaginaGaleria::where('contexto', 'royal_galeria')->orderBy('orden')->get(),
            ],
        ]);
    }

    public function actualizar_seccion(Request $request): JsonResponse
    {
        $sec = WebPaginaGaleriaSeccion::firstOrCreate(['id' => 1]);
        $sec->update($request->only(['seccion_titulo', 'seccion_subtitulo']));

        return response()->json(['success' => true, 'result' => $sec->fresh()]);
    }

    public function crear(Request $request): JsonResponse
    {
        $request->validate(['image' => 'required|image|max:6144']);
        $max = (int) (WebPaginaGaleria::where('contexto', 'royal_galeria')->max('orden') ?? 0);

        $item = WebPaginaGaleria::create([
            'contexto'       => 'royal_galeria',
            'categoria'      => $request->input('categoria', 'todas'),
            'titulo_overlay' => $request->input('titulo_overlay'),
            'alt_imagen'     => $request->input('alt_imagen'),
            'url_imagen'     => \App\Support\CmsImageStorage::store($request->file('image'), 'galeria_royal'),
            'orden'          => $max + 1,
            'Activo'         => 'S',
        ]);

        return response()->json(['success' => true, 'result' => $item]);
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate(['id_galeria' => 'required|integer']);
        $item = WebPaginaGaleria::findOrFail($request->id_galeria);
        $data = $request->only(['categoria', 'titulo_overlay', 'alt_imagen', 'orden', 'Activo']);

        if ($request->hasFile('image')) {
            $this->del($item->url_imagen);
            $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'galeria_royal');
        }

        $item->update($data);

        return response()->json(['success' => true, 'result' => $item->fresh()]);
    }

    public function eliminar(Request $request): JsonResponse
    {
        $item = WebPaginaGaleria::findOrFail($request->id_galeria);
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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebContadores;
use App\Models\WebNosotrosProducto;
use App\Models\WebNosotrosProductoSeccion;
use App\Models\WebNosotrosQuienes;
use App\Models\WebNosotrosQuienesBeneficio;
use App\Models\WebPaginaNosotros;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class WebPaginaNosotrosController extends Controller
{
    public function obtener(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'result'  => [
                'pagina'              => WebPaginaNosotros::first(),
                'rossety'             => \App\Support\RossetyNosotros::config(),
                'quienes'             => Schema::hasTable('web_nosotros_quienes')
                    ? WebNosotrosQuienes::where('Activo', 'S')->first()
                    : null,
                'quienes_beneficios'  => Schema::hasTable('web_nosotros_quienes_beneficio')
                    ? WebNosotrosQuienesBeneficio::where('Activo', 'S')->orderBy('orden')->get()
                    : [],
                // Misma fuente que la web (/nosotros): web_contadores con contexto "nosotros"
                'about_contadores'    => $this->contadoresNosotros(),
                'producto_seccion'    => Schema::hasTable('web_nosotros_producto_seccion')
                    ? WebNosotrosProductoSeccion::first()
                    : null,
                'productos'           => Schema::hasTable('web_nosotros_producto')
                    ? WebNosotrosProducto::where('Activo', 'S')->orderBy('orden')->get()
                    : [],
            ],
        ]);
    }

    public function actualizar_pagina(Request $request): JsonResponse
    {
        $request->validate([
            'rossety_nosotros' => 'sometimes|json',
            'nosotros_image' => \App\Support\OptimizedMedia::RULE,
        ]);
        $oldHistoriaImage = null;
        $newHistoriaImage = null;
        $pagina = WebPaginaNosotros::firstOrCreate(['id' => 1]);
        $data = $request->only([
            'banner_titulo',
            'intro_titulo',
            'intro_descripcion',
            'intro_btn_texto',
            'intro_btn_url',
            'frase_texto',
            'titulo_hero',
            'subtitulo_hero',
            'titulo_seccion',
            'descripcion_seccion',
            'historia_badge',
            'cta_titulo',
            'cta_texto',
            'cta_btn_texto',
            'cta_btn_url',
        ]);

        if ($request->has('rossety_nosotros')) {
            $conf = json_decode((string) $request->input('rossety_nosotros'), true);
            if (is_array($conf)) {
                unset($conf['historia']['imagen_url']);
                if ($request->hasFile('nosotros_image')) {
                    $anterior = \App\Support\RossetyNosotros::config()['historia']['imagen'] ?? '';
                    $oldHistoriaImage = $anterior;
                    $conf['historia']['imagen'] = $newHistoriaImage = \App\Support\CmsImageStorage::store($request->file('nosotros_image'), 'nosotros_historia');
                }
                $data['rossety_nosotros'] = json_encode($conf, JSON_UNESCAPED_UNICODE);
            }
        }

        if ($request->has('valores_json')) {
            $data['valores_json'] = is_string($request->valores_json)
                ? $request->valores_json
                : json_encode($request->valores_json, JSON_UNESCAPED_UNICODE);
        }
        if ($request->has('timeline_json')) {
            $data['timeline_json'] = is_string($request->timeline_json)
                ? $request->timeline_json
                : json_encode($request->timeline_json, JSON_UNESCAPED_UNICODE);
        }

        if ($request->hasFile('banner_image')) {
            $this->del($pagina->banner_url_imagen);
            $data['banner_url_imagen'] = \App\Support\CmsImageStorage::store($request->file('banner_image'), 'pagina_banner');
        }
        if ($request->hasFile('intro_logo')) {
            $this->del($pagina->intro_url_logo);
            $data['intro_url_logo'] = \App\Support\CmsImageStorage::store($request->file('intro_logo'), 'nosotros_intro');
        }
        if ($request->hasFile('catalogo_image')) {
            $this->del($pagina->catalogo_url_imagen);
            $data['catalogo_url_imagen'] = \App\Support\CmsImageStorage::store($request->file('catalogo_image'), 'nosotros_catalogo');
        }
        if ($request->hasFile('hero_image')) {
            $this->del($pagina->url_imagen_hero);
            $data['url_imagen_hero'] = \App\Support\CmsImageStorage::store($request->file('hero_image'), 'nosotros_hero');
        }
        if ($request->hasFile('seccion_image')) {
            $this->del($pagina->url_imagen_seccion);
            $data['url_imagen_seccion'] = \App\Support\CmsImageStorage::store($request->file('seccion_image'), 'nosotros_seccion');
        }

        try {
            $pagina->update($data);
        } catch (\Throwable $e) {
            if ($newHistoriaImage) $this->del($newHistoriaImage);
            throw $e;
        }
        if ($newHistoriaImage && $oldHistoriaImage !== $newHistoriaImage) {
            $this->del($oldHistoriaImage);
        }

        return response()->json(['success' => true, 'message' => 'Página actualizada', 'result' => array_merge($pagina->fresh()->toArray(), ['rossety' => \App\Support\RossetyNosotros::config()])]);
    }

    public function actualizar_quienes(Request $request): JsonResponse
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'image'  => 'nullable|image|max:6144',
        ]);

        $quienes = WebNosotrosQuienes::firstOrCreate(['id' => 1]);
        $data = [
            'titulo'      => $request->titulo,
            'descripcion' => $request->input('descripcion'),
            'Activo'      => 'S',
            'updated_at'  => now(),
        ];

        if ($request->hasFile('image')) {
            $this->del($quienes->url_imagen);
            $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'nosotros_quienes');
        }

        $quienes->update($data);

        return response()->json(['success' => true, 'message' => 'Quiénes Somos actualizado', 'result' => $quienes->fresh()]);
    }

    public function crear_beneficio(Request $request): JsonResponse
    {
        $request->validate(['texto' => 'required|string|max:500']);
        $max = WebNosotrosQuienesBeneficio::max('orden') ?? 0;
        $item = WebNosotrosQuienesBeneficio::create([
            'texto'  => $request->texto,
            'orden'  => $max + 1,
            'Activo' => 'S',
        ]);

        return response()->json(['success' => true, 'result' => $item]);
    }

    public function actualizar_beneficio(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|exists:web_nosotros_quienes_beneficio,id', 'texto' => 'required|string|max:500']);
        $item = WebNosotrosQuienesBeneficio::findOrFail($request->id);
        $item->update(['texto' => $request->texto]);

        return response()->json(['success' => true, 'result' => $item]);
    }

    public function eliminar_beneficio(Request $request): JsonResponse
    {
        WebNosotrosQuienesBeneficio::findOrFail($request->id)->delete();

        return response()->json(['success' => true, 'message' => 'Eliminado']);
    }

    /** Estadísticas de /nosotros (web_contadores, contexto "nosotros"), con `id` para el admin. */
    private function contadoresNosotros()
    {
        return WebContadores::where('contexto', 'nosotros')
            ->where('Activo', 'S')
            ->orderBy('orden')
            ->get()
            ->map(fn ($c) => array_merge($c->toArray(), ['id' => $c->id_contador]));
    }

    public function crear_contador(Request $request): JsonResponse
    {
        $request->validate([
            'valor'    => 'required|string|max:30',
            'etiqueta' => 'required|string|max:255',
        ]);

        $max = WebContadores::where('contexto', 'nosotros')->max('orden') ?? 0;
        $item = WebContadores::create([
            'contexto' => 'nosotros',
            'valor'    => $request->valor,
            'sufijo'   => $request->input('sufijo', ''),
            'etiqueta' => $request->etiqueta,
            'orden'    => $max + 1,
            'Activo'   => 'S',
        ]);

        return response()->json(['success' => true, 'result' => array_merge($item->toArray(), ['id' => $item->id_contador])]);
    }

    public function actualizar_contador(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|exists:web_contadores,id_contador']);
        $item = WebContadores::findOrFail($request->id);
        $item->update([
            'valor'    => $request->input('valor', $item->valor),
            'sufijo'   => $request->input('sufijo', '') ?? '',
            'etiqueta' => $request->input('etiqueta', $item->etiqueta),
        ]);

        return response()->json(['success' => true, 'result' => array_merge($item->toArray(), ['id' => $item->id_contador])]);
    }

    public function eliminar_contador(Request $request): JsonResponse
    {
        // Se desactiva (no se borra), igual que las demás secciones del CMS.
        WebContadores::where('id_contador', $request->id)
            ->where('contexto', 'nosotros')
            ->update(['Activo' => 'N']);

        return response()->json(['success' => true, 'message' => 'Eliminado']);
    }

    public function actualizar_producto_seccion(Request $request): JsonResponse
    {
        $sec = WebNosotrosProductoSeccion::firstOrCreate(['id' => 1]);
        $sec->update($request->only(['seccion_titulo', 'seccion_descripcion']));

        return response()->json(['success' => true, 'result' => $sec->fresh()]);
    }

    public function crear_producto(Request $request): JsonResponse
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'image'  => 'nullable|image|max:6144',
        ]);

        $max = WebNosotrosProducto::max('orden') ?? 0;
        $data = [
            'titulo'      => $request->titulo,
            'descripcion' => $request->input('descripcion'),
            'btn_texto'   => $request->input('btn_texto', 'Me interesa'),
            'btn_url'     => $request->input('btn_url', 'https://wa.me/51981629466'),
            'orden'       => $max + 1,
            'Activo'      => 'S',
        ];

        if ($request->hasFile('image')) {
            $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'nosotros_producto');
        }

        $item = WebNosotrosProducto::create($data);

        return response()->json(['success' => true, 'result' => $item]);
    }

    public function actualizar_producto(Request $request): JsonResponse
    {
        $request->validate([
            'id'     => 'required|exists:web_nosotros_producto,id',
            'titulo' => 'nullable|string|max:255',
            'image'  => 'nullable|image|max:6144',
        ]);

        $item = WebNosotrosProducto::findOrFail($request->id);
        $data = [
            'titulo'      => $request->input('titulo', $item->titulo),
            'descripcion' => $request->input('descripcion', $item->descripcion),
            'btn_texto'   => $request->input('btn_texto', $item->btn_texto),
            'btn_url'     => $request->input('btn_url', $item->btn_url),
            'updated_at'  => now(),
        ];

        if ($request->hasFile('image')) {
            $this->del($item->url_imagen);
            $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'nosotros_producto');
        }

        $item->update($data);

        return response()->json(['success' => true, 'result' => $item->fresh()]);
    }

    public function eliminar_producto(Request $request): JsonResponse
    {
        $item = WebNosotrosProducto::findOrFail($request->id);
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

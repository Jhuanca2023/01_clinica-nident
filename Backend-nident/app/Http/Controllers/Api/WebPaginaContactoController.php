<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebContactoColumna;
use App\Models\WebFooter;
use App\Models\WebPaginaContacto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebPaginaContactoController extends Controller
{
    public function obtener(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'result'  => [
                'pagina'   => WebPaginaContacto::find(1) ?? WebPaginaContacto::first(),
                'rossety'  => \App\Support\RossetyContacto::config(),
                'columnas' => WebContactoColumna::where('Activo', 'S')->orderBy('orden')->get(),
                // Redes de "Síguenos" (se editan en Header o Footer)
                'redes'    => \App\Support\LiamPageData::redesSociales(WebFooter::where('id_footer', 1)->first()) ?? [],
            ],
        ]);
    }

    public function actualizar(Request $request): JsonResponse
    {
        $pagina = WebPaginaContacto::firstOrCreate(['id' => 1]);
        $data = $request->only([
            'banner_titulo', 'hero_subtitulo', 'form_titulo', 'form_subtitulo', 'form_descripcion',
            'btn_texto', 'banner_url_imagen',
            'mapa_embed_url', 'info_titulo', 'info_texto',
            'telefono_etiqueta', 'telefonos_texto', 'telefono',
            'email_etiqueta', 'emails_texto', 'email',
            'ubicacion_etiqueta', 'ubicacion',
            'horario_etiqueta', 'horario_linea1', 'horario_linea2', 'horario_dias',
            'productos_placeholder',
            'suscribe_titulo', 'suscribe_texto', 'suscribe_placeholder',
            'frase_texto', 'rossety_contacto',
        ]);

        if ($request->hasFile('image')) {
            $this->del($pagina->url_imagen_form);
            $data['url_imagen_form'] = \App\Support\CmsImageStorage::store($request->file('image'), 'pagina_contacto');
        }
        if ($request->hasFile('banner_image')) {
            $this->del($pagina->banner_url_imagen);
            $data['banner_url_imagen'] = \App\Support\CmsImageStorage::store($request->file('banner_image'), 'pagina_banner');
        }

        $pagina->update($data);

        return response()->json(['success' => true, 'message' => 'Contacto actualizado', 'result' => $pagina->fresh()]);
    }

    public function crear_columna(Request $request): JsonResponse
    {
        $request->validate([
            'titulo'      => 'required|string|max:500',
            'descripcion' => 'nullable|string',
        ]);
        $max = WebContactoColumna::max('orden') ?? 0;
        $item = WebContactoColumna::create([
            'titulo'      => $request->titulo,
            'descripcion' => $request->descripcion,
            'orden'       => $max + 1,
            'Activo'      => 'S',
        ]);

        return response()->json(['success' => true, 'result' => $item]);
    }

    public function actualizar_columna(Request $request): JsonResponse
    {
        $request->validate([
            'id'          => 'required|exists:web_contacto_columna,id',
            'titulo'      => 'required|string|max:500',
            'descripcion' => 'nullable|string',
        ]);
        $item = WebContactoColumna::findOrFail($request->id);
        $item->update($request->only(['titulo', 'descripcion']));

        return response()->json(['success' => true, 'result' => $item]);
    }

    public function eliminar_columna(Request $request): JsonResponse
    {
        WebContactoColumna::findOrFail($request->id)->delete();

        return response()->json(['success' => true, 'message' => 'Eliminado']);
    }

    private function del(?string $path): void
    {
        if ($path && !str_starts_with($path, 'temp02/')) {
            \App\Support\CmsImageStorage::deleteIfOwned($path);
        }
    }
}

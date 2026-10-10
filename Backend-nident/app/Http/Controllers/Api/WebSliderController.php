<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebSlider;
use App\Models\WebSliderConfig;
use App\Support\CmsStorageUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebSliderController extends Controller
{
    private const SLIDER_IMAGE_DIR = 'storage_/slider';

    public function obtener(Request $request): JsonResponse
    {
        $id = $request->id;
        $rows = DB::select('select * from web_slider where id_slider = ?', [$id]);
        $result = array_map(fn ($row) => $this->formatSliderRow((array) $row), $rows);

        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result'  => $result,
        ]);
    }

    public function listar(): JsonResponse
    {
        $result = WebSlider::orderBy('id_slider', 'ASC')
            ->get()
            ->map(fn (WebSlider $slider) => $this->formatSlider($slider))
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => $result,
        ]);
    }

    public function configuracion(): JsonResponse
    {
        $defaults = [
            'id'             => 1,
            'autoplay'       => 'S',
            'interval_ms'    => 5000,
            'pause_on_hover' => 'S',
        ];

        if (! Schema::hasTable('web_slider_config')) {
            return response()->json([
                'success' => true,
                'message' => 'Configuración por defecto',
                'result'  => (object) $defaults,
            ]);
        }

        $row = WebSliderConfig::find(1);
        if (! $row) {
            WebSliderConfig::create($defaults);
            $row = WebSliderConfig::find(1);
        }

        return response()->json([
            'success' => true,
            'message' => 'Configuración del slider',
            'result'  => $row,
        ]);
    }

    public function actualizarConfiguracion(Request $request): JsonResponse
    {
        if (! Schema::hasTable('web_slider_config')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabla web_slider_config no existe. Ejecute la migración.',
            ], 422);
        }

        $autoplay = in_array($request->input('autoplay'), ['S', 'N'], true)
            ? $request->input('autoplay')
            : 'S';
        $pause = in_array($request->input('pause_on_hover'), ['S', 'N'], true)
            ? $request->input('pause_on_hover')
            : 'S';
        $interval = (int) $request->input('interval_ms', 5000);
        $interval = max(2000, min(30000, $interval));

        $row = WebSliderConfig::find(1);
        if (! $row) {
            $row = WebSliderConfig::create([
                'id'             => 1,
                'autoplay'       => $autoplay,
                'interval_ms'    => $interval,
                'pause_on_hover' => $pause,
            ]);
        } else {
            $row->autoplay       = $autoplay;
            $row->interval_ms    = $interval;
            $row->pause_on_hover = $pause;
            $row->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada',
            'result'  => $row,
        ]);
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'id_slider' => 'required|integer|exists:web_slider,id_slider',
            // Sin límite de peso ni formato: el cliente puede subir cualquier imagen
            'image'     => 'nullable|file',
        ]);

        $slider = WebSlider::find($request->id_slider);
        if (! $slider) {
            return response()->json([
                'success' => false,
                'message' => 'Slider no encontrado.',
            ], 404);
        }

        DB::beginTransaction();

        try {
            if ($request->hasFile('image')) {
                $oldPath = $slider->url_imagen;
                $slider->url_imagen = $this->storeSliderImage(
                    $request->file('image'),
                    (int) $slider->id_slider,
                    $oldPath
                );
            }

            foreach ([
                'subtitulo', 'slide_tag', 'titulo', 'descripcion', 'texto_extendido',
                'url_link', 'texto_boton', 'texto_boton_2', 'url_link_2', 'Activo',
            ] as $field) {
                if ($request->has($field)) {
                    $slider->{$field} = $request->input($field);
                }
            }

            $slider->updated_at = now();
            $slider->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registro actualizado',
                'result'  => $this->formatSlider($slider->fresh()),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar el slider: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el registro.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function crear(Request $request): JsonResponse
    {
        $request->validate([
            'titulo'      => 'nullable|string|max:500',
            'subtitulo'   => 'nullable|string|max:500',
            'descripcion' => 'nullable|string',
            'texto_boton' => 'nullable|string|max:255',
            'url_link'    => 'nullable|string|max:500',
            // Sin límite de peso ni formato
            'image'       => 'nullable|file',
        ]);

        DB::beginTransaction();
        try {
            $newId = ((int) WebSlider::max('id_slider')) + 1;
            if ($newId === WebSlider::ID_BANNER_INTERNO) {
                $newId++;
            }

            $data = [
                'id_slider'   => $newId,
                'subtitulo'   => $request->input('subtitulo', ''),
                'titulo'      => $request->input('titulo', 'Nuevo slide'),
                'descripcion' => $request->input('descripcion', ''),
                'texto_boton' => $request->input('texto_boton', 'Ver más'),
                'url_link'    => $request->input('url_link', '#'),
                'Activo'      => 'S',
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            if ($request->hasFile('image')) {
                $data['url_imagen'] = $this->storeSliderImage($request->file('image'), $newId);
            }

            $slider = WebSlider::create($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Slide creado correctamente.',
                'result'  => $this->formatSlider($slider),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WebSlider crear: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el slide.',
            ], 500);
        }
    }

    public function eliminar(Request $request): JsonResponse
    {
        $request->validate(['id_slider' => 'required|integer']);

        $id = (int) $request->input('id_slider');
        if ($id === WebSlider::ID_BANNER_INTERNO) {
            return response()->json([
                'success' => false,
                'message' => 'El banner de páginas internas no se puede eliminar desde aquí.',
            ], 422);
        }

        $slider = WebSlider::find($id);
        if (! $slider) {
            return response()->json([
                'success' => false,
                'message' => 'Slide no encontrado.',
            ], 404);
        }

        DB::beginTransaction();
        try {
            $this->deletePublicImage($slider->url_imagen);
            $slider->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Slide eliminado correctamente.',
                'result'  => 1,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WebSlider eliminar: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el slide.',
            ], 500);
        }
    }

    /**
     * Guarda en public/storage_/slider/slider_{id}_{fecha}_{aleatorio}.{ext}.
     */
    /**
     * Genera un nombre de archivo único por subida (fecha+hora+id aleatorio) en vez de un
     * nombre fijo por slider. Así cada actualización de imagen produce una URL nueva y el
     * navegador no puede servir una versión cacheada de la imagen anterior.
     */
    private function storeSliderImage(UploadedFile $file, int $sliderId, ?string $oldPath = null): string
    {
        $path = \App\Support\CmsImageStorage::store($file, 'slider');
        $this->deletePublicImage($oldPath);

        return $path;
    }

    private function deletePublicImage(?string $path): void
    {
        if (empty($path) || preg_match('#^https?://#i', $path)) {
            return;
        }

        $normalized = ltrim(str_replace('\\', '/', trim($path)), '/');
        if ($normalized === '') {
            return;
        }

        \App\Support\CmsImageStorage::deleteIfOwned($normalized);
    }

    private function formatSlider(WebSlider $slider): array
    {
        return $this->formatSliderRow($slider->toArray());
    }

    private function formatSliderRow(array $row): array
    {
        $url = CmsStorageUrl::forApi($row['url_imagen'] ?? null);
        $row['url_imagen_publica'] = $this->withCacheBuster($url, $row['url_imagen'] ?? null);

        return $row;
    }

    /**
     * El archivo se guarda siempre con el mismo nombre (slider_{id}.ext), así que subir una
     * imagen nueva no cambia la URL. Sin un parámetro de versión, el navegador reutiliza la
     * imagen anterior desde su caché en vez de pedir la nueva. Se ancla al mtime real del
     * archivo para que solo cambie cuando el archivo cambia.
     */
    private function withCacheBuster(?string $url, ?string $relativePath): ?string
    {
        if (! $url || ! $relativePath) {
            return $url;
        }

        $absolute = public_path(ltrim($relativePath, '/'));
        if (! is_file($absolute)) {
            return $url;
        }

        $version = filemtime($absolute);

        return $url . (str_contains($url, '?') ? '&' : '?') . 'v=' . $version;
    }
}

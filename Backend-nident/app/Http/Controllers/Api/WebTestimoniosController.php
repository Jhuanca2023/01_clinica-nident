<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebTestimonios;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebTestimoniosController extends Controller
{
    public function listar(): JsonResponse
    {
        $items = WebTestimonios::orderBy('orden', 'ASC')->get()
            ->map(function ($t) {
                // Agrega URL pública absoluta para que el admin React resuelva la imagen
                if ($t->url_avatar) {
                    $t->url_avatar_publica = url($t->url_avatar);
                }
                if ($t->url_captura) {
                    $t->url_captura_publica = url($t->url_captura);
                }
                return $t;
            });

        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => $items,
        ]);
    }

    public function crear(Request $request): JsonResponse
    {
        $request->validate([
            'tipo'           => 'nullable|in:texto,captura',
            'nombre'         => 'nullable|string|max:150',
            'subtitulo'      => 'nullable|string|max:200',
            'testimonio'     => 'nullable|string',
            'calificacion'   => 'nullable|numeric|min:0|max:5',
            'badge_seccion'  => 'nullable|string|max:200',
            'seccion_titulo' => 'nullable|string|max:500',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'captura'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
        ]);

        $tipo = $request->input('tipo', WebTestimonios::TIPO_TEXTO);
        $ref = WebTestimonios::orderBy('orden')->first();

        DB::beginTransaction();
        try {
            $maxOrden = (int) (WebTestimonios::max('orden') ?? 0);

            $data = [
                'tipo'           => $tipo,
                'badge_seccion'  => $request->input('badge_seccion', $ref?->badge_seccion),
                'seccion_titulo' => $request->input('seccion_titulo', $ref?->seccion_titulo),
                'seccion_eyebrow' => $request->input('seccion_eyebrow'),
                'orden'          => $maxOrden + 1,
                'Activo'         => 'S',
            ];

            if ($tipo === WebTestimonios::TIPO_CAPTURA) {
                if (! $request->hasFile('captura')) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Debe subir una imagen de captura (WhatsApp u otra evidencia).',
                    ], 422);
                }

                $data['nombre']       = $request->input('nombre') ?: 'Evidencia WhatsApp';
                $data['subtitulo']    = $request->subtitulo;
                $data['testimonio']   = null;
                $data['calificacion'] = $request->calificacion ?? 5.0;
                $data['url_captura']  = \App\Support\CmsImageStorage::store($request->file('captura'), 'testimonios/capturas');
                $data['url_avatar']   = null;
            } else {
                $data['nombre']       = $request->input('nombre') ?: 'Cliente Satisfecho';
                $data['subtitulo']    = $request->subtitulo;
                $data['testimonio']   = $request->testimonio;
                $data['calificacion'] = $request->calificacion ?? 4.8;

                if ($request->hasFile('image')) {
                    $data['url_avatar'] = \App\Support\CmsImageStorage::store($request->file('image'), 'testimonios');
                }
            }

            $registro = WebTestimonios::create($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Testimonio creado correctamente.',
                'result'  => $registro,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('stderr')->error('Error al crear testimonio: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Hubo un error al crear el registro.',
            ], 500);
        }
    }

    public function eliminar(Request $request): JsonResponse
    {
        $request->validate([
            'id_testimonio' => 'required|exists:web_testimonios,id_testimonio',
        ]);

        $registro = WebTestimonios::findOrFail($request->id_testimonio);

        DB::beginTransaction();
        try {
            $this->eliminarArchivo($registro->url_avatar);
            $this->eliminarArchivo($registro->url_captura);
            $registro->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Testimonio eliminado correctamente.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('stderr')->error('Error al eliminar testimonio: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Hubo un error al eliminar el registro.',
            ], 500);
        }
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'id_testimonio'  => 'required|exists:web_testimonios,id_testimonio',
            'tipo'           => 'nullable|in:texto,captura',
            'nombre'         => 'nullable|string|max:150',
            'subtitulo'      => 'nullable|string|max:200',
            'testimonio'     => 'nullable|string',
            'calificacion'   => 'nullable|numeric|min:0|max:5',
            'badge_seccion'  => 'nullable|string|max:200',
            'seccion_titulo' => 'nullable|string|max:500',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'captura'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
        ]);

        $registro = WebTestimonios::findOrFail($request->id_testimonio);
        $tipo = $request->input('tipo', $registro->tipo ?? WebTestimonios::TIPO_TEXTO);
        $tipoAnterior = $registro->tipo ?? WebTestimonios::TIPO_TEXTO;

        DB::beginTransaction();
        try {
            $data = [
                'tipo'           => $tipo,
                'updated_at'     => now(),
            ];
            foreach (['badge_seccion', 'seccion_titulo'] as $campo) {
                if ($request->has($campo)) {
                    $data[$campo] = $request->input($campo);
                }
            }
            if ($request->has('Activo') && in_array($request->Activo, ['S', 'N'], true)) {
                $data['Activo'] = $request->Activo;
            }

            if ($tipo === WebTestimonios::TIPO_CAPTURA) {
                $data['nombre']       = $request->input('nombre') ?: 'Evidencia WhatsApp';
                $data['subtitulo']    = $request->subtitulo;
                $data['testimonio']   = null;
                $data['calificacion'] = $request->calificacion ?? $registro->calificacion ?? 5.0;

                if ($request->hasFile('captura')) {
                    $this->eliminarArchivo($registro->url_captura);
                    $data['url_captura'] = \App\Support\CmsImageStorage::store($request->file('captura'), 'testimonios/capturas');
                } elseif ($tipoAnterior !== WebTestimonios::TIPO_CAPTURA && empty($registro->url_captura)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Debe subir una imagen de captura (WhatsApp u otra evidencia).',
                    ], 422);
                }

                if ($tipoAnterior === WebTestimonios::TIPO_TEXTO) {
                    $data['url_avatar'] = $registro->url_avatar;
                }
            } else {
                $data['nombre']       = $request->input('nombre') ?: 'Cliente Satisfecho';
                $data['subtitulo']    = $request->subtitulo;
                $data['testimonio']   = $request->testimonio;
                $data['calificacion'] = $request->calificacion ?? 5.0;

                if ($request->hasFile('image')) {
                    $this->eliminarArchivo($registro->url_avatar);
                    $data['url_avatar'] = \App\Support\CmsImageStorage::store($request->file('image'), 'testimonios');
                }

                if ($tipoAnterior === WebTestimonios::TIPO_CAPTURA) {
                    $this->eliminarArchivo($registro->url_captura);
                    $data['url_captura'] = null;
                }
            }

            $registro->update($data);
            $registro->refresh();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Testimonio actualizado correctamente.',
                'result'  => $registro,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('stderr')->error('Error al actualizar testimonio: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Hubo un error al actualizar el registro.',
            ], 500);
        }
    }

    public function actualizarSeccion(Request $request): JsonResponse
    {
        $request->validate([
            'badge_seccion'  => 'nullable|string|max:200',
            'seccion_titulo' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            WebTestimonios::query()->update([
                'badge_seccion'  => $request->badge_seccion,
                'seccion_titulo' => $request->seccion_titulo,
                'updated_at'     => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Encabezado actualizado en todos los registros.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('stderr')->error('Error al actualizar sección testimonios: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Hubo un error al actualizar la sección.',
            ], 500);
        }
    }

    /** Reseñas del carrusel de Rossety (incluye las ocultas) y su encabezado. */
    public function listarRossety(): JsonResponse
    {
        $sec = \App\Support\RossetyTestimonios::seccion(false);

        return response()->json(['success' => true, 'message' => 'OK', 'result' => $sec]);
    }

    public function actualizarSeccionRossety(Request $request): JsonResponse
    {
        $request->validate(['eyebrow' => 'nullable|string|max:200', 'titulo' => 'nullable|string|max:500']);
        WebTestimonios::where('badge_seccion', \App\Support\RossetyTestimonios::SECCION)->update([
            'seccion_eyebrow' => $request->eyebrow,
            'seccion_titulo'  => $request->titulo,
            'updated_at'      => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Encabezado actualizado.']);
    }

    /** Recibe ids en el orden deseado. */
    public function ordenarRossety(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        foreach (array_values($request->ids) as $i => $id) {
            WebTestimonios::where('id_testimonio', $id)->update(['orden' => $i + 1]);
        }

        return response()->json(['success' => true, 'message' => 'Orden actualizado.']);
    }

    private function eliminarArchivo(?string $ruta): void
    {
        \App\Support\CmsImageStorage::deleteIfOwned($ruta);
    }
}

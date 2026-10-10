<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebClienteEstadistica;
use App\Models\WebClienteLogo;
use App\Models\WebClienteSeccion;
use App\Support\CmsStorageUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebClienteController extends Controller
{
    public function obtener_seccion(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Obtener sección',
            'result'  => WebClienteSeccion::firstOrCreate(['id' => 1]),
        ]);
    }

    public function actualizar_seccion(Request $request): JsonResponse
    {
        $seccion = WebClienteSeccion::firstOrCreate(['id' => 1]);
        $seccion->update([
            'tag'         => $request->input('tag', $seccion->tag),
            'titulo'      => $request->input('titulo', $seccion->titulo),
            'descripcion' => $request->input('descripcion', $seccion->descripcion),
            'cta_texto'   => $request->input('cta_texto', $seccion->cta_texto),
            'cta_url'     => $request->input('cta_url', $seccion->cta_url),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sección actualizada.',
            'result'  => $seccion->fresh(),
        ]);
    }

    public function listar_estadisticas(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar estadísticas',
            'result'  => WebClienteEstadistica::where('Activo', 'S')->orderBy('orden')->get(),
        ]);
    }

    public function crear_estadistica(Request $request): JsonResponse
    {
        $request->validate([
            'valor'    => 'required|string|max:80',
            'etiqueta' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $maxOrden = WebClienteEstadistica::max('orden') ?? 0;
            $registro = WebClienteEstadistica::create([
                'valor'       => $request->valor,
                'etiqueta'    => $request->etiqueta,
                'icono_clase' => $request->input('icono_clase'),
                'orden'       => $maxOrden + 1,
                'Activo'      => 'S',
            ]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estadística creada correctamente.',
                'result'  => $registro,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear el registro.'], 500);
        }
    }

    public function actualizar_estadistica(Request $request): JsonResponse
    {
        $request->validate([
            'id'       => 'required|exists:web_cliente_estadistica,id',
            'valor'    => 'nullable|string|max:80',
            'etiqueta' => 'nullable|string|max:255',
        ]);

        $registro = WebClienteEstadistica::findOrFail($request->id);
        $registro->update([
            'valor'       => $request->input('valor', $registro->valor),
            'etiqueta'    => $request->input('etiqueta', $registro->etiqueta),
            'icono_clase' => $request->input('icono_clase', $registro->icono_clase),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estadística actualizada correctamente.',
            'result'  => $registro->fresh(),
        ]);
    }

    public function eliminar_estadistica(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|exists:web_cliente_estadistica,id']);

        DB::beginTransaction();
        try {
            WebClienteEstadistica::findOrFail($request->id)->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Estadística eliminada correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar el registro.'], 500);
        }
    }

    public function listar_logos(): JsonResponse
    {
        $result = WebClienteLogo::where('Activo', 'S')
            ->orderBy('orden')
            ->get()
            ->map(function (WebClienteLogo $row) {
                $arr = $row->toArray();
                $arr['url_imagen_publica'] = CmsStorageUrl::forApi($row->url_imagen);

                return $arr;
            })
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'message' => 'Listar logos',
            'result'  => $result,
        ]);
    }

    public function crear_logo(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'nullable|string|max:200',
            'image'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:6144',
        ]);

        DB::beginTransaction();
        try {
            $maxOrden = WebClienteLogo::max('orden') ?? 0;
            $data = [
                'nombre'     => $request->input('nombre'),
                'url_enlace' => $request->input('url_enlace'),
                'orden'      => $maxOrden + 1,
                'Activo'     => 'S',
            ];

            if ($request->hasFile('image')) {
                $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'cliente_logo');
            }

            $registro = WebClienteLogo::create($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Logo creado correctamente.',
                'result'  => $registro,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WebCliente crear_logo: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear el registro.'], 500);
        }
    }

    public function actualizar_logo(Request $request): JsonResponse
    {
        $request->validate([
            'id'     => 'required|exists:web_cliente_logo,id',
            'image'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:6144',
        ]);

        $registro = WebClienteLogo::findOrFail($request->id);

        DB::beginTransaction();
        try {
            $data = [
                'nombre'     => $request->input('nombre', $registro->nombre),
                'url_enlace' => $request->input('url_enlace', $registro->url_enlace),
                'updated_at' => now(),
            ];

            if ($request->hasFile('image')) {
                $this->eliminarArchivo($registro->url_imagen);
                $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'cliente_logo');
            }

            $registro->update($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Logo actualizado correctamente.',
                'result'  => $registro->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar el registro.'], 500);
        }
    }

    public function eliminar_logo(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|exists:web_cliente_logo,id']);
        $registro = WebClienteLogo::findOrFail($request->id);

        DB::beginTransaction();
        try {
            $this->eliminarArchivo($registro->url_imagen);
            $registro->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Logo eliminado correctamente.']);
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

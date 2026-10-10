<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebConfianzaItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class WebConfianzaItemController extends Controller
{
    public function listar(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => WebConfianzaItem::where('Activo', 'S')->orderBy('orden')->get(),
        ]);
    }

    public function crear(Request $request): JsonResponse
    {
        $request->validate([
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ]);

        DB::beginTransaction();
        try {
            $maxOrden = WebConfianzaItem::max('orden') ?? 0;
            $data = [
                'titulo'      => $request->titulo,
                'descripcion' => $request->input('descripcion'),
                'url_enlace'  => $request->input('url_enlace'),
                'orden'       => $maxOrden + 1,
                'Activo'      => 'S',
            ];

            if ($request->hasFile('image')) {
                $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'confianza_item');
            }

            $registro = WebConfianzaItem::create($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item creado correctamente.',
                'result'  => $registro,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WebConfianzaItem crear: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear el registro.'], 500);
        }
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'id'          => 'required|exists:web_confianza_item,id',
            'titulo'      => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ]);

        $registro = WebConfianzaItem::findOrFail($request->id);

        DB::beginTransaction();
        try {
            $data = [
                'titulo'      => $request->input('titulo', $registro->titulo),
                'descripcion' => $request->input('descripcion', $registro->descripcion),
                'url_enlace'  => $request->input('url_enlace', $registro->url_enlace),
                'updated_at'  => now(),
            ];

            if ($request->hasFile('image')) {
                $this->eliminarArchivo($registro->url_imagen);
                $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'confianza_item');
            }

            $registro->update($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item actualizado correctamente.',
                'result'  => $registro->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar el registro.'], 500);
        }
    }

    public function eliminar(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|exists:web_confianza_item,id']);
        $registro = WebConfianzaItem::findOrFail($request->id);

        DB::beginTransaction();
        try {
            $this->eliminarArchivo($registro->url_imagen);
            $registro->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Item eliminado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar el registro.'], 500);
        }
    }

    public function seccionObtener(): JsonResponse
    {
        if (! Schema::hasTable('web_porque_elejirnos')) {
            return response()->json(['success' => true, 'message' => 'Sección no configurada', 'result' => null]);
        }

        $row = DB::table('web_porque_elejirnos')->where('id_porque', 1)->first();

        return response()->json([
            'success' => true,
            'message' => 'Sección obtenida',
            'result'  => $row,
        ]);
    }

    public function seccionActualizar(Request $request): JsonResponse
    {
        $request->validate([
            'titulo'           => 'nullable|string|max:300',
            'descripcion'      => 'nullable|string',
            'url_imagen_fondo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ]);

        if (! Schema::hasTable('web_porque_elejirnos')) {
            return response()->json(['success' => false, 'message' => 'Tabla de sección no disponible.'], 404);
        }

        $row = DB::table('web_porque_elejirnos')->where('id_porque', 1)->first();
        if (! $row) {
            return response()->json(['success' => false, 'message' => 'Sección no encontrada.'], 404);
        }

        DB::beginTransaction();
        try {
            $data = [
                'titulo'      => $request->input('titulo', $row->titulo),
                'descripcion' => $request->input('descripcion', $row->descripcion),
                'updated_at'  => now(),
            ];

            if ($request->hasFile('url_imagen_fondo')) {
                $this->eliminarArchivo($row->url_imagen_fondo ?? null);
                $data['url_imagen_fondo'] = \App\Support\CmsImageStorage::store($request->file('url_imagen_fondo'), 'confianza_seccion');
            }

            DB::table('web_porque_elejirnos')->where('id_porque', 1)->update($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sección actualizada correctamente.',
                'result'  => DB::table('web_porque_elejirnos')->where('id_porque', 1)->first(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WebConfianzaItem seccionActualizar: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al actualizar la sección.'], 500);
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

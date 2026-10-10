<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebCarrusel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebCarruselController extends Controller
{
    public function listar(): JsonResponse
    {
        $result = WebCarrusel::where('Activo', 'S')
            ->orderBy('orden')
            ->get()
            ->map(function (WebCarrusel $row) {
                $arr = $row->toArray();
                $path = $row->url_imagen;
                $arr['url_imagen_publica'] = $path
                    ? (preg_match('#^https?://#i', $path) ? $path : asset($path))
                    : null;

                return $arr;
            })
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => $result,
        ]);
    }

    public function crear(Request $request): JsonResponse
    {
        $request->validate([
            'label' => 'nullable|string|max:200',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ]);

        DB::beginTransaction();
        try {
            $maxOrden = WebCarrusel::max('orden') ?? 0;

            $data = [
                'label'   => $request->input('label', 'Nueva imagen'),
                'orden'   => $maxOrden + 1,
                'Activo'  => 'S',
            ];

            if ($request->hasFile('image')) {
                $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'carrusel');
            }

            $registro = WebCarrusel::create($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Imagen creada correctamente.',
                'result'  => $registro,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('stderr')->error('Error al crear carrusel: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear el registro.'], 500);
        }
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'id_carrusel'  => 'required|exists:web_carrusel,id_carrusel',
            'label'        => 'nullable|string|max:200',
            'remove_image' => 'nullable|boolean',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ]);

        $registro = WebCarrusel::findOrFail($request->id_carrusel);

        DB::beginTransaction();
        try {
            $data = [
                'label'      => $request->input('label', $registro->label),
                'updated_at' => now(),
            ];

            if ($request->boolean('remove_image')) {
                $this->eliminarArchivo($registro->url_imagen);
                $data['url_imagen'] = null;
            } elseif ($request->hasFile('image')) {
                $this->eliminarArchivo($registro->url_imagen);
                $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'carrusel');
            }

            $registro->update($data);
            $registro->refresh();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Imagen actualizada correctamente.',
                'result'  => $registro,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('stderr')->error('Error al actualizar carrusel: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar el registro.'], 500);
        }
    }

    public function eliminar(Request $request): JsonResponse
    {
        $request->validate(['id_carrusel' => 'required|exists:web_carrusel,id_carrusel']);

        $registro = WebCarrusel::findOrFail($request->id_carrusel);

        DB::beginTransaction();
        try {
            $this->eliminarArchivo($registro->url_imagen);
            $registro->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Imagen eliminada correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('stderr')->error('Error al eliminar carrusel: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el registro.'], 500);
        }
    }

    public function reordenar(Request $request): JsonResponse
    {
        $request->validate(['items' => 'required|array', 'items.*.id_carrusel' => 'required|integer', 'items.*.orden' => 'required|integer']);

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                WebCarrusel::where('id_carrusel', $item['id_carrusel'])->update(['orden' => $item['orden']]);
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Orden actualizado.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al reordenar.'], 500);
        }
    }

    private function eliminarArchivo(?string $ruta): void
    {
        \App\Support\CmsImageStorage::deleteIfOwned($ruta);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebContadoresSeccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebContadoresSeccionController extends Controller
{
    public function obtener(): JsonResponse
    {
        $data = WebContadoresSeccion::firstOrCreate(['id' => 1], ['Activo' => 'S']);

        return response()->json([
            'success' => true,
            'message' => 'Obtener sección',
            'result'  => $data,
        ]);
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ]);

        DB::beginTransaction();
        try {
            $data = WebContadoresSeccion::firstOrCreate(['id' => 1], ['Activo' => 'S']);
            $update = ['updated_at' => now()];

            if ($request->hasFile('image')) {
                $this->eliminarArchivo($data->url_imagen_fondo);
                $update['url_imagen_fondo'] = \App\Support\CmsImageStorage::store($request->file('image'), 'contadores_seccion');
            }

            $data->update($update);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sección actualizada correctamente.',
                'result'  => $data->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WebContadoresSeccion actualizar: ' . $e->getMessage());
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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebLookbook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebLookbookController extends Controller
{
    public function obtener(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'result'  => WebLookbook::find(1),
        ]);
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
            'image_lateral' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ]);

        DB::beginTransaction();
        try {
            $row = WebLookbook::firstOrCreate(['id' => 1]);
            $data = $request->only([
                'subtitulo', 'titulo', 'titulo_accent', 'descripcion',
                'btn_texto', 'btn_url', 'Activo',
                'imagen_lateral_texto', 'imagen_lateral_cta', 'imagen_lateral_url',
            ]);

            if ($request->hasFile('image')) {
                $this->deleteFile($row->url_imagen);
                $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'lookbook');
            }
            if ($request->hasFile('image_lateral')) {
                $this->deleteFile($row->url_imagen_lateral);
                $data['url_imagen_lateral'] = \App\Support\CmsImageStorage::store($request->file('image_lateral'), 'lookbook');
            }

            $row->update($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lookbook actualizado.',
                'result'  => $row->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WebLookbook actualizar: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al guardar.'], 500);
        }
    }

    private function deleteFile(?string $path): void
    {
        \App\Support\CmsImageStorage::deleteIfOwned($path);
    }
}

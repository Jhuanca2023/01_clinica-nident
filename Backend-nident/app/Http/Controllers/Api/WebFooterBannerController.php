<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebFooterBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebFooterBannerController extends Controller
{
    public function obtener(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'result'  => WebFooterBanner::find(1),
        ]);
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
        ]);

        DB::beginTransaction();
        try {
            $row = WebFooterBanner::firstOrCreate(['id' => 1]);
            $data = $request->only(['subtitulo', 'titulo', 'btn_texto', 'btn_url', 'Activo']);

            if ($request->hasFile('image')) {
                $disk = Storage::disk('public_imagenes');
                if ($row->url_imagen && str_starts_with($row->url_imagen, 'storage_/') && $disk->exists($row->url_imagen)) {
                    $disk->delete($row->url_imagen);
                }
                $data['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'footer_banner');
            }

            $row->update($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Banner footer actualizado.',
                'result'  => $row->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WebFooterBanner actualizar: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al guardar.'], 500);
        }
    }
}

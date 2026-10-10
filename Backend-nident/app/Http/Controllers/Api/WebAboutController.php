<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebAbout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class WebAboutController extends Controller
{
    public function obtener(Request $request): JsonResponse
    {
        $id = $request->id;
        $result = DB::select('select * from web_about where id_about = ?', [$id]);

        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result'  => $result,
        ]);
    }

    public function listar(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => WebAbout::orderBy('id_about', 'ASC')->get(),
        ]);
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'id_about'    => 'required|exists:web_about,id_about',
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'image2'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $webAbout = WebAbout::findOrFail($request->id_about);

        DB::beginTransaction();

        try {
            $dataToUpdate = ['updated_at' => now()];

            foreach (['titulo', 'descripcion', 'subtitulo', 'lema_label', 'lema_texto', 'texto_extendido', 'btn_texto', 'btn_url'] as $field) {
                if ($request->has($field)) {
                    $dataToUpdate[$field] = $request->input($field);
                }
            }

            // El admin edita lema_label; la web pública usa subtitulo en el partial.
            if ($request->has('lema_label') && !$request->has('subtitulo')) {
                $dataToUpdate['subtitulo'] = $request->input('lema_label');
            }

            if ($request->has('url_video') && Schema::hasColumn('web_about', 'url_video')) {
                $dataToUpdate['url_video'] = $request->input('url_video');
            }

            if ($request->has('mostrar_video') && Schema::hasColumn('web_about', 'mostrar_video')) {
                $dataToUpdate['mostrar_video'] = $request->boolean('mostrar_video') ? 1 : 0;
            }

            if ($request->has('Activo')) {
                $dataToUpdate['Activo'] = $request->input('Activo');
            }

            if ($request->hasFile('image')) {
                \App\Support\CmsImageStorage::deleteIfOwned($webAbout->url_imagen);
                $dataToUpdate['url_imagen'] = \App\Support\CmsImageStorage::store($request->file('image'), 'about');
            }

            if ($request->hasFile('image2') && Schema::hasColumn('web_about', 'url_imagen_2')) {
                \App\Support\CmsImageStorage::deleteIfOwned($webAbout->url_imagen_2);
                $dataToUpdate['url_imagen_2'] = \App\Support\CmsImageStorage::store($request->file('image2'), 'about');
            }

            $webAbout->update($dataToUpdate);
            $webAbout->refresh();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registro actualizado correctamente.',
                'result'  => $webAbout,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar web_about: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Hubo un error al actualizar el registro.',
            ], 500);
        }
    }
}

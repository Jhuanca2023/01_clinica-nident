<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ClienteResource;
use App\Models\OpePedidos;
use App\Models\ProductoImagen;
use App\Models\ProductoFichaTecnica;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;

class FileController extends Controller
{ 
    /**
     * @OA\Get(
     *     path="/storage/{filename}",
     *     tags={"File Management"},
     *     summary="Servir archivo (comprobante) desde storage",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="filename", in="path", required=true, @OA\Schema(type="string"), example="payment_123.pdf"),
     *     @OA\Response(response=200, description="Archivo cargado satisfactoriamente"),
     *     @OA\Response(response=404, description="Archivo no encontrado")
     * )
     */
    public function show($filename)
    {
        // Log::channel('stderr')->info("Aqui".json_encode($filename));
        if (!Storage::exists("public/comprobantes/{$filename}")) {
            abort(404);
        }
        return response()->file(storage_path("app/public/comprobantes/{$filename}"));
        //  return response()->file(storage_path("app/public/comprobantes/payment__1747957021.pdf"));
    }

    /**
     * @OA\Get(
     *     path="/storage_ckeditor/{filename}",
     *     tags={"File Management"},
     *     summary="Servir imagen de editor de texto (CKEditor)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="filename", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Imagen cargada"),
     *     @OA\Response(response=404, description="Imagen no encontrada")
     * )
     */
    public function show_ckeditor($filename)
    {
        if (!Storage::exists("public/ckeditor/{$filename}")) {
            abort(404);
        }
        return response()->file(storage_path("app/public/ckeditor/{$filename}"));
    }

    /**
     * @OA\Get(
     *     path="/storage_reclamo/{filename}",
     *     tags={"File Management"},
     *     summary="Servir archivo de reclamo (Público)",
     *     description="Este endpoint es público para permitir la previsualización de adjuntos en el libro de reclamaciones.",
     *     @OA\Parameter(name="filename", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Archivo de reclamo cargado"),
     *     @OA\Response(response=404, description="Archivo no encontrado")
     * )
     */
    public function show_reclamo($filename)
    {
        if (!Storage::exists("public/reclamos/{$filename}")) {
            abort(404);
        }
        return response()->file(storage_path("app/public/reclamos/{$filename}"));
    }
}

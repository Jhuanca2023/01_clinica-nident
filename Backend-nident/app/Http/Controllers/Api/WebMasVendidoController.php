<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\WebMasVendido;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WebMasVendidoController extends Controller
{

    /**
     * @OA\Get(
     *     path="/web/mas_vendido/listar",
     *     tags={"Sitio Web"},
     *     summary="Listar productos destacados como 'Más Vendidos'",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de productos", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar()
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => WebMasVendido::orderBy('id_mas_vendido', 'ASC')->get()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/web/mas_vendido/actualizar",
     *     tags={"Sitio Web"},
     *     summary="Actualizar producto destacado (soporta imagen)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"id_mas_vendido","titulo"},
     *                 @OA\Property(property="id_mas_vendido", type="integer", example=1),
     *                 @OA\Property(property="titulo", type="string"),
     *                 @OA\Property(property="descripcion", type="string"),
     *                 @OA\Property(property="precio", type="number", format="float"),
     *                 @OA\Property(property="Activo", type="string", enum={"S","N"}),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Producto actualizado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request) : JsonResponse
    {
        // Log::channel('stderr')->info($request->id_mas_vendido);
        if($request->hasFile('image')){
            $result = WebMasVendido::where('id_mas_vendido',$request->id_mas_vendido)->get();
        
            if(!empty($result[0]->url_imagen)){
                \App\Support\CmsImageStorage::deleteIfOwned($result[0]->url_imagen);
            }
       
            $file = $request->file("image");           
            $ext = $file->getClientOriginalExtension();
            $filename_ = time().'.'.$ext; 
            $filename = \App\Support\CmsImageStorage::store($file, 'masvendido');
            WebMasVendido::where('id_mas_vendido',$request->id_mas_vendido)->update(['url_imagen'=> $filename]);
        } 

        DB::beginTransaction();
        
        try {

            $id = $request->input('id_mas_vendido');
            $result = WebMasVendido::where('id_mas_vendido',$id)
                    ->update([
                        'titulo'        => $request->titulo,
                        'descripcion'   => $request->descripcion,
                        'precio'   => $request->precio,

                        'updated_at'    => date("Y-m-d H:i:s"),
                        'Activo'        => $request->Activo,
                        ]);

            DB::commit();
        } catch (\Exception $e) {
            Log::channel('stderr')->info($e);
            DB::rollback();
            // something went wrong
        }
       
        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado',
            'result'  => $result
        ]);

  
    }

}
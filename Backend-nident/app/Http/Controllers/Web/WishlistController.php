<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Session;
use RealRashid\SweetAlert\Facades\Alert;

class WishlistController  extends Controller
{

    public function web_wishlist()
    {
        if (Auth::check()) {
            $listProductIndex = \App\Models\Producto::enhanceList(
                DB::select('CALL USP_WEB_PRODUCTO_LISTAR_DESEOS(?)', [Auth::user()->id])
            );
            $footerData = DB::table('web_footer')->where('id_footer', 1)->where('Activo', 'S')->get();
            $footerCorp = $footerData->first();
            return view('web.pages.web_wishlist')->with(compact('listProductIndex', 'footerData', 'footerCorp'));
        } else {
            //$listProductIndex = [];
            // Alert::success('Success Title', 'Success Message');
            return redirect()->intended('/')->with('messagePrimeroDebeIniciarSesion', '¡Primero debe iniciar sesión!');
        }
    }


    public function web_wishlist_agregar(Request $request)
    {
        $id_producto_ = $request->input('id_producto');

        if (Auth::check()) {
            // Verificar si ya existe el favorito para evitar duplicados
            $favoritoExistente = DB::table('web_favoritos')
                ->where('id_usuario', Auth::user()->id)
                ->where('id_producto', $id_producto_)
                ->first();

            if ($favoritoExistente) {
                return response()->json([
                    'data' => 'Este producto ya está en tus favoritos',
                    'state' => 'error'
                ]);
            }

            DB::beginTransaction();
            try {
                $insertLastId = DB::table('web_favoritos')->insertGetId([
                    'id_usuario'   => Auth::user()->id,
                    'id_producto'  => $id_producto_,
                    'created_at'   => now(),
                    'updated_at'   => now()
                ]);

                DB::commit();

                // Generar HTML para el estado "agregado"
                $isHtml = '<a href="javascript:void(0)" id="idActualizarDivFavorito' . $id_producto_ . '" class="clsBotonEliminarFavorito clsActionDelete" onclick="clsActionDelete(\'ESTADO_GALLERY_PRODUCT\',' . $id_producto_ . ',' . Auth::user()->id . ')"><i class="fa-solid fa-check"></i></a>';

                return response()->json([
                    'data'       => $isHtml,
                    'mensaje'    => 'Se agregó a Lista de deseos!',
                    'state'      => 'ok',
                    'id_usuario' => Auth::user()->id
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'data' => $e->getMessage(),
                    'state' => 'error'
                ]);
            }
        } else {
            return response()->json([
                'data' => 'Debe iniciar sesión para agregar favoritos',
                'state' => 'login'
            ]);
        }
    }


    public function web_wishlist_eliminar(Request $request)
    {
        $id_producto_ = $request->input('id_producto');
        $id_usuario_ = $request->input('id_usuario');
        $id_favorito_ = $request->input('id_favorito');

        DB::beginTransaction();
        try {
            DB::table('web_favoritos')
                ->where('id_usuario', $id_usuario_)->where('id_producto', $id_producto_)->delete();
            DB::commit();

            // Generar HTML para el estado "no agregado"
            $isHtml = '<a href="javascript:void(0)" id="idActualizarDivFavorito' . $id_producto_ . '" type="button" class="clsBotonAgragarFavorito clsActionAdd" onclick="clsActionAdd(\'ESTADO_GALLERY_PRODUCT\',' . $id_producto_ . ')"><i class="fa-regular fa-heart"></i></a>';

            return response()->json([
                'data' => $isHtml,
                'mensaje' => 'Se eliminó de Lista de deseos!',
                'state' => 'ok'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            return json_encode(['data' =>  $e->getMessage(), 'state' => 'error']);
        }
    }
}

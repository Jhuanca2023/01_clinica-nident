<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\BannerPopularController;
use App\Http\Controllers\Api\CategoriaPopularController;
use App\Traits\ChecksAdminRole;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OfertaDelDia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Session;
use App\Models\Producto;
use App\Models\MetadatosPagina;
use App\Models\WebFooter;
use App\Models\WebHeader;
use App\Models\WebPreguntasFrecuentes;
use App\Models\WebAbout;
use App\Models\WebAboutCaracteristica;
use App\Models\WebConfianzaItem;
use App\Models\WebContadores;
use App\Models\WebContadoresSeccion;
use App\Models\WebClienteLogo;
use App\Models\WebClienteSeccion;
use App\Models\WebLineaProducto;
use App\Models\WebLineaProductoSeccion;
use App\Models\WebProductoCatalogo;
use App\Models\WebProductoCatalogoSeccion;
use App\Models\WebProductoDestacado;
use App\Models\WebProductoDestacadoSeccion;
use App\Models\WebSlider;
use App\Models\WebSliderConfig;
use App\Models\WebVideo;
use App\Models\WebVideoSeccion;

class HomeController extends Controller
{
    use ChecksAdminRole;

    public function web_session_login()
    {
        return view('auth.login');
    }

    // ── Customizer: guardar estilos (solo admin) ──────────────────────────
    public function customizerGuardar(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
        }
        if (! $this->isAdminUser()) {
            return response()->json(['success' => false, 'message' => 'Sin permisos de administrador'], 403);
        }

        $styles = $request->input('styles');
        if (empty($styles)) {
            return response()->json(['success' => false, 'message' => 'No se recibieron estilos'], 422);
        }
        $json = is_array($styles) ? json_encode($styles, JSON_UNESCAPED_UNICODE) : (string) $styles;

        try {
            $footer = WebFooter::find(1);
            if (! $footer) {
                return response()->json(['success' => false, 'message' => 'Registro de footer no encontrado. Configure el footer primero.'], 404);
            }

            $footer->customizer_styles = $json;
            $footer->save();

            return response()->json(['success' => true, 'message' => 'Estilos guardados correctamente']);
        } catch (\Exception $e) {
            Log::error('CustomizerGuardar error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al guardar en la base de datos'], 500);
        }
    }

    // ── Customizer: reset (solo admin) ───────────────────────────────────
    public function customizerReset(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
        }
        if (! $this->isAdminUser()) {
            return response()->json(['success' => false, 'message' => 'Sin permisos de administrador'], 403);
        }

        try {
            $footer = WebFooter::find(1);
            if (! $footer) {
                return response()->json(['success' => false, 'message' => 'Registro de footer no encontrado'], 404);
            }

            $footer->customizer_styles = null;
            $footer->save();

            return response()->json(['success' => true, 'message' => 'Estilos restablecidos al original']);
        } catch (\Exception $e) {
            Log::error('CustomizerReset error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al restablecer estilos'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────

    public function web_shopDetail_lista()
    {
        return view('web.pages.web_shopDetail_lista');
    }

public function graciasPorLaCompra()
{
    $data = session('order_confirmation');

    if (!$data || \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($data['expires_at']))) {
        session()->forget('order_confirmation');
        return redirect('/');
    }

    $codigo_pedido = $data['codigo_pedido'];
    $total         = $data['total'];

    $result = DB::table('ope_pedidos')->where('codigo_pedido', $codigo_pedido)->first();
    if (!$result) {
        session()->forget('order_confirmation');
        return redirect('/');
    }

    $productos   = DB::table('ope_pedidos_producto')->where('id_pedido', $result->id_pedido)->get();
    $costo_envio = $result->pasarela_costo_envio;

    return view('web.pages.web_myAccount')->with(
        compact('codigo_pedido', 'total', 'result', 'productos', 'costo_envio'));
}

public function web_myAccount($codigo_pedido, $total)
{
    $result = DB::table('ope_pedidos')->where('codigo_pedido', $codigo_pedido)->first();
    if (!$result) {
        return redirect('/');
    }

    $productos = DB::table('ope_pedidos_producto')->where('id_pedido', $result->id_pedido)->get();
    $costo_envio = $result->pasarela_costo_envio; // Definir la variable primero

    return view('web.pages.web_myAccount')->with(
        compact(
            'codigo_pedido', 
            'total', 
            'result', 
            'productos',
            'costo_envio' // Sin asignación, solo el nombre
        ));
}


    public function web_gallery_list()
    {
        return view('web.pages.web_gallery_list');
    }

    public function web_gallery_2cols()
    {
        return view('web.pages.web_gallery_2cols');
    }

    public function web_gallery_4cols()
    {
        return view('web.pages.web_gallery_4cols');
    }

    public function web_ubicacion()
    {
        $metaData = MetadatosPagina::metaVigente('web_ubicacion');
        return view('web.pages.web_ubicacion',compact('metaData'));
    }


    /**
     * @deprecated Usar ProductoLiveSearchController::suggest (products.search).
     * Se mantiene por compatibilidad si algo llama al método antiguo.
     */
    public function searchProducts(Request $request)
    {
        return app(\App\Http\Controllers\Web\ProductoLiveSearchController::class)->suggest($request);
    }

    private function documentTerminos($id)
    {
        return DB::table('web_terminos')->whereIn('id_footer_document', [$id])->get();
    }

    public function web_politica_privacidad()
    {
        return redirect()->route('liam.privacidad', [], 301);
    }

    public function web_terminos_condiciones()
    {
        return redirect()->route('liam.terminos', [], 301);
    }

    public function web_devoluciones()
    {
        $metaData = MetadatosPagina::metaVigente('web_devoluciones');
        $terminosData = $this->documentTerminos(5);
        return view('web.pages.web_doc_garantia')->with(compact('terminosData','metaData'));
    }

    public function web_garantia()
    {
          $metaData = MetadatosPagina::metaVigente('web_garantia');
        $terminosData = $this->documentTerminos(6);
        return view('web.pages.web_doc_garantia')->with(compact('terminosData','metaData'));
    }

    public function web_servicio_cliente()
    {
        $metaData = MetadatosPagina::metaVigente('web_servicio_cliente');
        $terminosData = $this->documentTerminos(1);
        // return view('web.pages.web_doc_servicio_cliente')->with(compact('terminosData'));
        return view('web.pages.web_doc_servicio_cliente', compact('metaData', 'terminosData'));
    }

    public function web_delivery_informacion()
    {
         $metaData = MetadatosPagina::metaVigente('web_delivery_informacion');
        $terminosData = $this->documentTerminos(4);
        return view('web.pages.web_doc_delivery_informacion')->with(compact('terminosData','metaData'));
    }
}

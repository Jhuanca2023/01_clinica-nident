<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\LiamPageData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LiamVercceliController extends Controller
{
    private function noCacheView(string $view, array $data = [])
    {
        return response()
            ->view($view, $data)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function index()
    {
        return $this->noCacheView('web.pages.liam.index', LiamPageData::home());
    }

    public function about()
    {
        return $this->noCacheView('web.pages.liam.about', LiamPageData::about());
    }

    public function contacto()
    {
        return $this->noCacheView('web.pages.liam.contact', LiamPageData::contacto());
    }

    public function terminos()
    {
        return $this->noCacheView('web.pages.liam.legal', LiamPageData::legal('terminos'));
    }

    public function privacidad()
    {
        return $this->noCacheView('web.pages.liam.legal', LiamPageData::legal('privacidad'));
    }

    public function guiaTallas()
    {
        return $this->noCacheView('web.pages.liam.guia_tallas', LiamPageData::layout('tienda'));
    }

    public function reclamaciones()
    {
        return $this->noCacheView('web.pages.liam.legal', LiamPageData::legal('reclamaciones'));
    }

    public function thankYou()
    {
        $data = session('order_confirmation');

        if (! $data || \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($data['expires_at']))) {
            session()->forget('order_confirmation');

            return redirect()->route('home');
        }

        $codigo = $data['codigo_pedido'];
        $total  = $data['total'];
        $pedido = DB::table('ope_pedidos')->where('codigo_pedido', $codigo)->first();

        if (! $pedido) {
            session()->forget('order_confirmation');

            return redirect()->route('home');
        }

        $productos = DB::table('ope_pedidos_producto')->where('id_pedido', $pedido->id_pedido)->get();
        $productos = $this->enrichThankYouProductos($productos, (int) $pedido->id_pedido);

        return $this->noCacheView('web.pages.liam.thankyou', LiamPageData::thankYou([
            'metaData'      => null,
            'codigo_pedido' => $codigo,
            'total'         => $total,
            'pedido'        => $pedido,
            'productos'     => $productos,
            'costo_envio'   => $pedido->pasarela_costo_envio ?? 'gratis',
            'order_session' => $data,
        ]));
    }

    /**
     * Preferir imagen/nombre del snapshot repositorio_ (archivo copiado al crear el pedido).
     * La línea ope_pedidos_producto suele guardar rutas legacy (storage_cliente/…) que ya no existen.
     *
     * @param  Collection<int, object>|iterable<int, object>  $productos
     * @return Collection<int, object>
     */
    private function enrichThankYouProductos(iterable $productos, int $idPedido): Collection
    {
        $productos = collect($productos);
        if ($productos->isEmpty()) {
            return $productos;
        }

        $repoByLinea = collect();
        if (Schema::hasTable('repositorio_producto')) {
            $repos = DB::table('repositorio_producto')
                ->where('id_pedido', $idPedido)
                ->orderByDesc('id_repositorio_producto')
                ->get();

            // Pedidos sin snapshot aún: intentar generar si existe el helper
            if ($repos->isEmpty() && class_exists(\App\Support\PedidoProductoRepositorio::class)) {
                \App\Support\PedidoProductoRepositorio::snapshotPedidoCompleto($idPedido);
                $repos = DB::table('repositorio_producto')
                    ->where('id_pedido', $idPedido)
                    ->orderByDesc('id_repositorio_producto')
                    ->get();
            }

            $repoByLinea = $repos->keyBy(fn ($r) => (int) ($r->id_pedido_producto ?? 0));
        }

        foreach ($productos as $p) {
            $repo = $repoByLinea->get((int) ($p->id_pedido_producto ?? 0));
            $imgRaw = '';
            if ($repo) {
                $imgRaw = trim((string) ($repo->url_imagen ?? ''));
                if (! empty($repo->pro_nombre_linea)) {
                    $p->pro_nombre = $repo->pro_nombre_linea;
                } elseif (! empty($repo->nombre) && empty($p->pro_nombre)) {
                    $p->pro_nombre = $repo->nombre;
                }
            }
            if ($imgRaw === '') {
                $imgRaw = trim((string) ($p->pro_url_imagen ?? ''));
            }
            $p->pro_url_imagen = LiamPageData::img($imgRaw);
        }

        return $productos;
    }
}

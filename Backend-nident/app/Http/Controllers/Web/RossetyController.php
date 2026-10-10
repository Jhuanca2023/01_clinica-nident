<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\RossetyCatalog;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RossetyController extends Controller
{
    private function page(string $view)
    {
        return response()
            ->view($view, RossetyCatalog::viewShared())
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function inicio()
    {
        return $this->page('web.rossety.inicio');
    }

    public function nosotros()
    {
        return $this->page('web.rossety.nosotros');
    }

    public function contacto()
    {
        return $this->page('web.rossety.contacto');
    }

    public function tienda(?string $category = null, ?string $subcategory = null)
    {
        if ($category && ! request()->query('cat')) {
            $qs = 'cat='.urlencode($category);
            if ($subcategory) {
                $qs .= '&sub='.urlencode($subcategory);
            }

            return redirect('/tienda?'.$qs);
        }

        return $this->page('web.rossety.tienda');
    }

    public function producto(?string $slug = null)
    {
        if ($slug && ! request()->query('id')) {
            return redirect('/producto?id='.urlencode($slug));
        }

        return $this->page('web.rossety.producto');
    }

    public function carrito()
    {
        return $this->page('web.rossety.carrito');
    }

    public function checkout()
    {
        return $this->page('web.rossety.checkout');
    }

    public function gracias()
    {
        return $this->page('web.rossety.gracias');
    }

    public function pedido(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'cliente.nombre' => 'required|string|max:255',
            'cliente.apellidos' => 'required|string|max:255',
            'cliente.correo' => 'required|email',
            'cliente.telefono' => 'required|string|max:20',
            'cliente.documento' => 'required|string|max:20',
            'cliente.tipoDoc' => 'nullable|string|max:40',
            'envio.direccion' => 'required|string|max:500',
            'envio.departamento' => 'required|string|max:120',
            'envio.provincia' => 'required|string|max:120',
            'envio.distrito' => 'required|string|max:120',
            'comprobante.tipo' => 'nullable|string|max:40',
            'comprobante.ruc' => 'nullable|string|max:20',
            'comprobante.razon' => 'nullable|string|max:255',
            'dedicatoria' => 'nullable|string|max:1000',
            'obs' => 'nullable|string|max:1000',
        ]);

        Cart::destroy();
        $shop = app(ShopDetailController::class);
        foreach ($data['items'] as $line) {
            $product = DB::table('administracion_producto')
                ->where('slug', $line['id'])
                ->where('codigo_producto', 'like', 'PRS-%')
                ->first();
            if (! $product || $product->precio === null) {
                continue;
            }
            $sub = Request::create('/web_shopDetail_agregar', 'POST', [
                'codigo_producto' => $product->codigo_producto,
                'cantidad' => (int) $line['qty'],
            ]);
            $added = $shop->web_shopDetail_agregar($sub);
            $body = $added->getData(true);
            if (($body['status'] ?? '') !== 'success') {
                return response()->json([
                    'success' => false,
                    'message' => $body['message'] ?? 'No se pudo agregar un producto',
                    'result' => null,
                ], 422);
            }
        }

        if (Cart::count() < 1) {
            return response()->json([
                'success' => false,
                'message' => 'No hay productos con precio para registrar el pedido.',
                'result' => null,
            ], 422);
        }

        $obs = '';
        if (! empty($data['dedicatoria'])) {
            $obs .= 'Dedicatoria: '.$data['dedicatoria']."\n";
        }
        if (! empty($data['obs'])) {
            $obs .= $data['obs'];
        }

        $request->merge([
            'txt_nombre' => $data['cliente']['nombre'],
            'txt_apellido' => $data['cliente']['apellidos'],
            'txt_email' => $data['cliente']['correo'],
            'txt_direccion' => $data['envio']['direccion'],
            'txt_telefono' => $data['cliente']['telefono'],
            'txt_dni' => $data['cliente']['documento'],
            'txt_tipo_documento' => $data['cliente']['tipoDoc'] ?? 'DNI',
            'txt_ruc' => $data['comprobante']['ruc'] ?? null,
            'txt_razon_social' => $data['comprobante']['razon'] ?? null,
            'txt_listar_departamentos' => $data['envio']['departamento'],
            'txt_listar_provincias' => $data['envio']['provincia'],
            'txt_listar_distritos' => $data['envio']['distrito'],
            'txt_observaciones' => trim($obs),
            'radio_metodo_pago' => '1',
            'check_terminos' => '1',
        ]);

        try {
            $checkout = app(CheckoutController::class);
            if (\App\Services\PaymentGatewayManager::isWhatsapp()) {
                return $checkout->enviar_pedido_whatsapp($request);
            }

            return $checkout->enviar_pago_sin_culqi($request);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?: 'Revisa los datos del pedido.',
                'result' => null,
            ], 422);
        }
    }
}

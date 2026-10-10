<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Lang;
use Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\IzipayService;

class PaymentController  extends Controller {


    public function payWithYape(Request $request, IzipayService $izipay)
    {
        $data = [
            'amount' => $request->amount, // S/10.00 (epresentando 1000 céntimos)
            'currency' => $request->currency,
            'email' => $request->email,
            'order_id' => uniqid('yape_'),
        ];
        
        // return $data;
        // // var_dump($data);
        try {
            $formToken = $izipay->createYapePayment($data);
    
            return view('payments.yape', compact('formToken'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function redirectToPayment(Request $request, IzipayService $izipay)
    {
        $paymentData = [
            'amount' => $request->amount, // En céntimos, e.g., 1000 = S/10.00
            'email' => $request->email,
            'order_id' => uniqid('order_'),
            'user_id' =>545,
        ];
    
        try {
            $result = $izipay->createPayment($paymentData);
    
            // URL para redirigir al formulario de pago
            return redirect($result['answer']['formToken']);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        // Nota:
        // ⚠️ Nota: Izipay devuelve un form token, y tú debes generar 
        // un formulario que lo use. Izipay no devuelve directamente una URL redireccionable.
    }

    public function handleWebhook(Request $request)
    {
        // \Log::info('Webhook recibido de Izipay', $request->all());
    
        if ($request->has('transaction') && $request->transaction['status'] === 'PAID') {
            // Busca la orden y márcala como pagada
            $orderId = $request->transaction['orderId'];
            // Ejemplo:
            // Order::where('order_id', $orderId)->update(['status' => 'paid']);
        }
    
        return response('OK', 200);
    }

    // El usuario:
    // Va a tu app → presiona "Pagar con Yape".
    // Es redirigido a Izipay → escanea QR con Yape.
    // Izipay notifica el pago → tu sistema marca la orden como pagada.


}
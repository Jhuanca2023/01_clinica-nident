<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\WebSuscriptores;
use App\Support\ContactoValento;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; // Añade esto al inicio del controlador


class SuscriptoresController  extends Controller
{
    public function crear_suscriptores(Request $request)
    {
         // Opción 2: Validar directamente el campo en mayúsculas
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:web_suscriptores,email'
        ], [
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'Debe ingresar un correo electrónico válido',
            'email.unique' => 'Este correo ya está registrado'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Crear el suscriptor con token
        $token = Str::random(32);
        // Crear el suscriptor
        WebSuscriptores::create([
            'email' => $request->email,
            'unsubscribe_token' => $token
        ]);

        // Enviar correo de agradecimiento al suscriptor
        $this->enviarCorreoGracias($request->email, $token);

        // Enviar el correo al administrador (opcional)
        Mail::send('email.admin_suscripcion', [
            'txt_email' => $request->email,
        ], function ($message) use ($request) {
            $message->to(ContactoValento::ventasEmail());
            $message->subject('Nuevo suscriptor en el sitio web');
            $message->replyTo($request->email);
        });

        return response()->json([
            'success' => true,
            'message' => '¡Gracias por suscribirte!'
        ]);
    }

    protected function enviarCorreoGracias($email, $token)
    {
        $unsubscribeLink = url('/unsuscribe?email=' . $email . '&token=' . $token);

        $data = [
            'email' => $email,
            'logo' => asset('imagenes/logo-principal.png'),
            'unsubscribeLink' => $unsubscribeLink
        ];

        Mail::send('email.gracias_suscripcion', $data, function ($message) use ($email) {
            $message->to($email);
            $message->subject('¡Gracias por suscribirte!');
            $message->from(ContactoValento::ventasEmail(), config('app.name'));
        });
    }

    //============================= CANCELAR SUSCRIPCION =============================
    //***************************************************************************** */
    public function showUnsubscribeForm(Request $request)
    {
        $email = $request->query('email');
        $token = $request->query('token');

        $subscriber = WebSuscriptores::where('email', $email)
            ->where('unsubscribe_token', $token)
            ->first();

        if (!$subscriber) {
            abort(404, 'Enlace de cancelación no válido');
        }

        return view('unsubscribe.form', compact('email', 'token'));
    }

    public function unsubscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:web_suscriptores,email',
            'token' => 'required'
        ]);


        try {
            $subscriber = WebSuscriptores::where('email', $request->email)
                ->where('unsubscribe_token', $request->token)
                ->firstOrFail();

            $subscriber->delete();

            return view('unsubscribe.confirmation', [
                'success' => true,
                'email' => $request->email
            ]);
        } catch (\Exception $e) {
            return view('unsubscribe.confirmation', [
                'success' => false,
                'message' => 'No se pudo cancelar la suscripción. ' . $e->getMessage()
            ]);
        }
    }
}

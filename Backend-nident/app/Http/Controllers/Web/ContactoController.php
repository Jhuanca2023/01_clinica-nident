<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Models\MetadatosPagina;
use App\Models\WebContactoMensaje;

class ContactoController extends Controller
{
    public function web_contact()
    {
        $metaData = MetadatosPagina::metaVigente('web_contact');
        return view('web.pages.web_contact', compact('metaData'));
    }

    public function submitForm(Request $request)
    {
        $request->validate([
            'txt_nombre'           => 'required|string|max:100',
            'txt_email'            => 'required|email',
            'txt_asunto'           => 'required|string|max:150',
            'txt_mensaje'          => 'required|string|max:2000',
            'g-recaptcha-response' => 'required',
        ], [
            'g-recaptcha-response.required' => 'Por favor, verifica que no eres un robot.',
        ]);

        if (!app()->environment('local')) {
            $this->verifyRecaptcha($request);
        }

        Mail::send('email.formContacto', [
            'txt_nombre'   => $request->input('txt_nombre'),
            'txt_telefono' => $request->input('txt_telefono'),
            'txt_email'    => $request->input('txt_email'),
            'txt_asunto'   => $request->input('txt_asunto'),
            'txt_mensaje'  => $request->input('txt_mensaje'),
        ], function ($message) use ($request) {
            $message->from(config('mail.from.address'), config('mail.from.name'));
            $message->to(config('mail.contact_to'));
            $message->subject('Nuevo mensaje de contacto');
            $message->replyTo($request->input('txt_email'), $request->input('txt_nombre'));
        });

        return redirect()->back()->with('success', '¡Mensaje enviado correctamente!');
    }

    /** Formulario página /contacto */
    public function submitPagina(Request $request)
    {
        return $this->guardarYEnviarContacto($request, route('contacto.pagina'));
    }

    private function guardarYEnviarContacto(Request $request, string $redirectUrl)
    {
        $request->validate([
            'nombre'            => 'required|string|max:100',
            'apellido'          => 'nullable|string|max:100',
            'email'             => 'required|email|max:150',
            'telefono'          => 'nullable|string|max:60',
            'producto_interes'  => 'required|string|max:255',
            'fecha_mensaje'     => 'required|date',
            'mensaje'           => 'nullable|string|max:2000',
            'acepto_privacidad' => 'accepted',
        ], [
            'acepto_privacidad.accepted' => 'Debes aceptar la Política de Privacidad y los Términos.',
        ]);

        $emailDestino = \App\Support\ContactoValento::ventasEmail()
            ?: config('mail.contact_to')
            ?: config('mail.from.address');

        $nombreCompleto = trim($request->nombre . ' ' . ($request->apellido ?? ''));

        $mensaje = WebContactoMensaje::create([
            'nombre'          => $request->nombre,
            'apellido'        => $request->apellido ?? '',
            'email'           => $request->email,
            'asunto'          => $request->producto_interes,
            'producto_interes'=> $request->producto_interes,
            'fecha_mensaje'   => $request->fecha_mensaje,
            'telefono'        => $request->telefono ?? '',
            'mensaje'         => $request->mensaje ?? '',
            'email_enviado'   => 'N',
        ]);

        try {
            Mail::send('email.formContacto', [
                'txt_nombre'   => $nombreCompleto,
                'txt_telefono' => $request->telefono ?? '',
                'txt_email'    => $request->email,
                'txt_asunto'   => $request->producto_interes,
                'txt_mensaje'  => $request->mensaje,
            ], function ($message) use ($request, $nombreCompleto, $emailDestino) {
                $message->from(
                    config('mail.from.address'),
                    config('mail.from.name', 'Valento')
                );
                $message->to($emailDestino);
                $message->subject('Nuevo mensaje — ' . $request->producto_interes);
                $message->replyTo($request->email, $nombreCompleto);
            });

            $mensaje->update(['email_enviado' => 'S']);
        } catch (\Throwable $e) {
            Log::error('Contacto form SMTP: ' . $e->getMessage(), [
                'mailer' => config('mail.default'),
                'host'   => config('mail.mailers.smtp.host'),
            ]);

            return redirect()->to($redirectUrl)
                ->withErrors(['email' => 'El mensaje se guardó, pero no se pudo enviar el correo. Revisa la configuración SMTP o intenta por WhatsApp.'])
                ->withInput();
        }

        return redirect()->to($redirectUrl)->with('success', '¡Mensaje enviado! Te responderemos pronto.');
    }

    private function verifyRecaptcha(Request $request): void
    {
        $googleResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => config('services.recaptcha.secret'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        $json = $googleResponse->json();
        if (!($json['success'] ?? false) || ($json['score'] ?? 0) < 0.5) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'captcha' => 'No se pudo verificar que eres humano.',
            ]);
        }
    }
}

<?php

namespace App\Mail;

use App\Support\ContactoValento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;

    public function __construct($pedido)
    {
        $this->pedido = $pedido;
    }

    public function build()
    {
        // Debe coincidir con MAIL_USERNAME / MAIL_FROM_ADDRESS (SMTP de cPanel).
        // Si se usa no-reply@ sin buzón, Gmail y otros externos suelen rechazar el correo
        // mientras que ventas@rossety.pe (mismo dominio) sí llega.
        $fromAddress = config('mail.from.address', ContactoValento::ventasEmail());
        $fromName = config('mail.from.name', 'Valento');

        return $this->from($fromAddress, $fromName)
            ->replyTo($fromAddress, $fromName)
            ->subject('Confirmación de tu pedido #' . ($this->pedido['codigo_pedido'] ?? ''))
            ->view('email.order_confirmation')
            ->with(['pedido' => $this->pedido]);
    }
}

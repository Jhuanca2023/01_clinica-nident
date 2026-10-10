<?php

namespace App\Mail;

use App\Support\ContactoValento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationVentasMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;

    public function __construct($pedido)
    {
        $this->pedido = $pedido;
    }

    public function build()
    {
        // Mismo dominio que el buzón SMTP: evita rechazos de Gmail y otros externos.
        $fromAddress = config('mail.from.address', ContactoValento::ventasEmail());
        $fromName = config('mail.from.name', 'Valento');

        return $this->from($fromAddress, $fromName)
            ->replyTo($fromAddress, $fromName)
            ->subject('Nuevo Pedido Recibido #' . ($this->pedido['codigo_pedido'] ?? ''))
            ->view('email.order_confirmation')
            ->with(['pedido' => $this->pedido]);
    }
}

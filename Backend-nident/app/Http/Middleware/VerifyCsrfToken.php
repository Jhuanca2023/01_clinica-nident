<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //'api/*', // Excluye todas las rutas API - activar si hace falta
        'pay/yape',
        'web_shopDetail_agregar',
        'web_shopDetail_eliminar',
        'web_shopDetail_actualizar',
        'web_wishlist_agregar',
        'web_wishlist_eliminar',
        'api/stripe/webhook',

        // Cotizador público — el JS envía el token pero SESSION_DOMAIN en producción
        // puede causar mismatch si la cookie se emite para el dominio incorrecto.
        // La ruta solo guarda una cotización, valida inputs y envía email; sin acción sensible de usuario.
        'cotizador/submit',
        'web_checkout/ubigeo/provincias',
        'web_checkout/ubigeo/distritos',
    ];
}

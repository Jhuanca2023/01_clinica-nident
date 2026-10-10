<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebCarritoConfig extends Model
{
    protected $table = 'web_carrito_config';
    protected $guarded = [];

    /** Valores por defecto para primera creación. */
    public static function defaults(): array
    {
        return [
            'resumen_titulo'       => 'Resumen de su compra',
            'label_subtotal'       => 'Subtotal',
            'label_igv'            => 'IGV (18%)',
            'igv_porcentaje'       => 18.00,
            'label_descuento'      => 'Descuento oferta',
            'label_total'          => 'Total',
            'umbral_envio_gratis'  => 199.00,
            'envio_gratis_texto'   => '¡Envío GRATIS!',
            'envio_falta_texto'    => 'Te faltan {monto} para envío gratis',
            'nota_titulo'          => 'IMPORTANTE:',
            'nota_texto'           => 'Revisa tallas y colores antes de confirmar. Para uniformes por volumen, cotiza por WhatsApp.',
            'nota_activa'          => 'N',
            'btn_continuar_texto'  => 'Continuar',
            'btn_seguir_texto'     => 'Seguir comprando',
            'asesor_activo'        => 'S',
            'asesor_label'         => 'Asesor en línea',
            'asesor_titulo'        => '¿Necesitas ayuda?',
            'asesor_subtitulo'     => 'Chatea con un asesor ahora',
            'asesor_mensaje'       => 'Hola! Estoy en el carrito de Valento y me gustaría recibir asesoría para completar mi compra.',
            'Activo'               => 'S',
        ];
    }

    /** Registro único (id=1), creándolo con defaults si no existe. */
    public static function obtenerConfig(): self
    {
        return static::firstOrCreate(['id' => 1], static::defaults());
    }
}

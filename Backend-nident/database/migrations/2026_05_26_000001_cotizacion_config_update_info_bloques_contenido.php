<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function bloques(): array
    {
        return [
            [
                'id'      => 'promo',
                'variant' => 'promo',
                'titulo'  => 'Todos los proyectos incluyen',
                'icon'    => 'bi-gift',
                'items'   => [
                    'Hosting gratis 1 año',
                    'Dominio gratis 1 año',
                    'SSL gratis (HTTPS activado)',
                    '10 correos corporativos',
                    'Diseño responsivo (móvil + escritorio)',
                    'Panel de administración web',
                    'Capacitación en uso del sistema',
                    'Soporte técnico post-entrega',
                    'Código fuente entregado al cliente',
                ],
            ],
            [
                'id'      => 'renovacion',
                'variant' => 'default',
                'titulo'  => 'Renovación anual',
                'icon'    => 'bi-arrow-repeat',
                'items'   => [
                    'Dominio .com: S/ {renovacion_dominio} / año',
                    'Renovación de hosting compartido',
                    'Renovación de SSL incluida',
                    'Backup mensual del sitio',
                    'Monitoreo de disponibilidad 24/7',
                ],
            ],
            [
                'id'      => 'soporte',
                'variant' => 'default',
                'titulo'  => 'Soporte y mantenimiento',
                'icon'    => 'bi-tools',
                'items'   => [
                    'Soporte técnico dentro del desarrollo',
                    'Corrección de errores post-entrega (30 días)',
                    'Actualizaciones menores de seguridad',
                    'Atención por WhatsApp y correo',
                    'Sistemas de terceros: S/ {soporte_hora} / hora',
                ],
            ],
        ];
    }

    public function up(): void
    {
        DB::table('cotizacion_config')
            ->orderBy('id')
            ->limit(1)
            ->update([
                'info_bloques' => json_encode($this->bloques(), JSON_UNESCAPED_UNICODE),
            ]);
    }

    public function down(): void
    {
        // Reversión: deja el campo como estaba (null → usa defaults del código)
        DB::table('cotizacion_config')
            ->orderBy('id')
            ->limit(1)
            ->update(['info_bloques' => null]);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Legales Valento (web_terminos): reemplaza el correo heredado
 * informacion@liamvercelli.com (variante con una "c" y doble "l", que
 * 2026_09_24_180200_seed_valento_legales_privacidad no cubría).
 *
 * id_footer_document 3 (Privacidad) → ventas@rossety.pe
 * resto (Términos, etc.)             → ventas@rossety.pe
 *
 * Idempotente: no-op si ya no quedan correos de Liam.
 */
return new class extends Migration
{
    private const TABLA = 'web_terminos';

    private const EMAIL_PRIVACIDAD = 'ventas@rossety.pe';
    private const EMAIL_VENTAS = 'ventas@rossety.pe';

    /** Variantes de dominio heredadas (orden: más largas primero). */
    private const DOMINIOS_LIAM = [
        'liamvercelli.com',
        'liamvercceli.com',
        'liamverceli.com',
        'liamvercelli.pe',
    ];

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLA) || ! Schema::hasColumn(self::TABLA, 'descripcion')) {
            return;
        }

        $tieneUpdated = Schema::hasColumn(self::TABLA, 'updated_at');

        foreach (DB::table(self::TABLA)->get(['id_footer_document', 'descripcion']) as $fila) {
            $descripcion = (string) ($fila->descripcion ?? '');
            $email = (int) $fila->id_footer_document === 3 ? self::EMAIL_PRIVACIDAD : self::EMAIL_VENTAS;

            $nueva = $descripcion;
            foreach (self::DOMINIOS_LIAM as $dominio) {
                // Cualquier buzón @dominio-liam → correo Valento
                $nueva = preg_replace('/[A-Za-z0-9._%+-]+@' . preg_quote($dominio, '/') . '/i', $email, $nueva);
            }

            if ($nueva === $descripcion) {
                continue;
            }

            $cambios = ['descripcion' => $nueva];
            if ($tieneUpdated) {
                $cambios['updated_at'] = now();
            }

            DB::table(self::TABLA)
                ->where('id_footer_document', $fila->id_footer_document)
                ->update($cambios);
        }
    }

    public function down(): void
    {
        // Contenido legal de marca: no se revierte.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Popup promocional (web_popup_promo): adapta a Valento los valores heredados
 * de la plantilla Liam Vercceli.
 *
 * - color_acento rojo de la plantilla (#922b21) → azul marino Valento (#121a36)
 * - opciones de polos (Color, Polo Box, Cuello redondo, Talla) → líneas Valento
 * - url_imagen que apunta a un archivo que ya no existe → null (placeholder)
 *
 * Idempotente: solo toca valores que siguen siendo los heredados.
 */
return new class extends Migration
{
    private const TABLA = 'web_popup_promo';

    private const ACENTO_VALENTO = '#121a36';

    private const OPCIONES_VALENTO = ['Ropa laboral', 'Uniformes', 'Textil promocional', 'Vestuario deportivo'];

    /** Opciones que delatan la plantilla anterior (comparación en minúsculas). */
    private const OPCIONES_HEREDADAS = ['polo box', 'cuello redondo', 'colores enteros', 'colores vibrantes', 'print lover'];

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLA)) {
            return;
        }

        foreach (DB::table(self::TABLA)->get() as $fila) {
            $cambios = [];

            if (Schema::hasColumn(self::TABLA, 'color_acento')
                && strtolower(trim((string) $fila->color_acento)) === '#922b21') {
                $cambios['color_acento'] = self::ACENTO_VALENTO;
            }

            if (Schema::hasColumn(self::TABLA, 'opciones')) {
                $opciones = json_decode((string) ($fila->opciones ?? ''), true);
                $heredadas = is_array($opciones) && count(array_intersect(
                    array_map(fn ($o) => mb_strtolower(trim((string) $o)), $opciones),
                    self::OPCIONES_HEREDADAS
                )) > 0;

                if ($heredadas) {
                    $cambios['opciones'] = json_encode(self::OPCIONES_VALENTO, JSON_UNESCAPED_UNICODE);
                    if (Schema::hasColumn(self::TABLA, 'opciones_label')) {
                        $cambios['opciones_label'] = '¿Qué te interesa?';
                    }
                }
            }

            if (Schema::hasColumn(self::TABLA, 'url_imagen') && ! empty($fila->url_imagen)
                && ! preg_match('#^https?://#i', (string) $fila->url_imagen)
                && ! is_file(public_path('storage_/' . ltrim((string) $fila->url_imagen, '/')))) {
                $cambios['url_imagen'] = null;
            }

            if ($cambios === []) {
                continue;
            }

            if (Schema::hasColumn(self::TABLA, 'updated_at')) {
                $cambios['updated_at'] = now();
            }

            DB::table(self::TABLA)->where('id_popup', $fila->id_popup)->update($cambios);
        }
    }

    public function down(): void
    {
        // Contenido de marca: no se revierte.
    }
};

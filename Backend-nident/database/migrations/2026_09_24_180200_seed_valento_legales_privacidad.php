<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Legales Valento (web_terminos): limpia marcas heredadas (Tiendas Él, Lucdesoft,
 * Liam Vercceli) y garantiza el bloque de datos canónicos de la empresa.
 *
 * id_footer_document: 2 → Términos, 3 → Privacidad, 4 → Libro de Reclamaciones.
 *
 * Idempotente: las sustituciones son no-op si ya se aplicaron y el bloque de datos
 * se identifica con el marcador MARCADOR_DATOS.
 */
return new class extends Migration
{
    private const TABLA = 'web_terminos';

    private const RAZON_SOCIAL = 'Valento Stile S.A.C.';
    private const RUC = '20608473159';
    private const DIRECCION = 'Calle Los Flamencos 318, Urb. Santa Catalina, La Victoria, Lima – Perú';
    private const EMAIL_PRIVACIDAD = 'ventas@rossety.pe';
    private const EMAIL_VENTAS = 'ventas@rossety.pe';
    private const WA_NUMERO = '51964381275';
    private const WA_DISPLAY = '+51 964 381 275';

    private const MARCADOR_DATOS = '<!-- valento-datos-empresa -->';

    /** @var array<int, string> */
    private const TITULOS = [
        2 => 'Términos y Condiciones',
        3 => 'Política de Privacidad',
        4 => 'Libro de Reclamaciones',
    ];

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLA) || ! Schema::hasColumn(self::TABLA, 'descripcion')) {
            return;
        }

        $tieneTitulo = Schema::hasColumn(self::TABLA, 'titulo');
        $tieneUpdated = Schema::hasColumn(self::TABLA, 'updated_at');

        foreach (array_keys(self::TITULOS) as $docId) {
            $fila = DB::table(self::TABLA)->where('id_footer_document', $docId)->first();
            if (! $fila) {
                continue;
            }

            $descripcion = (string) ($fila->descripcion ?? '');
            $nuevaDescripcion = $this->asegurarDatosEmpresa($this->rebrand($descripcion), $docId);

            $cambios = [];
            if ($nuevaDescripcion !== $descripcion) {
                $cambios['descripcion'] = $nuevaDescripcion;
            }

            if ($tieneTitulo) {
                $tituloActual = trim((string) ($fila->titulo ?? ''));
                $tituloNuevo = $tituloActual === '' ? self::TITULOS[$docId] : $this->rebrand($tituloActual);
                if ($tituloNuevo !== $tituloActual) {
                    $cambios['titulo'] = $tituloNuevo;
                }
            }

            if ($cambios === []) {
                continue;
            }

            if ($tieneUpdated) {
                $cambios['updated_at'] = now();
            }

            DB::table(self::TABLA)->where('id_footer_document', $docId)->update($cambios);
        }
    }

    public function down(): void
    {
        // Contenido legal de marca: no se revierte.
    }

    /**
     * Sustituye marcas y datos de contacto heredados por los de Valento.
     * El orden importa: correos y dominios antes que el nombre de marca.
     */
    private function rebrand(string $html): string
    {
        $reemplazos = [
            'contacto@rossety.pe' => self::EMAIL_VENTAS,
            'ventas@rossety.pe' => self::EMAIL_VENTAS,
            'https://rossety.pe' => 'https://rossety.pe',
            'rossety.pe' => 'rossety.pe',
            'https://wa.me/51970048451' => 'https://wa.me/' . self::WA_NUMERO,
            'https://wa.me/51999999999' => 'https://wa.me/' . self::WA_NUMERO,
            '51970048451' => self::WA_NUMERO,
            '51999999999' => self::WA_NUMERO,
            '+51 970 048 451' => self::WA_DISPLAY,
            '+51 999 999 999' => self::WA_DISPLAY,
            'Tiendas Él' => self::RAZON_SOCIAL,
            'Tiendas El' => self::RAZON_SOCIAL,
            'TIENDAS ÉL' => 'rossety STILE S.A.C.',
            'TIENDAS EL' => 'rossety STILE S.A.C.',
            'LIAM VERCCELI' => 'rossety',
            'Liam Vercceli' => 'Valento',
            'Lucdesoft' => self::RAZON_SOCIAL,
            'LUCDESOFT' => 'rossety STILE S.A.C.',
        ];

        return strtr($html, $reemplazos);
    }

    /**
     * Anexa el bloque de datos canónicos si el documento aún no lo trae.
     * Se considera presente si ya aparece el RUC o el marcador.
     */
    private function asegurarDatosEmpresa(string $html, int $docId): string
    {
        if (str_contains($html, self::MARCADOR_DATOS) || str_contains($html, self::RUC)) {
            return $html;
        }

        $email = $docId === 3 ? self::EMAIL_PRIVACIDAD : self::EMAIL_VENTAS;

        return rtrim($html) . "\n\n" . $this->bloqueDatosEmpresa($email);
    }

    private function bloqueDatosEmpresa(string $email): string
    {
        $marcador = self::MARCADOR_DATOS;
        $razon = self::RAZON_SOCIAL;
        $ruc = self::RUC;
        $direccion = self::DIRECCION;
        $wa = self::WA_NUMERO;
        $waDisplay = self::WA_DISPLAY;

        return <<<HTML
        {$marcador}
        <h3 style="color:#1a1a1a;font-family:Arial,sans-serif;font-size:15px;margin:24px 0 8px">Datos de la empresa</h3>
        <p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
        <strong>{$razon}</strong><br>
        RUC: {$ruc}<br>
        Domicilio fiscal: {$direccion}<br>
        Correo: <a href="mailto:{$email}">{$email}</a><br>
        WhatsApp: <a href="https://wa.me/{$wa}">{$waDisplay}</a>
        </p>
        HTML;
    }
};

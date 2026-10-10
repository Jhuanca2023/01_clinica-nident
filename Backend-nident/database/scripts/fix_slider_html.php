<?php
/**
 * Script: fix_slider_html.php
 * Limpia etiquetas HTML de los campos titulo, subtitulo, descripcion, texto_boton
 * en la tabla web_slider (datos guardados con TinyMCE por error).
 *
 * Ejecutar:
 *   php artisan tinker --no-interaction
 *   require database_path('scripts/fix_slider_html.php');
 */

use Illuminate\Support\Facades\DB;

$sliders = DB::table('web_slider')->get();

$fixed = 0;
foreach ($sliders as $s) {
    $titulo      = trim(strip_tags(html_entity_decode($s->titulo      ?? '', ENT_QUOTES)));
    $subtitulo   = trim(strip_tags(html_entity_decode($s->subtitulo   ?? '', ENT_QUOTES)));
    $descripcion = trim(strip_tags(html_entity_decode($s->descripcion ?? '', ENT_QUOTES)));
    $texto_boton = trim(strip_tags(html_entity_decode($s->texto_boton ?? '', ENT_QUOTES)));

    // Normaliza espacios múltiples
    $titulo      = preg_replace('/\s+/', ' ', $titulo);
    $subtitulo   = preg_replace('/\s+/', ' ', $subtitulo);
    $descripcion = preg_replace('/\s+/', ' ', $descripcion);
    $texto_boton = preg_replace('/\s+/', ' ', $texto_boton);

    if (
        $titulo      !== ($s->titulo      ?? '') ||
        $subtitulo   !== ($s->subtitulo   ?? '') ||
        $descripcion !== ($s->descripcion ?? '') ||
        $texto_boton !== ($s->texto_boton ?? '')
    ) {
        DB::table('web_slider')->where('id_slider', $s->id_slider)->update([
            'titulo'      => $titulo,
            'subtitulo'   => $subtitulo,
            'descripcion' => $descripcion,
            'texto_boton' => $texto_boton,
            'updated_at'  => now(),
        ]);
        echo "  ✔ Slider #{$s->id_slider} — limpiado\n";
        echo "      titulo:      " . substr($titulo, 0, 60) . "\n";
        echo "      subtitulo:   " . substr($subtitulo, 0, 60) . "\n";
        $fixed++;
    } else {
        echo "  — Slider #{$s->id_slider} — ya limpio\n";
    }
}

echo "\n✅ Completado: {$fixed} sliders corregidos de " . count($sliders) . " totales.\n";

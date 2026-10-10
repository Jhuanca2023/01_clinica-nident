<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebPopupPromo;
use App\Models\WebPopupPromoHistorial;
use App\Support\PopupPromoDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class WebPopupPromoController extends Controller
{
    /** GET /api/web-popup/obtener — panel admin y web pública */
    public function obtener(Request $request)
    {
        $popup = WebPopupPromo::first();
        if (! $popup) {
            return response()->json(['result' => null]);
        }

        $formatted = $this->format($popup);

        if (str_contains($request->path(), 'web-popup/publico')) {
            if (($popup->activo ?? 'N') !== 'S' || ! PopupPromoDiscount::estaEnRangoFechas($popup)) {
                $formatted['activo'] = 'N';
                $formatted['vigente'] = false;
            } else {
                $formatted['vigente'] = true;
            }
            $formatted['descuento_aplicado'] = PopupPromoDiscount::activePayload() !== null;
        }

        return response()->json(['result' => $formatted]);
    }

    /** POST /api/web-popup/aplicar-descuento */
    public function aplicarDescuento()
    {
        $popup = PopupPromoDiscount::popupVigente();
        if (! $popup) {
            return response()->json([
                'success' => false,
                'message' => 'La promoción no está activa o ya venció.',
            ], 422);
        }

        $payload = PopupPromoDiscount::activarDesdePopup($popup);
        if (! $payload) {
            return response()->json([
                'success' => false,
                'message' => 'Configure un porcentaje válido (ej: 15% OFF).',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Descuento aplicado',
            'result' => $payload,
            'redirect' => url('/tienda') . '?filtros=libres',
        ]);
    }

    /** GET /api/web-popup/historial */
    public function historial()
    {
        if (! Schema::hasTable('web_popup_promo_historial')) {
            return response()->json(['result' => []]);
        }

        $rows = WebPopupPromoHistorial::query()
            ->orderByDesc('id_historial')
            ->limit(100)
            ->get()
            ->map(fn ($h) => $this->formatHistorial($h));

        return response()->json(['result' => $rows]);
    }

    /** POST /api/web-popup/historial_guardar — archiva la config actual (nueva fecha / snapshot) */
    public function historial_guardar(Request $request)
    {
        $popup = WebPopupPromo::firstOrCreate(['id_popup' => 1]);
        $nota = trim((string) $request->input('nota', 'Snapshot manual'));
        $row = $this->archivePopup($popup, $nota !== '' ? $nota : 'Snapshot manual');

        return response()->json([
            'success' => true,
            'message' => 'Promoción guardada en el historial',
            'result' => $row ? $this->formatHistorial($row) : null,
        ]);
    }

    /**
     * POST /api/web-popup/historial_nueva — archiva actual y limpia fechas para una nueva campaña
     */
    public function historial_nueva()
    {
        $popup = WebPopupPromo::firstOrCreate(['id_popup' => 1]);
        $this->archivePopup($popup, 'Antes de nueva promoción');

        $popup->fecha_inicio = null;
        $popup->fecha_fin = null;
        $popup->activo = 'N';
        $popup->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuración archivada. Configure fechas para la nueva promoción.',
            'result' => $this->format($popup),
        ]);
    }

    /** DELETE /api/web-popup/historial_eliminar */
    public function historial_eliminar(Request $request)
    {
        $id = (int) $request->input('id_historial');
        $row = WebPopupPromoHistorial::find($id);
        if (! $row) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
        }

        if ($row->url_imagen && str_contains((string) $row->url_imagen, 'popup/historial/')) {
            $path = public_path('storage_/' . $row->url_imagen);
            if (is_file($path)) {
                @unlink($path);
            }
        }

        $row->delete();

        return response()->json(['success' => true, 'message' => 'Eliminado del historial']);
    }

    /**
     * POST /api/web-popup/historial_restaurar
     * Body: id_historial, guardar_actual (0|1)
     */
    public function historial_restaurar(Request $request)
    {
        $request->validate([
            'id_historial' => 'required|integer',
            'guardar_actual' => 'nullable',
        ]);

        $hist = WebPopupPromoHistorial::find((int) $request->input('id_historial'));
        if (! $hist) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
        }

        $popup = WebPopupPromo::firstOrCreate(['id_popup' => 1]);

        $guardar = filter_var($request->input('guardar_actual', false), FILTER_VALIDATE_BOOLEAN);
        if ($guardar) {
            $this->archivePopup($popup, 'Antes de restaurar historial #' . $hist->id_historial);
        }

        $this->applyHistorialToPopup($popup, $hist);
        $popup->save();

        return response()->json([
            'success' => true,
            'message' => 'Promoción restaurada',
            'result' => $this->format($popup),
        ]);
    }

    /** POST /api/web-popup/actualizar */
    public function actualizar(Request $request)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
        ]);

        $modoReq = (string) $request->input('modo', '');
        if ($modoReq === 'oferta_tienda') {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            ], [
                'fecha_inicio.required' => 'La fecha de inicio es obligatoria para la oferta de tienda.',
                'fecha_fin.required' => 'La fecha fin es obligatoria para la oferta de tienda.',
            ]);
        }

        $popup = WebPopupPromo::firstOrCreate(['id_popup' => 1]);

        // Archivar versión previa si ya tenía contenido relevante
        if ($this->popupTieneContenidoArchivable($popup)) {
            $this->archivePopup($popup, 'Versión anterior al guardar');
        }

        $fields = [
            'titulo', 'descuento_texto', 'subtitulo', 'btn_texto',
            'placeholder_email', 'placeholder_telefono',
            'delay_segundos', 'cookie_dias', 'cookie_unidad', 'color_acento',
            'whatsapp_numero', 'whatsapp_mensaje', 'modo',
            'fecha_inicio', 'fecha_fin', 'descuento_porcentaje',
        ];
        foreach ($fields as $f) {
            if ($request->has($f)) {
                $val = $request->input($f);
                if (in_array($f, ['fecha_inicio', 'fecha_fin'], true) && ($val === '' || $val === null)) {
                    $popup->$f = null;
                } elseif ($f === 'descuento_porcentaje' && ($val === '' || $val === null)) {
                    $popup->$f = null;
                } else {
                    $popup->$f = $val;
                }
            }
        }

        if ($request->has('descuento_texto') && ! $request->filled('descuento_porcentaje')) {
            $parsed = PopupPromoDiscount::parsePorcentaje($request->input('descuento_texto'));
            if ($parsed !== null) {
                $popup->descuento_porcentaje = $parsed;
            }
        }

        if ($request->has('modo')) {
            $modo = (string) $request->input('modo');
            $popup->modo = in_array($modo, ['formulario', 'imagen', 'oferta_tienda'], true)
                ? $modo
                : 'formulario';
        }

        if ($request->has('mostrar_email')) {
            $popup->mostrar_email = filter_var($request->input('mostrar_email'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('mostrar_telefono')) {
            $popup->mostrar_telefono = filter_var($request->input('mostrar_telefono'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('mostrar_opciones')) {
            $popup->mostrar_opciones = filter_var($request->input('mostrar_opciones'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('activo')) {
            $popup->activo = $request->input('activo');
        }

        if ($request->has('opciones')) {
            $raw = $request->input('opciones');
            $popup->opciones = is_string($raw) ? $raw : json_encode($raw);
        }
        if ($request->has('opciones_label')) {
            $popup->opciones_label = $request->input('opciones_label');
        }

        // Quitar imagen sin subir otra
        if ($request->boolean('quitar_imagen') && ! $request->hasFile('image')) {
            \App\Support\CmsImageStorage::deleteIfOwned($popup->url_imagen);
            $popup->url_imagen = null;
        }

        if ($request->hasFile('image')) {
            \App\Support\CmsImageStorage::deleteIfOwned($popup->url_imagen);
            $popup->url_imagen = \App\Support\CmsImageStorage::store($request->file('image'), 'popup');
        }

        $popup->save();

        return response()->json(['result' => $this->format($popup), 'message' => 'Popup actualizado']);
    }

    public function toggle()
    {
        $popup = WebPopupPromo::firstOrCreate(['id_popup' => 1]);
        $popup->activo = $popup->activo === 'S' ? 'N' : 'S';
        $popup->save();

        return response()->json(['result' => $this->format($popup)]);
    }

    private function popupTieneContenidoArchivable(WebPopupPromo $p): bool
    {
        return ! empty($p->fecha_inicio)
            || ! empty($p->fecha_fin)
            || ! empty($p->url_imagen)
            || trim((string) ($p->descuento_texto ?? '')) !== '';
    }

    private function archivePopup(WebPopupPromo $popup, string $nota = ''): ?WebPopupPromoHistorial
    {
        if (! Schema::hasTable('web_popup_promo_historial')) {
            return null;
        }
        if (! $this->popupTieneContenidoArchivable($popup) && $nota === '') {
            return null;
        }

        $imgRel = $this->copyImageToHistorial($popup->url_imagen ?? null);

        return WebPopupPromoHistorial::create([
            'id_popup' => (int) ($popup->id_popup ?? 1),
            'titulo' => $popup->titulo,
            'descuento_texto' => $popup->descuento_texto,
            'descuento_porcentaje' => $popup->descuento_porcentaje,
            'subtitulo' => $popup->subtitulo,
            'btn_texto' => $popup->btn_texto,
            'url_imagen' => $imgRel,
            'modo' => $popup->modo,
            'color_acento' => $popup->color_acento,
            'fecha_inicio' => $popup->fecha_inicio,
            'fecha_fin' => $popup->fecha_fin,
            'delay_segundos' => $popup->delay_segundos,
            'cookie_dias' => $popup->cookie_dias,
            'cookie_unidad' => $popup->cookie_unidad,
            'whatsapp_numero' => $popup->whatsapp_numero,
            'whatsapp_mensaje' => $popup->whatsapp_mensaje,
            'mostrar_email' => $popup->mostrar_email,
            'mostrar_telefono' => $popup->mostrar_telefono,
            'mostrar_opciones' => $popup->mostrar_opciones,
            'opciones_label' => $popup->opciones_label,
            'opciones' => is_array($popup->opciones)
                ? json_encode($popup->opciones)
                : $popup->opciones,
            'activo' => $popup->activo,
            'nota' => $nota,
        ]);
    }

    private function copyImageToHistorial(?string $rel): ?string
    {
        $rel = trim((string) $rel);
        if ($rel === '') {
            return null;
        }
        // Ya es URL absoluta → no copiar
        if (preg_match('#^https?://#i', $rel)) {
            $path = parse_url($rel, PHP_URL_PATH);
            $rel = ltrim((string) $path, '/');
            if (str_starts_with($rel, 'storage_/')) {
                $rel = substr($rel, strlen('storage_/'));
            }
        }

        $src = public_path('storage_/' . ltrim($rel, '/'));
        if (! is_file($src)) {
            return $rel;
        }

        $dir = public_path('storage_/popup/historial');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = time() . '_' . basename($src);
        $dest = $dir . DIRECTORY_SEPARATOR . $name;
        @copy($src, $dest);

        return 'popup/historial/' . $name;
    }

    private function applyHistorialToPopup(WebPopupPromo $popup, WebPopupPromoHistorial $hist): void
    {
        $popup->titulo = $hist->titulo;
        $popup->descuento_texto = $hist->descuento_texto;
        $popup->descuento_porcentaje = $hist->descuento_porcentaje;
        $popup->subtitulo = $hist->subtitulo;
        $popup->btn_texto = $hist->btn_texto;
        $popup->modo = $hist->modo;
        $popup->color_acento = $hist->color_acento;
        $popup->fecha_inicio = $hist->fecha_inicio;
        $popup->fecha_fin = $hist->fecha_fin;
        $popup->delay_segundos = $hist->delay_segundos;
        $popup->cookie_dias = $hist->cookie_dias;
        $popup->cookie_unidad = $hist->cookie_unidad;
        $popup->whatsapp_numero = $hist->whatsapp_numero;
        $popup->whatsapp_mensaje = $hist->whatsapp_mensaje;
        $popup->mostrar_email = $hist->mostrar_email;
        $popup->mostrar_telefono = $hist->mostrar_telefono;
        $popup->mostrar_opciones = $hist->mostrar_opciones;
        $popup->opciones_label = $hist->opciones_label;
        $popup->opciones = $hist->opciones;
        $popup->activo = $hist->activo ?? 'N';

        // Restaurar imagen: copiar del historial a popup/
        $histImg = trim((string) ($hist->url_imagen ?? ''));
        if ($histImg !== '') {
            $src = public_path('storage_/' . ltrim($histImg, '/'));
            if (is_file($src)) {
                $dir = public_path('storage_/popup');
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $name = 'restored_' . time() . '_' . basename($src);
                @copy($src, $dir . DIRECTORY_SEPARATOR . $name);
                $popup->url_imagen = 'popup/' . $name;
            } else {
                $popup->url_imagen = $histImg;
            }
        } else {
            $popup->url_imagen = null;
        }
    }

    private function formatHistorial(WebPopupPromoHistorial $h): array
    {
        $img = $h->url_imagen
            ? asset('storage_/' . ltrim((string) $h->url_imagen, '/'))
            : null;

        return [
            'id_historial' => $h->id_historial,
            'titulo' => $h->titulo,
            'descuento_texto' => $h->descuento_texto,
            'descuento_porcentaje' => $h->descuento_porcentaje,
            'subtitulo' => $h->subtitulo,
            'btn_texto' => $h->btn_texto,
            'url_imagen' => $img,
            'modo' => $h->modo,
            'color_acento' => $h->color_acento,
            'fecha_inicio' => $h->fecha_inicio
                ? \Carbon\Carbon::parse($h->fecha_inicio)->format('Y-m-d H:i')
                : null,
            'fecha_fin' => $h->fecha_fin
                ? \Carbon\Carbon::parse($h->fecha_fin)->format('Y-m-d H:i')
                : null,
            'activo' => $h->activo,
            'nota' => $h->nota,
            'created_at' => $h->created_at
                ? $h->created_at->format('Y-m-d H:i')
                : null,
        ];
    }

    private function format(WebPopupPromo $p): array
    {
        $pct = PopupPromoDiscount::parsePorcentaje(
            $p->descuento_texto ?? null,
            $p->descuento_porcentaje ?? null
        );

        return [
            'id_popup' => $p->id_popup,
            'titulo' => $p->titulo,
            'descuento_texto' => $p->descuento_texto,
            'descuento_porcentaje' => $pct,
            'subtitulo' => $p->subtitulo,
            'url_imagen' => $p->url_imagen ? asset('storage_/' . $p->url_imagen) : null,
            'btn_texto' => $p->btn_texto,
            'placeholder_email' => $p->placeholder_email,
            'placeholder_telefono' => $p->placeholder_telefono,
            'mostrar_email' => (bool) $p->mostrar_email,
            'mostrar_telefono' => (bool) $p->mostrar_telefono,
            'activo' => $p->activo,
            'fecha_inicio' => $p->fecha_inicio
                ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d\TH:i')
                : null,
            'fecha_fin' => $p->fecha_fin
                ? \Carbon\Carbon::parse($p->fecha_fin)->format('Y-m-d\TH:i')
                : null,
            'modo' => in_array($p->modo ?? '', ['formulario', 'imagen', 'oferta_tienda'], true)
                ? $p->modo
                : 'formulario',
            'delay_segundos' => $p->delay_segundos,
            'cookie_dias' => $p->cookie_dias,
            'cookie_unidad' => $p->cookie_unidad ?? 'dias',
            'color_acento' => $p->color_acento ?? '#121a36',
            'mostrar_opciones' => (bool) $p->mostrar_opciones,
            'opciones_label' => $p->opciones_label ?? '¿Qué te interesa?',
            'opciones' => ($p->opciones && $p->opciones !== '[]')
                ? (is_array($p->opciones) ? $p->opciones : json_decode($p->opciones, true))
                : [],
            'whatsapp_numero' => $p->whatsapp_numero ?? '',
            'whatsapp_mensaje' => $p->whatsapp_mensaje ?? '',
            'vigente' => PopupPromoDiscount::estaEnRangoFechas($p) && ($p->activo ?? 'N') === 'S',
        ];
    }
}

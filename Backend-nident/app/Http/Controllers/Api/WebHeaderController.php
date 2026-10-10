<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebFooter;
use App\Models\WebHeader;
use App\Support\CmsStorageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WebHeaderController extends Controller
{
    // Mismo respaldo que la web (header.blade.php)
    private const LOGO_FALLBACK = 'temp02/img/inicio/logo-valento.svg';

    public function obtener()
    {
        $data = WebHeader::find(1);
        if ($data) {
            $data = \App\Support\LiamPageData::syncAndPersistHeaderNav($data);
            $data->refresh();
        }

        return response()->json(['result' => $this->headerForApi($data)]);
    }

    /** Fuerza sincronización de submenús Inicio/Nosotros/Contacto con la web. */
    public function sincronizar_nav()
    {
        $data = WebHeader::firstOrCreate(
            ['id_header' => 1],
            [
                'Activo'    => 'S',
                'url_logo'  => 'temp02/img/inicio/logo-valento.svg',
                'nav_items' => [],
            ]
        );
        $data = \App\Support\LiamPageData::syncAndPersistHeaderNav($data);
        $data->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Submenús sincronizados con la web',
            'result'  => $this->headerForApi($data),
        ]);
    }

    public function actualizar(Request $request)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $data = WebHeader::firstOrCreate(
                ['id_header' => 1],
                [
                    'Activo'          => 'S',
                    'url_logo'        => 'temp02/img/inicio/logo-valento.svg',
                    'nav_items'       => [],
                    'telefono_label'  => 'Llámanos:',
                    'telefono_numero' => '',
                    'telefono_href'   => '',
                    'btn_texto'       => '',
                ]
            );

            $updateData = ['updated_at' => now()];

            if ($request->has('nav_items')) {
                $navItems = $request->input('nav_items');
                if (is_string($navItems)) {
                    $navItems = json_decode($navItems, true) ?? [];
                }
                // Tras guardar, re-sincroniza bloques dinámicos de Inicio preservando etiquetas editadas
                $updateData['nav_items'] = \App\Support\LiamPageData::syncHeaderNavItems(
                    is_array($navItems) ? $navItems : []
                );
            }

            if ($request->has('ticker_items')) {
                $tickerItems = $request->input('ticker_items');
                if (is_string($tickerItems)) {
                    $tickerItems = json_decode($tickerItems, true) ?? [];
                }
                $updateData['ticker_items'] = $tickerItems ?? [];
            }

            if ($request->has('ticker_speed')) {
                $speed = (int) $request->input('ticker_speed');
                // Segundos por ciclo: más alto = más lento
                $updateData['ticker_speed'] = max(15, min(120, $speed > 0 ? $speed : 45));
            }

            // Mensaje del centro de la barra superior y ciudad de envío son campos independientes
            foreach (['topbar_mensaje_centro' => ['topbar_mensaje_centro'], 'top_ubicacion' => ['top_ubicacion', 'topbar_ubicacion']] as $col => $keys) {
                $v = $this->firstInput($request, $keys);
                if ($v !== null) {
                    $updateData[$col] = $v;
                }
            }
            $waNumero = $this->firstInput($request, ['topbar_wa_numero', 'telefono_numero']);
            if ($waNumero !== null) {
                $updateData['topbar_wa_numero'] = $waNumero;
            }

            $waHref = $this->firstInput($request, ['topbar_wa_href', 'url_whatsapp_top']);
            if ($waHref !== null) {
                $updateData['url_whatsapp_top'] = $waHref;
            }

            foreach (['topbar_bgcolor', 'nav_bgcolor', 'nav_link_color', 'topbar_btn_bgcolor'] as $colorField) {
                if ($request->has($colorField)) {
                    $updateData[$colorField] = $request->input($colorField);
                }
            }

            foreach ([
                'top_email'            => ['topbar_correo', 'top_email'],
                'top_telefonos'        => ['topbar_celular', 'top_telefonos'],
                'url_contacto_top'     => ['topbar_cta_href', 'url_contacto_top'],
                'topbar_wa_texto'      => ['topbar_wa_texto'],
                'topbar_cta_texto'     => ['topbar_cta_texto'],
                'nav_promo_texto'      => ['nav_promo_texto'],
                'nav_promo_url'        => ['nav_promo_url'],
                'buscador_placeholder' => ['buscador_placeholder'],
                // Horario de la barra superior (va después de topbar_mensaje_centro para prevalecer)
                'topbar_promo'         => ['top_horario'],
                'telefono_label'       => ['telefono_label'],
                'telefono_numero'      => ['telefono_numero'],
                'telefono_href'        => ['telefono_href'],
                'btn_texto'            => ['btn_texto'],
                'etiqueta_hero_lateral'=> ['etiqueta_hero_lateral'],
                'btn_visitanos_texto'  => ['btn_visitanos_texto'],
                'btn_visitanos_url'    => ['btn_visitanos_url'],
                'btn_eventos_texto'    => ['btn_eventos_texto'],
                'btn_eventos_url'      => ['btn_eventos_url'],
            ] as $column => $keys) {
                $value = $this->firstInput($request, $keys);
                if ($value !== null) {
                    $updateData[$column] = $value;
                }
            }

            foreach (['side_menu_enlaces', 'redes_side'] as $jsonField) {
                if (!$request->has($jsonField)) {
                    continue;
                }
                $val = $request->input($jsonField);
                if (is_string($val)) {
                    $val = json_decode($val, true) ?? [];
                }
                $updateData[$jsonField] = $val ?? [];
            }

            if ($request->hasFile('image')) {
                // Borra el logo anterior solo si es una subida propia (CmsImageStorage protege temp02/, imagenes/...).
                \App\Support\CmsImageStorage::deleteIfOwned($data->url_logo);
                // Misma carpeta que los logos del footer (public/storage_/footer)
                $path = \App\Support\CmsImageStorage::store($request->file('image'), 'footer');
                $updateData['url_logo'] = $path;
            }

            $data->update($updateData);
            $data->refresh();

            // Redes sociales del sitio: se guardan en web_footer (las comparten header, footer y contacto)
            if ($request->has('footer_redes')) {
                $redes = $request->input('footer_redes');
                if (is_string($redes)) {
                    $redes = json_decode($redes, true) ?? [];
                }
                WebFooter::where('id_footer', 1)->update([
                    'footer_redes' => json_encode(array_values(is_array($redes) ? $redes : []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at'   => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'result'  => $this->headerForApi($data),
                'message' => 'Header actualizado correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar header: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Error al actualizar el header.'], 500);
        }
    }

    /** Primer valor presente en el request; null si ninguna clave fue enviada. */
    private function firstInput(Request $request, array $keys): mixed
    {
        foreach ($keys as $key) {
            if ($request->has($key)) {
                return $request->input($key);
            }
        }
        return null;
    }

    /** Misma resolución de logo que la web pública (storage + fallback temp02). */
    private function headerForApi(?WebHeader $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $arr = $data->toArray();
        // Mismo logo que muestra la web de Rossety
        $arr['url_logo_resolved'] = \App\Support\RossetyCatalog::logoUrl($data);
        $arr['categorias'] = \App\Support\RossetyCatalog::payload()['categorias'] ?? [];

        // Redes sociales (web_footer.footer_redes) para editarlas desde el panel del header
        $redes = WebFooter::where('id_footer', 1)->value('footer_redes');
        $arr['footer_redes'] = is_string($redes) ? (json_decode($redes, true) ?: []) : [];

        return $arr;
    }
}

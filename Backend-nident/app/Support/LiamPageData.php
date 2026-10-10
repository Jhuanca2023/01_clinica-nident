<?php

namespace App\Support;

use App\Models\MetadatosPagina;
use App\Models\OfertaDelDia;
use App\Models\Producto;
use App\Models\ProductoCategoria;
use App\Models\ProductoCategoriaSub;
use App\Models\Promociones;
use App\Models\WebCarrusel;
use App\Models\WebClienteLogo;
use App\Models\WebClienteSeccion;
use App\Models\WebContadores;
use App\Models\WebFooter;
use App\Models\WebFooterBanner;
use App\Models\WebHeader;
use App\Models\WebLookbook;
use App\Models\WebPromoBloque;
use App\Models\WebNuestroEquipo;
use App\Models\WebNosotrosQuienesBeneficio;
use App\Models\WebPaginaContacto;
use App\Models\WebPaginaNosotros;
use App\Models\WebPreguntasFrecuentes;
use App\Models\WebProductoDestacadoSeccion;
use App\Models\WebHomeColeccion;
use App\Models\WebHomeColeccionSeccion;
use App\Models\WebSlider;
use App\Models\WebSliderConfig;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LiamPageData
{
    /**
     * Submenú Inicio alineado con anclas de la home (#categoria, #bloqueN, #lookbook, #nuestros_clientes).
     * Conserva etiquetas personalizadas del CMS si el href coincide.
     *
     * @param  array<int, array<string, mixed>>  $existingChildren
     * @return array<int, array{label: string, href: string, url: string}>
     */
    public static function inicioNavChildren(array $existingChildren = []): array
    {
        return [];
    }

    /**
     * @return array{tienda: array<int, array{label: string, href: string, url: string}>}
     */
    public static function defaultSectionNavChildren(): array
    {
        // Valento: Nosotros y Contacto van sin submenú en la barra de navegación.
        return [
            'nosotros' => [],
            'contacto' => [],
        ];
    }

    /**
     * Sincroniza submenús del header (sin submenús).
     *
     * @param  array<int, mixed>|null  $navItems
     * @return array<int, array<string, mixed>>
     */
    public static function syncHeaderNavItems(?array $navItems): array
    {
        $items = is_array($navItems) ? $navItems : [];
        if ($items === []) {
            $items = [
                ['label' => 'Inicio', 'href' => '/', 'url' => '/'],
                ['label' => 'Tienda', 'href' => '/tienda', 'url' => '/tienda'],
                ['label' => 'Nosotros', 'href' => '/nosotros', 'url' => '/nosotros'],
                ['label' => 'Contacto', 'href' => '/contacto', 'url' => '/contacto'],
            ];
        }

        return array_values(array_map(static function ($item) {
            if (is_array($item)) {
                $item['children'] = [];
            }
            return $item;
        }, $items));
    }

    /**
     * Persiste nav_items sincronizados en web_header si cambiaron.
     */
    public static function syncAndPersistHeaderNav(?WebHeader $header): ?WebHeader
    {
        if ($header === null) {
            return null;
        }

        $current = is_array($header->nav_items) ? $header->nav_items : [];
        $synced = self::syncHeaderNavItems($current);
        if (json_encode($current) !== json_encode($synced)) {
            $header->nav_items = $synced;
            $header->updated_at = now();
            $header->save();
        }

        return $header;
    }

    /**
     * Productos home: todos los de web_home_coleccion; si la tabla está vacía, fallback al catálogo.
     */
    public static function listarProductosHome(?int $fallbackLimit = 8): array
    {
        if (Schema::hasTable('web_home_coleccion') && WebHomeColeccion::where('Activo', 'S')->exists()) {
            $idsOrden = WebHomeColeccion::where('Activo', 'S')
                ->orderBy('orden')
                ->pluck('orden', 'id_producto')
                ->toArray();

            $productos = Producto::query()
                ->with('categoria:id_producto_categoria,nombre')
                ->where('Activo', 'S')
                ->whereIn('id_producto', array_keys($idsOrden))
                ->get()
                ->sortBy(fn ($p) => $idsOrden[$p->id_producto] ?? 99)
                ->map(fn ($p) => (object) [
                    'id_producto'      => $p->id_producto,
                    'codigo_producto'  => $p->codigo_producto,
                    'codigo_barra'     => $p->codigo_barra ?? null,
                    'etiqueta_badge'   => $p->etiqueta_badge ?? null,
                    'titulo'           => $p->nombre,
                    'descripcion'      => $p->descripcion,
                    'precio'           => $p->precio,
                    'precio_old'       => $p->precio_old,
                    'stock'            => $p->stock,
                    'url_imagen'       => $p->url_imagen,
                    'slug'             => $p->slug,
                    'nombre_categoria' => $p->categoria?->nombre ?? '',
                    'numero_estrellas' => $p->numero_estrellas ?? 0,
                ])
                ->values()
                ->all();

            return $productos;
        }

        $query = Producto::query()
            ->with('categoria:id_producto_categoria,nombre')
            ->where('Activo', 'S')
            ->orderBy('id_producto');

        if ($fallbackLimit !== null) {
            $query->limit($fallbackLimit);
        }

        return $query
            ->get()
            ->map(fn ($p) => (object) [
                'id_producto'      => $p->id_producto,
                'codigo_producto'  => $p->codigo_producto,
                'codigo_barra'     => $p->codigo_barra ?? null,
                'etiqueta_badge'   => $p->etiqueta_badge ?? null,
                'titulo'           => $p->nombre,
                'descripcion'      => $p->descripcion,
                'precio'           => $p->precio,
                'precio_old'       => $p->precio_old,
                'stock'            => $p->stock,
                'url_imagen'       => $p->url_imagen,
                'slug'             => $p->slug,
                'nombre_categoria' => $p->categoria?->nombre ?? '',
                'numero_estrellas' => $p->numero_estrellas ?? 0,
            ])
            ->all();
    }

    /**
     * Marca es_favorito en listas de productos para el usuario autenticado.
     */
    public static function markFavoritos(array $products): array
    {
        if (empty($products)) {
            return $products;
        }

        foreach ($products as $producto) {
            $producto->es_favorito = false;
        }

        if (! Auth::check()) {
            return $products;
        }

        $idProductos = collect($products)->pluck('id_producto')->filter()->toArray();
        $favoritosIds = DB::table('web_favoritos')
            ->where('id_usuario', Auth::id())
            ->whereIn('id_producto', $idProductos)
            ->pluck('id_producto')
            ->toArray();

        foreach ($products as $producto) {
            $producto->es_favorito = in_array($producto->id_producto, $favoritosIds);
        }

        return $products;
    }

    public static function listarBannersPopulares()
    {
        if (! Schema::hasTable('web_populares_banners')) {
            return collect();
        }

        $activoCol = Schema::hasColumn('web_populares_banners', 'activo') ? 'activo' : 'Activo';

        return DB::table('web_populares_banners')
            ->when(Schema::hasColumn('web_populares_banners', $activoCol), fn ($q) => $q->where($activoCol, 'S'))
            ->orderBy('orden')
            ->get()
            ->map(fn ($b) => (object) [
                'titulo'      => $b->titulo_principal ?? $b->titulo ?? '',
                'descripcion' => $b->titulo_secundario ?? $b->texto_descuento ?? '',
                'url_imagen'  => $b->url_imagen ?? null,
            ]);
    }

    public static function img(?string $path, ?string $fallback = null): string
    {
        if (empty($path)) {
            return $fallback ? asset($fallback) : '';
        }
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return CmsStorageUrl::forWeb($path, $fallback);
    }

    /** WhatsApp del header CMS; si no hay, usa el del footer corporativo. */
    public static function headerWhatsappUrl(?WebHeader $header, ?object $footer = null): string
    {
        if ($header) {
            foreach (['url_whatsapp_top', 'topbar_wa_href'] as $field) {
                $url = trim((string) ($header->{$field} ?? ''));
                if ($url !== '' && preg_match('#(wa\.me|api\.whatsapp\.com|web\.whatsapp\.com)#i', $url)) {
                    return $url;
                }
            }

            $numero = trim((string) ($header->topbar_wa_numero ?? $header->telefono_numero ?? ''));
            if ($numero !== '') {
                return self::whatsappUrlFromPhone($numero);
            }
        }

        return \Helpers::landingWhatsappUrl($footer);
    }

    /** Convierte un número (+51 970…) en URL wa.me. */
    public static function whatsappUrlFromPhone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if ($digits === '') {
            return '';
        }

        return 'https://wa.me/' . ltrim($digits, '0');
    }

    /**
     * Redes sociales del sitio (web_footer.footer_redes): las usan header, footer y contacto.
     * Devuelve [['tipo', 'url', 'label']] ordenadas y con URL válida; null si nunca se configuraron.
     *
     * @return array<int, array{tipo: string, url: string, label: string}>|null
     */
    public static function redesSociales(?WebFooter $footer): ?array
    {
        if ($footer === null || $footer->footer_redes === null || $footer->footer_redes === '') {
            return null;
        }

        $decoded = is_array($footer->footer_redes)
            ? $footer->footer_redes
            : (json_decode((string) $footer->footer_redes, true) ?: []);

        usort($decoded, fn ($a, $b) => ($a['orden'] ?? 0) <=> ($b['orden'] ?? 0));

        $redes = [];
        foreach ($decoded as $r) {
            $url = trim((string) ($r['url'] ?? ''));
            if ($url === '') {
                continue;
            }
            $tipo = strtolower((string) ($r['tipo'] ?? 'link'));
            // WhatsApp: si guardaron solo el número, armar el enlace wa.me
            if ($tipo === 'whatsapp' && ! preg_match('#^https?://#i', $url)) {
                $url = self::whatsappUrlFromPhone($url);
                if ($url === '') {
                    continue;
                }
            }
            $redes[] = ['tipo' => $tipo, 'url' => $url, 'label' => $r['etiqueta'] ?? ucfirst($tipo)];
        }

        return $redes;
    }

    public static function layout(string $paginaActiva = ''): array
    {
        $footerRow = WebFooter::where('id_footer', 1)->where('Activo', 'S')->first();
        $footerData = $footerRow ? collect([$footerRow]) : collect();
        $webHeader = WebHeader::find(1);

        return [
            'webHeader'    => $webHeader,
            'footerData'   => $footerData,
            'footerCorp'   => $footerRow,
            'urlWhatsapp'  => self::headerWhatsappUrl($webHeader, $footerRow),
            'paginaActiva' => $paginaActiva,
            'cartCount'    => Cart::count(),
            'menuCategorias' => self::menuCategorias(),
        ];
    }

    /**
     * Estructura de categorías, subcategorías y bloques de ofertas para el mega-menú
     * del header. Misma data que la barra lateral de la tienda, con URLs navegables.
     *
     * @return array{categorias: array<int,array>, bloques: array<int,array>}
     */
    public static function menuCategorias(): array
    {
        $catTable = 'administracion_producto_categoria';
        $subTable = 'administracion_producto_categoria_sub';
        $prodTable = 'administracion_producto';

        // Conteo de productos activos por categoría / subcategoría
        $countCat = DB::table($prodTable)
            ->select('id_producto_categoria', DB::raw('COUNT(*) as t'))
            ->where('Activo', 'S')
            ->groupBy('id_producto_categoria')
            ->pluck('t', 'id_producto_categoria');

        $countSub = DB::table($prodTable)
            ->select('id_producto_categoria_sub', DB::raw('COUNT(*) as t'))
            ->where('Activo', 'S')
            ->whereNotNull('id_producto_categoria_sub')
            ->groupBy('id_producto_categoria_sub')
            ->pluck('t', 'id_producto_categoria_sub');

        // Categorías activas (con productos)
        $catQuery = ProductoCategoria::where('Activo', 'S');
        Schema::hasColumn($catTable, 'orden')
            ? $catQuery->orderBy('orden')->orderBy('nombre')
            : $catQuery->orderBy('nombre');

        $categorias = [];
        foreach ($catQuery->get() as $cat) {
            $idCat = (int) $cat->id_producto_categoria;
            if ((int) ($countCat[$idCat] ?? 0) <= 0) {
                continue;
            }

            $subQuery = ProductoCategoriaSub::where('id_producto_categoria', $idCat)
                ->where('Activo', 'S');
            Schema::hasColumn($subTable, 'orden')
                ? $subQuery->orderBy('orden')->orderBy('nombre')
                : $subQuery->orderBy('nombre');

            $subs = [];
            foreach ($subQuery->get() as $sub) {
                $idSub = (int) $sub->id_producto_categoria_sub;
                if ((int) ($countSub[$idSub] ?? 0) <= 0) {
                    continue;
                }
                $subs[] = [
                    'nombre' => $sub->nombre,
                    'url'    => CategoriaTiendaUrl::tienda($cat, $sub),
                ];
            }

            $categorias[] = [
                'id'     => $idCat,
                'nombre' => $cat->nombre,
                'url'    => CategoriaTiendaUrl::tienda($cat),
                'subs'   => $subs,
            ];
        }

        // Bloques de ofertas/promociones con sus submódulos (items)
        $bloquesMenu = [];
        if (Schema::hasTable('web_promo_bloque')) {
            $bloques = WebPromoBloque::with(['items' => function ($q) {
                $q->where('Activo', 'S')->orderBy('orden');
            }])
                ->where('Activo', 'S')
                ->orderBy('orden')
                ->orderBy('id_promo_bloque')
                ->get();

            $itemIds = $bloques->flatMap(fn ($b) => $b->items->pluck('id_promo_item'))
                ->filter()->unique()->values()->all();
            $countItem = [];
            if (! empty($itemIds) && Schema::hasTable('web_promo_item_producto')) {
                $countItem = DB::table('web_promo_item_producto')
                    ->select('id_promo_item', DB::raw('COUNT(*) as t'))
                    ->whereIn('id_promo_item', $itemIds)
                    ->groupBy('id_promo_item')
                    ->pluck('t', 'id_promo_item')
                    ->map(fn ($n) => (int) $n)
                    ->all();
            }

            foreach ($bloques as $bloque) {
                $idB = (int) $bloque->id_promo_bloque;
                $items = [];
                foreach ($bloque->items as $item) {
                    $idItem = (int) $item->id_promo_item;
                    $total = (int) ($countItem[$idItem] ?? 0);
                    if ($total === 0 && (int) ($item->id_producto ?? 0) > 0) {
                        $total = 1;
                    }
                    if ($total === 0) {
                        continue;
                    }
                    $items[] = [
                        'nombre' => $item->nombre ?: ($item->categoria ?: 'Submódulo'),
                        'url'    => url('/tienda') . '?filtros=' . urlencode('promo-' . $idB . '-' . $idItem),
                    ];
                }

                if (empty($items)) {
                    continue;
                }

                $bloquesMenu[] = [
                    'id'     => $idB,
                    'nombre' => trim((string) ($bloque->subtitulo_seccion ?: $bloque->titulo_seccion ?: 'Ofertas')),
                    'url'    => PromoTiendaUrl::tiendaOfertas($bloque),
                    'items'  => $items,
                ];
            }
        }

        return [
            'categorias' => $categorias,
            'bloques'    => $bloquesMenu,
        ];
    }

    public static function home(): array
    {
        $metaData = MetadatosPagina::metaVigente('home');

        $productos = self::markFavoritos(Producto::enhanceList(self::listarProductosHome()));

        $catQuery = ProductoCategoria::where('Activo', 'S');
        if (Schema::hasColumn('administracion_producto_categoria', 'mostrar_panel_principal')) {
            $catQuery->where('mostrar_panel_principal', 'S');
        }
        if (Schema::hasColumn('administracion_producto_categoria', 'orden')) {
            $catQuery->orderBy('orden');
        } else {
            $catQuery->orderByDesc('id_producto_categoria');
        }
        $categorias = $catQuery->get();

        // Productos activos por categoría: permite avisar cuando una categoría está vacía
        $conteoPorCategoria = $categorias->isEmpty()
            ? collect()
            : DB::table('administracion_producto')
                ->select('id_producto_categoria', DB::raw('COUNT(*) as total'))
                ->where('Activo', 'S')
                ->whereIn('id_producto_categoria', $categorias->pluck('id_producto_categoria')->all())
                ->groupBy('id_producto_categoria')
                ->pluck('total', 'id_producto_categoria');

        $categorias->each(function ($cat) use ($conteoPorCategoria) {
            $cat->productos_count = (int) ($conteoPorCategoria[$cat->id_producto_categoria] ?? 0);
        });

        $banners = self::listarBannersPopulares();

        $promociones = Schema::hasTable('oferta_promociones')
            ? Promociones::where('Activo', 'S')->orderBy('id_promociones')->limit(8)->get()
            : collect();

        $ofertas = Schema::hasTable('oferta_del_dia')
            ? OfertaDelDia::where('Activo', 'S')
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->orderBy('id_oferta_dia')
                ->limit(4)
                ->get()
            : collect();

        $promoBloques = Schema::hasTable('web_promo_bloque')
            ? WebPromoBloque::with(array_filter([
                'items',
                'productosVinculados' => fn ($q) => $q->select(
                    'administracion_producto.id_producto',
                    'administracion_producto.nombre',
                    'administracion_producto.slug'
                ),
                Schema::hasTable('web_promo_item_producto') ? 'items.productosVinculados' : null,
            ]))->where('Activo', 'S')->orderBy('orden')->get()
            : collect();

        $seccionDestacada = Schema::hasTable('web_home_coleccion_seccion')
            ? WebHomeColeccionSeccion::firstOrCreate(
                ['id' => 1],
                ['seccion_titulo' => 'Líneas Valento', 'seccion_subtitulo' => 'Mayorista · stock permanente']
              )
            : (Schema::hasTable('web_producto_destacado_seccion')
                ? WebProductoDestacadoSeccion::firstOrCreate(['id' => 1])
                : null);

        return array_merge(self::layout('home'), [
            'metaData'         => $metaData,
            'sliderData'       => WebSlider::where('Activo', 'S')->forHeroCarousel()->orderBy('id_slider')->get(),
            'sliderConfig'     => Schema::hasTable('web_slider_config') ? WebSliderConfig::first() : null,
            'categorias'       => $categorias,
            'productos'        => $productos,
            'seccionDestacada' => $seccionDestacada,
            'banners'        => $banners,
            'promociones'    => $promociones,
            'ofertas'        => $ofertas,
            'promoBloques'   => $promoBloques,
            'lookbook'       => Schema::hasTable('web_lookbook') ? WebLookbook::find(1) : null,
            'footerBanner'   => Schema::hasTable('web_footer_banner') ? WebFooterBanner::find(1) : null,
            'logoCarrusel'    => WebCarrusel::where('Activo', 'S')->orderBy('orden')->get(),
            'clientesSeccion' => Schema::hasTable('web_cliente_seccion')
                ? WebClienteSeccion::firstOrCreate(['id' => 1], [
                    'tag'         => 'Nuestros Clientes',
                    'titulo'      => 'Marcas que confían en nosotros',
                    'descripcion' => 'Empresas y tiendas que eligen la calidad Valento en todo el Perú.',
                ])
                : null,
            'clientesLogos'   => Schema::hasTable('web_cliente_logo')
                ? WebClienteLogo::where('Activo', 'S')->orderBy('orden')->get()
                : collect(),
        ]);
    }

    public static function about(): array
    {
        $pagina = WebPaginaNosotros::first();

        $contadoresQuery = WebContadores::where('Activo', 'S')->orderBy('orden');
        if (Schema::hasColumn('web_contadores', 'contexto')) {
            $contadoresQuery->where(function ($q) {
                $q->where('contexto', 'nosotros')->orWhereNull('contexto');
            });
        }
        $contadores = $contadoresQuery->get();

        $valores = $pagina && $pagina->valores_json
            ? (is_string($pagina->valores_json) ? json_decode($pagina->valores_json, true) : $pagina->valores_json)
            : [];
        $timeline = $pagina && $pagina->timeline_json
            ? (is_string($pagina->timeline_json) ? json_decode($pagina->timeline_json, true) : $pagina->timeline_json)
            : [];

        return array_merge(self::layout('nosotros'), [
            'metaData'          => MetadatosPagina::metaVigente('web_about') ?? MetadatosPagina::metaVigente('home'),
            'pagina'            => $pagina,
            'equipo'            => WebNuestroEquipo::where('Activo', 'S')->orderBy('orden')->get(),
            'contadores'        => $contadores,
            'historiaItems'     => Schema::hasTable('web_nosotros_quienes_beneficio')
                ? WebNosotrosQuienesBeneficio::where('Activo', 'S')->orderBy('orden')->get()
                : collect(),
            'valores'           => collect($valores ?? []),
            'timeline'          => collect($timeline ?? []),
        ]);
    }

    public static function auth(string $tab = 'login'): array
    {
        return array_merge(self::layout($tab), [
            'metaData' => MetadatosPagina::metaVigente('home'),
            'authTab'  => $tab,
        ]);
    }

    public static function contacto(): array
    {
        $faqs = Schema::hasTable('web_preguntas_frecuentes')
            ? WebPreguntasFrecuentes::where('Activo', 'S')->orderBy('orden')->get()
            : collect();

        $paginaContacto = Schema::hasTable('web_pagina_contacto')
            ? WebPaginaContacto::find(1) ?? WebPaginaContacto::first()
            : null;

        $layout = self::layout('contacto');

        // Prioridad: número CMS de página contacto (admin "Número botón chatear")
        $waDesdeContacto = self::whatsappUrlFromPhone($paginaContacto->telefono ?? null);
        if ($waDesdeContacto !== '') {
            $layout['urlWhatsapp'] = $waDesdeContacto;
        }

        return array_merge($layout, [
            'metaData'       => MetadatosPagina::metaVigente('web_contact') ?? MetadatosPagina::metaVigente('home'),
            'paginaContacto' => $paginaContacto,
            'faqs'           => $faqs,
        ]);
    }

    public static function legal(string $tipo): array
    {
        $docId = match ($tipo) {
            'privacidad' => 3,
            'reclamaciones' => 4,
            default => 2,
        };

        $terminosData = DB::table('web_terminos')->where('id_footer_document', $docId)->get();

        return array_merge(self::layout($tipo), [
            'metaData'     => MetadatosPagina::metaVigente('web_terminos_condiciones'),
            'terminosData' => $terminosData,
            'legalTipo'    => $tipo,
        ]);
    }

    public static function thankYou(array $orderData): array
    {
        return array_merge(self::layout('thankyou'), $orderData);
    }
}

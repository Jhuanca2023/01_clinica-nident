<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MetadatosPagina;
use App\Models\WebAbout;
use App\Models\WebAboutContador;
use App\Models\WebNosotrosProducto;
use App\Models\WebNosotrosProductoSeccion;
use App\Models\WebNosotrosQuienes;
use App\Models\WebNosotrosQuienesBeneficio;
use App\Models\WebContactoColumna;
use App\Models\WebFooter;
use App\Models\WebHeader;
use App\Models\WebPaginaContacto;
use App\Models\WebPaginaNosotros;
use App\Models\WebPaginaProductos;
use App\Models\WebPaginaMaquinarias;
use App\Models\WebMaquinariasItem;
use App\Models\WebPaginaVideos;
use App\Models\WebVideos;
use App\Models\WebProductosPaginaGaleria;
use App\Models\WebProductosPaginaGaleriaSeccion;
use App\Support\LiamPageData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WebPaginasController extends Controller
{
    private function bannerAsset(?string $path, array $fallbacks = []): string
    {
        return \Helpers::cmsBannerUrl($path, $fallbacks);
    }

    private function layoutData(string $paginaActiva = ''): array
    {
        $footerData = DB::table('web_footer')->where('id_footer', 1)->where('Activo', 'S')->get();
        $footerCorp = $footerData->first();
        $webHeader = WebHeader::find(1);

        return [
            'webHeader'    => $webHeader,
            'footerData'   => $footerData,
            'footerCorp'   => $footerCorp,
            'urlWhatsapp'  => LiamPageData::headerWhatsappUrl($webHeader, $footerCorp),
            'paginaActiva' => $paginaActiva,
        ];
    }

    public function nosotros()
    {
        $metaData = MetadatosPagina::metaVigente('web_about') ?? MetadatosPagina::metaVigente('home');
        $pagina = WebPaginaNosotros::first() ?? new WebPaginaNosotros([
            'banner_titulo'    => 'Nosotros',
            'banner_subtitulo' => 'Valento',
            'banner_url_imagen'=> 'temp02/assets/img/jh_importaciones/inicio/seccion%202.jpg',
        ]);

        $webAbout = WebAbout::where('Activo', 'S')->orderBy('id_about')->first();

        $nosotrosQuienes = Schema::hasTable('web_nosotros_quienes')
            ? WebNosotrosQuienes::where('Activo', 'S')->first()
            : null;

        $nosotrosQuienesBeneficios = Schema::hasTable('web_nosotros_quienes_beneficio')
            ? WebNosotrosQuienesBeneficio::where('Activo', 'S')->orderBy('orden')->get()
            : collect();

        $nosotrosContadores = $webAbout && Schema::hasTable('web_about_contador')
            ? WebAboutContador::where('id_about', $webAbout->id_about)->where('Activo', 'S')->orderBy('orden')->get()
            : collect();

        $nosotrosProductoSeccion = Schema::hasTable('web_nosotros_producto_seccion')
            ? WebNosotrosProductoSeccion::first()
            : null;

        $nosotrosProductos = Schema::hasTable('web_nosotros_producto')
            ? WebNosotrosProducto::where('Activo', 'S')->orderBy('orden')->get()
            : collect();

        $bannerImagenUrl = $this->bannerAsset($pagina->banner_url_imagen ?? null, array_filter([
            $pagina->catalogo_url_imagen ?? null,
            $nosotrosQuienes?->url_imagen ?? null,
        ]));

        return view('web.pages.nosotros', array_merge($this->layoutData('nosotros'), compact(
            'metaData',
            'pagina',
            'bannerImagenUrl',
            'nosotrosQuienes',
            'nosotrosQuienesBeneficios',
            'nosotrosContadores',
            'nosotrosProductoSeccion',
            'nosotrosProductos'
        )));
    }

    public function videos()
    {
        $metaData = MetadatosPagina::metaVigente('web_videos') ?? MetadatosPagina::metaVigente('home');
        $pagina = WebPaginaVideos::first() ?? new WebPaginaVideos([
            'banner_titulo'       => 'Videos',
            'seccion_titulo'      => 'Creando Espirales',
            'seccion_descripcion' => 'Descubre cómo nuestras espirales de alta calidad pueden transformar tus proyectos.',
        ]);

        $videos = Schema::hasTable('web_videos')
            ? WebVideos::where('Activo', 'S')->orderBy('orden')->get()
            : collect();

        return view('web.pages.videos', array_merge($this->layoutData('videos'), compact(
            'metaData',
            'pagina',
            'videos'
        )));
    }

    public function maquinarias()
    {
        $metaData = MetadatosPagina::metaVigente('web_maquinarias') ?? MetadatosPagina::metaVigente('home');
        $pagina = WebPaginaMaquinarias::first() ?? new WebPaginaMaquinarias([
            'banner_titulo'  => 'Máquinarias',
            'seccion_titulo' => 'Nuestras máquinarias',
        ]);

        $maquinariasItems = Schema::hasTable('web_maquinarias_item')
            ? WebMaquinariasItem::where('Activo', 'S')->orderBy('orden')->get()
            : collect();

        return view('web.pages.maquinarias', array_merge($this->layoutData('maquinarias'), compact(
            'metaData',
            'pagina',
            'maquinariasItems'
        )));
    }

    public function productos()
    {
        $metaData = MetadatosPagina::metaVigente('web_productos') ?? MetadatosPagina::metaVigente('home');
        $pagina = WebPaginaProductos::first() ?? new WebPaginaProductos([
            'banner_titulo' => 'Productos',
            'intro_titulo'  => 'Nuestros Productos',
        ]);

        $productosGaleriaSeccion = Schema::hasTable('web_productos_pagina_galeria_seccion')
            ? WebProductosPaginaGaleriaSeccion::first()
            : null;

        $productosGaleria = Schema::hasTable('web_productos_pagina_galeria')
            ? WebProductosPaginaGaleria::where('Activo', 'S')->orderBy('orden')->get()
            : collect();

        return view('web.pages.productos', array_merge($this->layoutData('productos'), compact(
            'metaData',
            'pagina',
            'productosGaleriaSeccion',
            'productosGaleria'
        )));
    }

    public function contacto()
    {
        $metaData = MetadatosPagina::metaVigente('web_contact') ?? MetadatosPagina::metaVigente('home');
        $paginaContacto = WebPaginaContacto::first() ?? new WebPaginaContacto([
            'banner_titulo'    => 'Contáctanos',
            'banner_subtitulo' => 'Estamos para ayudarte',
            'banner_url_imagen'=> 'temp02/assets/img/jh_importaciones/inicio/jh_importaciones_img_5.jpg',
        ]);

        $contactoColumnas = Schema::hasTable('web_contacto_columna')
            ? WebContactoColumna::where('Activo', 'S')->orderBy('orden')->get()
            : collect();

        return view('web.pages.contacto', array_merge($this->layoutData('contacto'), compact(
            'metaData',
            'paginaContacto',
            'contactoColumnas'
        )));
    }
}

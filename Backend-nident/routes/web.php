<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LiamVercceliController;
use App\Http\Controllers\Web\RossetyController;
use App\Http\Controllers\Web\WebPaginasController;
use App\Http\Controllers\Web\AboutController;
use App\Http\Controllers\Web\TerminosController;
use App\Http\Controllers\Web\ShopDetailController;
use App\Http\Controllers\Web\GaleriaController;
use App\Http\Controllers\Web\WishlistController;
use App\Http\Controllers\Web\PedidosController;
use App\Http\Controllers\Web\ContactoController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\LibroReclamosController;
use App\Http\Controllers\CulqiPaymentController;
use App\Http\Controllers\Web\CalcularEnvioController;
use App\Http\Controllers\Web\FileController;
use App\Http\Controllers\Web\SuscriptoresController;
use App\Http\Controllers\Api\WebPopupPromoController;
use App\Models\Producto;
use App\Models\ProductoCategoria;
use App\Models\ProductoCategoriaSub;
use App\Models\WebPromoBloque;
use App\Support\CategoriaTiendaUrl;

if (! function_exists('adminSpaResponse')) {
    /**
     * Local (APP_ENV=local): redirige a Vite :5173 — no usa public/admin.
     * Probar build local: ADMIN_DIST_PATH=C:/.../systemWeb-Frontend/dist
     * Producción: public/admin/index.html
     */
    function adminSpaResponse(?string $any = null)
    {
        if (app()->environment('local') && ! env('ADMIN_DIST_PATH')) {
            $frontend = rtrim(env('APP_URL_FROND', 'http://localhost:5173'), '/');
            $path = '/admin' . ($any ? '/'.$any : '');
            $qs = request()->getQueryString();

            return redirect()->away($frontend . $path . ($qs ? '?'.$qs : ''));
        }

        $devPath = env('ADMIN_DIST_PATH');
        $indexHtml = $devPath
            ? rtrim($devPath, '/\\') . '/index.html'
            : public_path('admin/index.html');

        if (! file_exists($indexHtml)) {
            abort(404, 'Panel admin no encontrado. Ejecute pnpm run build:production en systemWeb-Frontend.');
        }

        return response(file_get_contents($indexHtml), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//::::::::::::::::::::::: WEB : HOME ::::::::::::::::::::::::::
Route::get('/',                                       [RossetyController::class, 'inicio'])->name('home');
Route::get('/buscar',                                 \App\Http\Controllers\Web\SiteSearchController::class)->name('site.search');

Route::get('/nosotros',                               [RossetyController::class, 'nosotros'])->name('nosotros');
Route::get('/productos',                              [WebPaginasController::class, 'productos'])->name('productos');
Route::get('/videos',                                 [WebPaginasController::class, 'videos'])->name('videos');
Route::get('/maquinarias',                            [WebPaginasController::class, 'maquinarias'])->name('maquinarias');
Route::get('/contacto',                               [RossetyController::class, 'contacto'])->name('contacto.pagina');
Route::post('/contacto',                              [ContactoController::class, 'submitPagina'])->name('contacto.pagina.submit');
Route::redirect('/about', '/nosotros', 301);
// Route::get('/home',                                   [HomeController::class, 'Home']);
//::::::::::::::::::::::: WEB: LOGEO ::::::::::::::::::::::::::
Auth::routes();
Route::get('/web_session_login',                    [HomeController::class, 'web_session_login']);

// GET/POST login: Auth::routes() → Auth\LoginController + vista auth.login
Route::get('registration',                          [AuthController::class, 'registration'])->name('register');
Route::post('post-registration',                    [AuthController::class, 'postRegistration'])->name('register.post');
// Route::get('home',                                  [AuthController::class, 'dashboard']);
Route::get('logout',                                [AuthController::class, 'logout'])->name('logout');

// VERIFICARCION POR CORREO
Route::get('home',                                    [AuthController::class, 'dashboard'])->middleware(['auth', 'is_verify_email']);
Route::get('account/verify/{token}',                  [AuthController::class, 'verifyAccount'])->name('user.verify');

// RECUPERAR CONTRASEÑA
Route::get('forget-password',                         [ForgotPasswordController::class, 'showForgetPasswordForm'])->name('forget.password.get');
Route::post('forget-password',                        [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forget.password.post');
Route::get('reset-password/{token}',                  [ForgotPasswordController::class, 'showResetPasswordForm'])->name('reset.password.get');
Route::post('reset-password',                         [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');

//::::::::::::::::::::::: WEB: HOME ::::::::::::::::::::::::::
Route::get('/web_about',                            [AboutController::class, 'web_about']);
Route::get('/web_gallery_list',                     [HomeController::class, 'web_gallery_list']);
Route::get('/web_gallery_2cols',                    [HomeController::class, 'web_gallery_2cols']);
Route::get('/web_gallery_4cols',                    [HomeController::class, 'web_gallery_4cols']);
Route::get('/web_ubicacion',                        [HomeController::class, 'web_ubicacion']);
Route::get('/web_buscar_productos',                 [\App\Http\Controllers\Web\ProductoLiveSearchController::class, 'suggest'])->name('products.search');
Route::get('/web_buscar_productos/resultados',      [\App\Http\Controllers\Web\ProductoLiveSearchController::class, 'search'])->name('products.search.full');
Route::post('/web_customizer_guardar',              [HomeController::class, 'customizerGuardar'])->name('customizer.guardar');
Route::post('/web_home_coleccion_seccion',          [\App\Http\Controllers\Web\WebHomeColeccionSeccionController::class, 'update'])->middleware('auth');
Route::post('/web_customizer_reset',                [HomeController::class, 'customizerReset'])->name('customizer.reset');
Route::get('/web_myAccount/{codigo}/{total}',       [HomeController::class, 'web_myAccount']);
Route::get('/thank-you',                            [RossetyController::class, 'gracias'])->name('gracias.compra');
Route::get('/gracias',                              [RossetyController::class, 'gracias'])->name('rossety.gracias');
Route::get('/carrito',                              [RossetyController::class, 'carrito'])->name('rossety.carrito');
Route::get('/checkout',                             [RossetyController::class, 'checkout'])->name('rossety.checkout');
Route::post('/rossety/pedido',                      [RossetyController::class, 'pedido'])->name('rossety.pedido');
Route::get('/producto/{slug?}',                     [RossetyController::class, 'producto'])->name('rossety.producto');
Route::get('/terminos-condiciones',                 [LiamVercceliController::class, 'terminos'])->name('liam.terminos');
Route::get('/politica-privacidad',                  [LiamVercceliController::class, 'privacidad'])->name('liam.privacidad');
Route::get('/guia-de-tallas',                       [LiamVercceliController::class, 'guiaTallas'])->name('liam.guia_tallas');
Route::get('/libro-reclamaciones',                  [LibroReclamosController::class, 'web_libro_reclamos'])->name('liam.reclamaciones');
Route::get('/tienda/ofertas/{promoSlug}/{productoSlug?}', [GaleriaController::class, 'web_galeria_promo'])->name('tienda.promo');
Route::get('/tienda/{category?}/{subcategory?}',    [RossetyController::class, 'tienda'])->name('tienda');


//::::::::::::::::::::::: WEB: CONTACTO ::::::::::::::::::::::::::
Route::redirect('/web_contact', '/contacto', 301);
Route::post('/web_contact_submit',                                  [ContactoController::class, 'submitForm'])->name('contact.submit');

//===== DETALLE TERMINOS CONDICIONES ======
Route::get('/web_terminos',                                         [TerminosController::class, 'web_terminos']);

//===== DETALLE PRODUCTO ======
Route::get('/web_shopDetail/{slug}',                                function (string $slug) {
    return redirect('/producto/'.$slug);
})->name('product.detail');
Route::post('/web_shopDetail_agregar',                              [ShopDetailController::class, 'web_shopDetail_agregar']);
Route::redirect('/web_shopDetail_lista', '/shop-detail-lista', 301);
Route::post('/web_shopDetail_eliminar',                             [ShopDetailController::class, 'web_shopDetail_eliminar']);
Route::post('/web_shopDetail_actualizar',                           [ShopDetailController::class, 'web_shopDetail_actualizar']);
Route::post('/web_whatsapp_pedido',                                 [ShopDetailController::class, 'whatsappPedido']);
Route::post('/web_whatsapp_pedido_producto',                        [ShopDetailController::class, 'whatsappPedidoProducto']);
// Route::get('/web_get_cart_data',                                    [ShopDetailController::class, 'web_get_cart_data']);
Route::get('/web_shopDetail_total_carrito',                         [ShopDetailController::class, 'getTotalCarrito'])->name('web.shopDetail.totalCarrito');
Route::get('/web_cart_items',                                       [ShopDetailController::class, 'getCartItems'])->name('web.cart.items');
Route::get('/web_shopDetail_sugeridos/{id}',                       [ShopDetailController::class, 'getProductosSugeridos']);


//===== AGRAGAR LISTA DE DESEOS ======
Route::get('/web_wishlist',                                         [WishlistController::class, 'web_wishlist']);
Route::post('/web_wishlist_agregar',                                [WishlistController::class, 'web_wishlist_agregar']);
Route::post('/web_wishlist_eliminar',                               [WishlistController::class, 'web_wishlist_eliminar']);
Route::post('/web_popup_aplicar_descuento',                         [WebPopupPromoController::class, 'aplicarDescuento'])->name('web.popup.aplicar');

//===== DETALLE GALERIA ======
Route::get('/web_gallery/{category?}/{subcategory?}',               [GaleriaController::class, 'web_galeria_listar'])->name('web_gallery');

// Callback / Webhook
// Izipay puede notificarte cuando el pago se complete. Configura una ruta en web.php:
Route::controller(GoogleController::class)->group(function () {
  Route::get('auth/google', 'redirectToGoogle')->name('auth.google');
  Route::get('auth/google/callback', 'handleGoogleCallback');
});

//::::::::::::::::::::::: WEB: LIBRO DE RECLAMOS ::::::::::::::::::::::::::
Route::get('/web_libro_reclamos',                                   [LibroReclamosController::class, 'web_libro_reclamos']);
Route::post('/web_libro_reclamos_enviar',                           [LibroReclamosController::class, 'web_libro_reclamos_enviar'])->name('libro_reclamos.enviar');

//===== DETALLE PRODUCTO : ENVIOS ======
Route::get('/web_calcular_envio/{department}/lista_distritos',      [CalcularEnvioController::class, 'lista_distritos']);
Route::post('/web_calcular_envio/calcular_envio',                   [CalcularEnvioController::class, 'calcular_envio']);


// ========= Es una ruta para el compilador de admin ==========================
// Dev:  ADMIN_DIST_PATH=C:/xampp/htdocs_pro/01-miweb/miweb-Frontend/dist
// Prod: dejar vacío → usa public/admin automáticamente
Route::get('/admin/{any?}', function () {
    if (app()->environment('local') && ! env('ADMIN_DIST_PATH')) {
        $frontend = rtrim(env('APP_URL_FROND', 'http://localhost:5173'), '/');
        $any = request()->route('any') ?? '';
        $path = '/admin' . ($any ? '/'.$any : '');
        $qs = request()->getQueryString();

        return redirect()->away($frontend . $path . ($qs ? '?'.$qs : ''));
    }

    $devPath = env('ADMIN_DIST_PATH');
    $indexHtml = $devPath
        ? rtrim($devPath, '/\\') . '/index.html'
        : public_path('admin/index.html');
    // En local, si el build del admin no existe, redirigir al Vite dev server (5173)
    if (!file_exists($indexHtml)) {
        $frontend = rtrim(env('APP_URL_FROND', 'http://localhost:5173'), '/');
        $any = request()->route('any') ?? '';
        $path = '/admin' . ($any ? '/'.$any : '');
        $qs = request()->getQueryString();
        $url = $frontend . $path . ($qs ? ('?'.$qs) : '');
        return redirect()->away($url);
    }
    return file_get_contents($indexHtml);
})->where('any', '.*');

Route::get('/admin', function () {
    if (app()->environment('local') && ! env('ADMIN_DIST_PATH')) {
        $frontend = rtrim(env('APP_URL_FROND', 'http://localhost:5173'), '/');

        return redirect()->away($frontend . '/admin');
    }

    $devPath = env('ADMIN_DIST_PATH');
    $indexHtml = $devPath
        ? rtrim($devPath, '/\\') . '/index.html'
        : public_path('admin/index.html');
    if (!file_exists($indexHtml)) {
        $frontend = rtrim(env('APP_URL_FROND', 'http://localhost:5173'), '/');
        return redirect()->away($frontend . '/admin');
    }
    return file_get_contents($indexHtml);
});

//=============================== PROCESOS DE PAGO CULQUI===============================
//***************************************************************************** */
Auth::routes(["verify" => true]);

Route::get('/web_checkout/index',                           fn () => redirect('/checkout'));
Route::get('/web_checkout/izipay-form',                     [CheckoutController::class, 'formularioIzipay'])->name('web_checkout.izipay_form');
Route::post('/web_checkout/generar-formulario-culqi',       [CheckoutController::class, 'generarFormularioCulqi']);
Route::post('/web_checkout/enviar_pago_con_culqi',          [CheckoutController::class, 'enviar_pago_con_culqi']);
Route::post('/web_checkout/enviar_pago_sin_culqi',          [CheckoutController::class, 'enviar_pago_sin_culqi']);
Route::post('/web_checkout/enviar_pedido_whatsapp',         [CheckoutController::class, 'enviar_pedido_whatsapp']);
Route::post('/web_checkout/generar-formulario-izipay',      [CheckoutController::class, 'generarFormularioIzipay']);
Route::post('/web_checkout/enviar_pago_con_izipay',         [CheckoutController::class, 'enviar_pago_con_izipay']);

//=============================== PROCESOS DE PAGO PAYPAL===============================
Route::post('/web_checkout/generar-formulario-paypal',      [CheckoutController::class, 'generarFormularioPaypal']);
Route::post('/web_checkout/enviar_pago_con_paypal',         [CheckoutController::class, 'enviar_pago_con_paypal']);

Route::match(['get', 'post'], '/web_checkout/ubigeo/provincias', [CheckoutController::class, 'getProvinciasByDepartamento'])->name('ubigeo.provincias');
Route::match(['get', 'post'], '/web_checkout/ubigeo/distritos',  [CheckoutController::class, 'getDistritosByProvincia'])->name('ubigeo.distritos');
Route::post('/web_checkout/generar-formulario-stripe', [CheckoutController::class, 'generarFormularioStripe']);
Route::post('/web_checkout/stripe/confirmar-pago',     [CheckoutController::class, 'enviar_pago_con_stripe']);

Route::middleware(['auth'])->group(function () {
    //===== OPERACIONES(OPE) - CLIENTE ======
    Route::get('/mi-cuenta', function () {
        return redirect('/ope_pedidos/listar_pedidos');
    })->name('mi.cuenta');
    Route::get('/ope_pedidos/listar_pedidos',                           [PedidosController::class, 'listar_pedidos']);
    Route::post('/ope_pedidos/listar_pedidos_paginar',                  [PedidosController::class, 'listar_pedidos_paginar']);
    Route::post('/ope_pedidos/listar_pedidos_obtener',                  [PedidosController::class, 'listar_pedidos_obtener']);
    Route::post('/ope_pedidos/{order}/subir_comprobante_pedido',        [PedidosController::class, 'subir_comprobante_pedido'])->name('ope_pedidos.subir_comprobante_pedido');
    Route::post('/ope_pedidos/perfil_actualizar',                      [PedidosController::class, 'perfil_actualizar']);
    Route::get('/ope_pedidos/perfil_obtener',                          [PedidosController::class, 'perfil_obtener']);

    Route::get('/ope_pedidos/direcciones_listar',                       [PedidosController::class, 'direcciones_listar']);
    Route::post('/ope_pedidos/direcciones_crear',                       [PedidosController::class, 'direcciones_crear']);
    Route::put('/ope_pedidos/direcciones_actualizar/{id}',              [PedidosController::class, 'direcciones_actualizar']);
    Route::delete('/ope_pedidos/direcciones_eliminar/{id}',             [PedidosController::class, 'direcciones_eliminar']);
    Route::post('/ope_pedidos/direcciones_principal/{id}',              [PedidosController::class, 'direcciones_principal']);
    Route::post('/producto/calificar',                                  [App\Http\Controllers\Web\ProductRatingController::class, 'store']);
});


//===== WEB: TERMINOS Y CONDICIONES ======
Route::get('/web_politica_privacidad',                      [HomeController::class, 'web_politica_privacidad']);
Route::get('/web_terminos_condiciones',                     [HomeController::class, 'web_terminos_condiciones']);
Route::get('/web_servicio_cliente',                         [HomeController::class, 'web_servicio_cliente']);
Route::get('/web_delivery_informacion',                     [HomeController::class, 'web_delivery_informacion']);
Route::get('/web_garantia',                                 [HomeController::class, 'web_garantia']);
Route::get('/web_devoluciones',                             [HomeController::class, 'web_devoluciones']);

//===== SUSCRIPTORES ======
Route::post('/subscribe',                                   [SuscriptoresController::class, 'crear_suscriptores'])->name('subscribe');
Route::get('/unsuscribe',                                   [SuscriptoresController::class, 'showUnsubscribeForm'])->name('unsubscribe.form');
Route::post('/unsuscribe',                                  [SuscriptoresController::class, 'unsubscribe'])->name('unsubscribe');



// robots.txt dinámico (public/robots.txt eliminado: Apache lo servía antes que Laravel
// con la línea Sitemap hardcodeada a localhost).
Route::get('/robots.txt', function () {
    $path = public_path('robots.txt');
    $content = is_file($path)
        ? (string) file_get_contents($path)
        : "User-agent: *\nSitemap: https://rossety.pe/sitemap.xml\n";

    return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
});


// ===================PARA EL SEO ==================
// XML armado a mano: Spatie v7 no imprime changefreq/priority de forma fiable.
Route::get('/sitemap.xml', function () {
    $base = rtrim((string) config('app.url'), '/');
    $today = now()->toDateString();
    $entries = [];

    $push = function (string $path, string $changefreq, string $priority, ?string $lastmod = null) use (&$entries, $base, $today) {
        $loc = $path === '/' ? $base.'/' : $base.$path;
        if (isset($entries[$loc])) {
            return;
        }
        $entries[$loc] = [
            'loc' => $loc,
            'lastmod' => $lastmod ?: $today,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    };

    $dateOf = function ($model): string {
        $date = $model->updated_at ?? null;
        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        return now()->toDateString();
    };

    foreach ([
        ['path' => '/', 'changefreq' => 'weekly', 'priority' => '1.0'],
        ['path' => '/tienda', 'changefreq' => 'weekly', 'priority' => '0.9'],
        ['path' => '/nosotros', 'changefreq' => 'monthly', 'priority' => '0.8'],
        ['path' => '/contacto', 'changefreq' => 'monthly', 'priority' => '0.7'],
        ['path' => '/guia-de-tallas', 'changefreq' => 'monthly', 'priority' => '0.6'],
        ['path' => '/terminos-condiciones', 'changefreq' => 'yearly', 'priority' => '0.3'],
        ['path' => '/politica-privacidad', 'changefreq' => 'yearly', 'priority' => '0.3'],
        ['path' => '/libro-reclamaciones', 'changefreq' => 'monthly', 'priority' => '0.4'],
    ] as $page) {
        $push($page['path'], $page['changefreq'], $page['priority'], $today);
    }

    $categories = ProductoCategoria::query()->where('Activo', 'S')->get();
    foreach ($categories as $category) {
        $slug = CategoriaTiendaUrl::categorySlug($category);
        if ($slug === '') {
            continue;
        }
        $push('/tienda/'.$slug, 'weekly', '0.7', $dateOf($category));
    }

    $categoriesById = $categories->keyBy('id_producto_categoria');
    $subs = ProductoCategoriaSub::query()->where('Activo', 'S')->get();
    foreach ($subs as $sub) {
        $parent = $categoriesById->get($sub->id_producto_categoria);
        if (! $parent) {
            continue;
        }
        $catSlug = CategoriaTiendaUrl::categorySlug($parent);
        $subSlug = CategoriaTiendaUrl::subcategorySlug($sub);
        if ($catSlug === '' || $subSlug === '') {
            continue;
        }
        $push('/tienda/'.$catSlug.'/'.$subSlug, 'monthly', '0.6', $dateOf($sub));
    }

    $promos = WebPromoBloque::query()
        ->where('Activo', 'S')
        ->whereNotNull('slug')
        ->where('slug', '<>', '')
        ->get(['slug']);
    foreach ($promos as $promo) {
        $slug = trim((string) $promo->slug);
        if ($slug === '') {
            continue;
        }
        $push('/tienda/ofertas/'.$slug, 'weekly', '0.7', $today);
    }

    $products = Producto::query()
        ->where('Activo', 'S')
        ->whereNotNull('slug')
        ->where('slug', '<>', '')
        ->get(['slug', 'updated_at']);
    foreach ($products as $product) {
        $slug = trim((string) $product->slug);
        if ($slug === '' || ctype_digit($slug)) {
            continue;
        }
        $push('/web_shopDetail/'.$slug, 'weekly', '0.8', $dateOf($product));
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    foreach ($entries as $entry) {
        $loc = htmlspecialchars($entry['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$loc}</loc>\n";
        $xml .= "    <lastmod>{$entry['lastmod']}</lastmod>\n";
        $xml .= "    <changefreq>{$entry['changefreq']}</changefreq>\n";
        $xml .= "    <priority>{$entry['priority']}</priority>\n";
        $xml .= "  </url>\n";
    }
    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
});

Route::get('/comprobantes/{filename}',                              [FileController::class, 'show'])->name('comprobante.show');


//Redirect urls
Route::get('/about', [AboutController::class, 'web_about'])->name('about');
Route::get('/web_about', function () {
    return redirect('/about', 301);
});


/*
|--------------------------------------------------------------------------
| SEO Friendly Contact URL (alias → /contacto)
|--------------------------------------------------------------------------
*/
Route::redirect('/contact', '/contacto', 301)->name('contact');

/*
|--------------------------------------------------------------------------
| Old URL Redirect (301)
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| SEO Friendly Servicio Cliente URL
|--------------------------------------------------------------------------
*/
Route::get('/servicio-cliente', [HomeController::class, 'web_servicio_cliente'])
    ->name('servicio.cliente');
/*
|--------------------------------------------------------------------------
| Old URL Redirect (301)
|--------------------------------------------------------------------------
*/
Route::get('/web_servicio_cliente', function () {
    return redirect('/servicio-cliente', 301);
});
/*
|--------------------------------------------------------------------------
| Old URL Redirect (301) — Términos / Política (Valento en líneas ~160-161)
|--------------------------------------------------------------------------
*/
Route::get('/web_terminos_condiciones', function () {
    return redirect('/terminos-condiciones', 301);
});
Route::get('/web_politica_privacidad', function () {
    return redirect('/politica-privacidad', 301);
});

/*
|--------------------------------------------------------------------------
| SEO Friendly Delivery Información URL
|--------------------------------------------------------------------------
*/
Route::get('/delivery-informacion', [HomeController::class, 'web_delivery_informacion'])
    ->name('delivery.informacion');
/*
|--------------------------------------------------------------------------
| Old URL Redirect (301)
|--------------------------------------------------------------------------
*/
Route::get('/web_delivery_informacion', function () {
    return redirect('/delivery-informacion', 301);
});

/*
|--------------------------------------------------------------------------
| SEO Friendly Ubicación URL
|--------------------------------------------------------------------------
*/
Route::get('/ubicacion', [HomeController::class, 'web_ubicacion'])
    ->name('ubicacion');
/*
|--------------------------------------------------------------------------
| Old URL Redirect (301)
|--------------------------------------------------------------------------
*/
Route::get('/web_ubicacion', function () {
    return redirect('/ubicacion', 301);
});

/*
|--------------------------------------------------------------------------
| SEO Friendly Shop Detail Lista URL
|--------------------------------------------------------------------------
*/
Route::get('/shop-detail-lista', fn () => redirect('/carrito'))
    ->name('shop.detail.lista');

/*
|--------------------------------------------------------------------------
| SEO Friendly Devoluciones URL
|--------------------------------------------------------------------------
*/
Route::get('/devoluciones', [HomeController::class, 'web_devoluciones'])
    ->name('devoluciones');


/*
|--------------------------------------------------------------------------
| Old URL Redirect (301)
|--------------------------------------------------------------------------
*/
Route::get('/web_devoluciones', function () {
    return redirect('/devoluciones', 301);
});


/*
|--------------------------------------------------------------------------
| SEO Friendly Libro de Reclamos URL
|--------------------------------------------------------------------------
*/
Route::get('/libro-reclamos', [LibroReclamosController::class, 'web_libro_reclamos'])
    ->name('libro.reclamos');

Route::match(['get','post'], '/libro-reclamos/estado', [LibroReclamosController::class, 'web_libro_reclamos_estado'])
    ->name('libro.reclamos.estado');

/*
|--------------------------------------------------------------------------
| Old URL Redirect (301)
|--------------------------------------------------------------------------
*/
Route::get('/web_libro_reclamos', function () {
    return redirect('/libro-reclamos', 301);
});


/*
|--------------------------------------------------------------------------
| SEO Friendly Garantía URL
|--------------------------------------------------------------------------
*/
Route::get('/garantia', [HomeController::class, 'web_garantia'])
    ->name('garantia');

// Diagnóstico temporal de zona horaria — eliminar tras verificar en producción
Route::get('/test-time', function () {
    \App\Support\MysqlTimezone::applySession();

    $mysql = DB::selectOne('SELECT @@session.time_zone AS session_tz, NOW() AS ahora');

    return response()->json([
        'config_timezone' => config('app.timezone'),
        'db_timezone_config' => config('database.connections.mysql.timezone'),
        'php_timezone' => date_default_timezone_get(),
        'laravel_now' => now()->toDateTimeString(),
        'mysql_session_tz' => $mysql->session_tz ?? null,
        'mysql_now' => $mysql->ahora ?? null,
        'ok' => now()->format('Y-m-d') === \Carbon\Carbon::parse($mysql->ahora ?? '')->format('Y-m-d'),
    ]);
});


/*
|--------------------------------------------------------------------------
| Old URL Redirect (301)
|--------------------------------------------------------------------------
*/
Route::get('/web_garantia', function () {
    return redirect('/garantia', 301);
});
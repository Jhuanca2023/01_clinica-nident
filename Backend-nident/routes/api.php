<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WebPopupPromoController;
use App\Http\Controllers\Api\WebPasoPedidoController;
use App\Http\Controllers\Api\WebCuentaPagoController;
use App\Http\Controllers\Api\WebHeroRossetyController;
use App\Http\Controllers\Api\BannerPopularController;
use App\Http\Controllers\Api\CalculoEnvioController;
use App\Http\Controllers\Api\ClientesController;
use App\Http\Controllers\Api\EmpleadosController;
use App\Http\Controllers\Api\PerfilesController;
use App\Http\Controllers\Api\RolesController;
use App\Http\Controllers\Api\TipoBombaController;
use App\Http\Controllers\Api\UnidadMedidaController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VendedorController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\ProgramacionController;
use App\Http\Controllers\Api\CarguioController;
use App\Http\Controllers\Api\CategoriaPopularController;
use App\Http\Controllers\Api\CostoEnvioController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\ProductoImportController;
use App\Http\Controllers\Api\ProductoCategoriaController;
use App\Http\Controllers\Api\ProductoCategoriaSubController;
use App\Http\Controllers\Api\PedidosController;
use App\Http\Controllers\Api\BalanceVentasController;

use App\Http\Controllers\Api\WebFooterController;
use App\Http\Controllers\Api\WebSliderController;
use App\Http\Controllers\Api\WebPlanesController;
use App\Http\Controllers\Api\WebNuestroEquipoController;
use App\Http\Controllers\Api\WebHeaderController;
use App\Http\Controllers\Api\WebMasVendidoController;
use App\Http\Controllers\Api\WebAboutController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\OfertaDelDiaController;
use App\Http\Controllers\Api\PrecioPesoController;
use App\Http\Controllers\Api\PromocionController;
use App\Http\Controllers\Api\UbigeoController;
use App\Http\Controllers\Api\MetadatosPaginaController;
use App\Http\Controllers\Api\AppFaviconController;
use App\Http\Controllers\Api\SistemaMenuController;
use App\Http\Controllers\Api\SistemaMenuTreeviewController;
use App\Http\Controllers\Api\SistemaObjetosController;
use App\Http\Controllers\Api\WebReclamosController;
use App\Http\Controllers\Api\PagoPasarelaController;
use App\Http\Controllers\Api\SeguridadAuthProveedorController;
use App\Http\Controllers\Api\SeguridadAuthOAuthController;
use App\Http\Controllers\Api\SeguridadAuth2faController;
use App\Http\Controllers\Api\SeguridadUsuarioAuthConfigController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\BackupController;
use App\Http\Controllers\Api\BeneficioController;
use App\Http\Controllers\Api\WebPreguntasFrecuentesController;
use App\Http\Controllers\Api\WebTestimoniosController;
use App\Http\Controllers\Api\WebCarruselController;
use App\Http\Controllers\Api\WebContadoresController;
use App\Http\Controllers\Api\WebPaginaNosotrosController;
use App\Http\Controllers\Api\WebPaginaProductosController;
use App\Http\Controllers\Api\WebPaginaMaquinariasController;
use App\Http\Controllers\Api\WebPaginaContactoController;
use App\Http\Controllers\Api\WebAboutCaracteristicaController;
use App\Http\Controllers\Api\WebConfianzaItemController;
use App\Http\Controllers\Api\WebContadoresSeccionController;
use App\Http\Controllers\Api\WebProductoDestacadoController;
use App\Http\Controllers\Api\WebHomeColeccionController;
use App\Http\Controllers\Api\WebProductoCatalogoController;
use App\Http\Controllers\Api\WebLineaProductoController;
use App\Http\Controllers\Api\WebVideoController;
use App\Http\Controllers\Api\WebVideosController;
use App\Http\Controllers\Api\WebClienteController;
use App\Http\Controllers\Api\WebPromoBloqueController;
use App\Http\Controllers\Api\WebPromoMultiplicadorController;
use App\Http\Controllers\Api\WebPaginaGaleriaController;
use App\Http\Controllers\Api\WebContactoMensajeController;
use App\Http\Controllers\Api\WebWhatsappConfigController;
use App\Http\Controllers\Api\WebCarritoConfigController;
use App\Http\Controllers\Api\AppFinanza\SyncController;
use App\Http\Controllers\Api\DeliveryConfigController;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/ 

Route::get('/', function () {
    return response()->json([
        'message' => 'Bienvenido a la API de Valento',
        'documentation' => url('/api/documentation'),
        'status' => 'Operativo'
    ]);
});

// ── Web pública (sin autenticación) ─────────────────────────────────────────
Route::prefix('public/web')->group(function () {
    Route::get('/footer',        [WebFooterController::class, 'obtener']);
    Route::get('/footer_banner', [\App\Http\Controllers\Api\WebFooterBannerController::class, 'obtener']);
});

// Ruta corregida para usar JWT
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return auth()->user(); // Cambiado a auth() en lugar de $request->user()
});

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/auth/logout',  [AuthController::class, 'logout']);
    Route::post('/auth/olvido-contrasena', [AuthController::class, 'olvido_contrasena']);
    Route::post('/auth/restablecer-contrasena', [AuthController::class, 'restablecer_contrasena']);

    Route::get('/auth/metodos-login', [SeguridadAuthProveedorController::class, 'metodosLogin']);
    Route::get('/auth/oauth/redirect/{proveedor}', [SeguridadAuthOAuthController::class, 'redirect'])
        ->where('proveedor', 'microsoft|google')
        ->middleware('throttle:20,1');
    Route::get('/auth/oauth/callback/{proveedor}', [SeguridadAuthOAuthController::class, 'callback'])
        ->where('proveedor', 'microsoft|google')
        ->middleware('throttle:30,1');
    Route::post('/auth/oauth/exchange', [SeguridadAuthOAuthController::class, 'exchange'])
        ->middleware('throttle:15,1');

    Route::post('/auth/2fa/verify', [SeguridadAuth2faController::class, 'verify'])
        ->middleware('throttle:10,1');
    Route::post('/auth/2fa/mandatory/init', [SeguridadAuth2faController::class, 'mandatoryInit'])
        ->middleware('throttle:10,1');
    Route::post('/auth/2fa/mandatory/confirm', [SeguridadAuth2faController::class, 'mandatoryConfirm'])
        ->middleware('throttle:10,1');

    Route::post('/auth/metodos-por-email', [SeguridadUsuarioAuthConfigController::class, 'metodosPorEmail'])
        ->middleware('throttle:20,1');

    // Ruta pública para servir archivos de reclamos (sin auth, necesario para previsualización en iframe)
    Route::get('/storage_reclamo/{filename}', [FileController::class, 'show_reclamo']);

    //Stripe no envía token de autenticación, solo firma el payload con el Webhook Secret
    Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');

Route::get('/config/favicon', [AppFaviconController::class, 'obtener']);

// Popup público (sin auth) — consumido por la web pública vía JS
Route::get('/web-popup/publico', [WebPopupPromoController::class, 'obtener']);
Route::post('/web-popup/aplicar-descuento', [WebPopupPromoController::class, 'aplicarDescuento']);

Route::group(['middleware' => ['auth:api']], function () {
    //Cuando se carga la pagina, valida de que siga conectado o entodo caso se redirecciona al login:Profile
    //await axios.post(`${API_URL}/api/usuario/obtener`, { id: userId }); - React
    Route::post('/perfil/validar_conexion',       [AuthController::class, 'validar_conexion']);
    Route::post('/perfil/validar_perfil',         [AuthController::class, 'validar_perfil']);

    Route::get('/auth/2fa/status', [SeguridadAuth2faController::class, 'status']);
    Route::post('/auth/2fa/setup', [SeguridadAuth2faController::class, 'setup'])->middleware('throttle:5,1');
    Route::post('/auth/2fa/confirm', [SeguridadAuth2faController::class, 'confirm'])->middleware('throttle:10,1');
    Route::post('/auth/2fa/disable', [SeguridadAuth2faController::class, 'disable'])->middleware('throttle:5,1');
    Route::post('/auth/2fa/recovery/regenerate', [SeguridadAuth2faController::class, 'regenerateRecovery'])
        ->middleware('throttle:3,1');

    //Clientes
    Route::get('/clientes/obtener',               [ClientesController::class, 'obtener']);
    Route::get('/clientes/listar',                [ClientesController::class, 'listar']);
    Route::post('/clientes/crear',                [ClientesController::class, 'crear']);
    Route::put('/clientes/actualizar',            [ClientesController::class, 'actualizar']);
    Route::delete('/clientes/eliminar',           [ClientesController::class, 'eliminar']);

    //Empleados
    Route::get('/empleados/obtener',              [EmpleadosController::class, 'obtener']);
    Route::get('/empleados/listar',               [EmpleadosController::class, 'listar']);
    Route::post('/empleados/crear',               [EmpleadosController::class, 'crear']);
    Route::put('/empleados/actualizar',           [EmpleadosController::class, 'actualizar']);
    Route::delete('/empleados/eliminar',          [EmpleadosController::class, 'eliminar']);

    //Perfiles
    Route::get('/perfiles/obtener',               [PerfilesController::class, 'obtener']);
    Route::get('/perfiles/listar',                [PerfilesController::class, 'listar']);
    Route::post('/perfiles/crear',                [PerfilesController::class, 'crear']);
    Route::put('/perfiles/actualizar',            [PerfilesController::class, 'actualizar']);
    Route::delete('/perfiles/eliminar',           [PerfilesController::class, 'eliminar']);

    Route::post('/perfiles/obtener_asignar',      [PerfilesController::class, 'obtener_asignar']);
    Route::get('/perfiles/obtener_lista',         [PerfilesController::class, 'obtener_lista']);
    Route::get('/perfiles/obtener_check',         [PerfilesController::class, 'obtener_check']);
    Route::delete('/perfiles/obtener_eliminar',   [PerfilesController::class, 'obtener_eliminar']);
    Route::delete('/perfiles/eliminar_multiple',  [PerfilesController::class, 'eliminar_multiple']);

    //Roles-1
    Route::get('/roles/obtener',                  [RolesController::class, 'obtener']);
    Route::get('/roles/listar',                   [RolesController::class, 'listar']);
    Route::post('/roles/crear',                   [RolesController::class, 'crear']);
    Route::put('/roles/actualizar',               [RolesController::class, 'actualizar']);
    Route::delete('/roles/eliminar',              [RolesController::class, 'eliminar']);
    Route::get('/roles/listar_treeview',          [SistemaMenuTreeviewController::class, 'listar']);
    Route::post('/roles/agregar_permisos',        [SistemaMenuTreeviewController::class, 'guardar']);
    Route::post('/roles/copiar_permisos',         [SistemaMenuTreeviewController::class, 'copiar_permisos']);
    
    Route::get('/menu_treeview/listar',           [SistemaMenuTreeviewController::class, 'listar']);
    Route::post('/menu_treeview/guardar',         [SistemaMenuTreeviewController::class, 'guardar']);
    
    Route::post('/roles/obtener_asignar',         [RolesController::class, 'obtener_asignar']);
    Route::get('/roles/obtener_lista',            [RolesController::class, 'obtener_lista']);
    Route::get('/roles/obtener_check',            [RolesController::class, 'obtener_check']);
    Route::delete('/roles/obtener_eliminar',      [RolesController::class, 'obtener_eliminar']);
    Route::delete('/roles/eliminar_multiple',     [RolesController::class, 'eliminar_multiple']);

    // Menús y objetos (gestión)
    Route::get('/menu/listar_rutas_sistema',      [SistemaMenuController::class, 'listarRutasSistema']);
    Route::get('/menu/resolver_modulo_ruta',       [SistemaMenuController::class, 'resolver_modulo_ruta']);
    Route::post('/menu/desactivar_modulo_rol',     [SistemaMenuController::class, 'desactivar_modulo_rol']);
    Route::get('/menu/favoritos/listar',           [SistemaMenuController::class, 'listar_favoritos']);
    Route::post('/menu/favoritos/toggle',          [SistemaMenuController::class, 'toggle_favorito']);
    Route::post('/menu/favoritos/registrar_visita', [SistemaMenuController::class, 'registrar_visita_favorito']);
    Route::get('/menu/historial_sessiones/listar', [SistemaMenuController::class, 'listar_historial_sessiones']);
    Route::post('/menu/historial_sessiones/registrar', [SistemaMenuController::class, 'registrar_historial_sessiones']);
    Route::post('/menu/historial_sessiones/eliminar', [SistemaMenuController::class, 'eliminar_historial_sessiones']);
    Route::get('/menu/listar',                    [SistemaMenuController::class, 'listar']);
    Route::get('/menu/listar_modulos',            [SistemaMenuController::class, 'listar_modulos']);
    Route::post('/menu/crear_modulo',             [SistemaMenuController::class, 'crear_modulo']);
    Route::post('/menu/convertir_sin_modulo',     [SistemaMenuController::class, 'convertir_sin_modulo']);
    Route::put('/menu/actualizar_modulo',         [SistemaMenuController::class, 'actualizar_modulo']);
    Route::delete('/menu/eliminar_modulo',        [SistemaMenuController::class, 'eliminar_modulo']);
    Route::post('/menu/reordenar_modulos',       [SistemaMenuController::class, 'reordenar_modulos']);
    Route::get('/menu/listar_orden_sidebar',      [SistemaMenuController::class, 'listar_orden_sidebar']);
    Route::post('/menu/reordenar_sidebar',        [SistemaMenuController::class, 'reordenar_sidebar']);
    Route::get('/menu/mi_orden_sidebar',              [SistemaMenuController::class, 'listar_mi_orden_sidebar']);
    Route::post('/menu/guardar_mi_orden_sidebar',    [SistemaMenuController::class, 'guardar_mi_orden_sidebar']);
    Route::post('/menu/guardar_mi_etiqueta_sidebar', [SistemaMenuController::class, 'guardar_mi_etiqueta_sidebar']);
    Route::post('/menu/guardar_etiqueta_sidebar',    [SistemaMenuController::class, 'guardar_etiqueta_sidebar']);
    Route::get('/menu/listar_sidebar',            [SistemaMenuController::class, 'listar_sidebar']);
    Route::post('/menu/crear',                    [SistemaMenuController::class, 'crear']);
    Route::put('/menu/actualizar',                 [SistemaMenuController::class, 'actualizar']);
    Route::post('/menu/reordenar',                 [SistemaMenuController::class, 'reordenar']);
    Route::delete('/menu/eliminar',               [SistemaMenuController::class, 'eliminar']);
    Route::get('/menu/listar_objetos_menu',      [SistemaMenuController::class, 'listar_objetos_menu']);
    Route::post('/menu/asignar_objeto',           [SistemaMenuController::class, 'asignar_objeto']);
    Route::delete('/menu/quitar_objeto',         [SistemaMenuController::class, 'quitar_objeto']);
    Route::get('/menu/listar_objetos_modulo',    [SistemaMenuController::class, 'listar_objetos_modulo']);
    Route::post('/menu/asignar_objeto_modulo',   [SistemaMenuController::class, 'asignar_objeto_modulo']);
    Route::delete('/menu/quitar_objeto_modulo',  [SistemaMenuController::class, 'quitar_objeto_modulo']);

    Route::get('/administracion_etiquetas/listar',          [\App\Http\Controllers\Api\AdministracionEtiquetasController::class, 'listar']);
    Route::get('/administracion_etiquetas/listar_opciones', [\App\Http\Controllers\Api\AdministracionEtiquetasController::class, 'listar_opciones']);
    Route::post('/administracion_etiquetas/crear',          [\App\Http\Controllers\Api\AdministracionEtiquetasController::class, 'crear']);
    Route::put('/administracion_etiquetas/actualizar',      [\App\Http\Controllers\Api\AdministracionEtiquetasController::class, 'actualizar']);
    Route::delete('/administracion_etiquetas/eliminar',     [\App\Http\Controllers\Api\AdministracionEtiquetasController::class, 'eliminar']);

    Route::get('/objetos/listar',                 [SistemaObjetosController::class, 'listar']);
    Route::post('/objetos/crear',                 [SistemaObjetosController::class, 'crear']);
    Route::put('/objetos/actualizar',            [SistemaObjetosController::class, 'actualizar']);
    Route::delete('/objetos/eliminar',            [SistemaObjetosController::class, 'eliminar']);

    //Usuarios
    Route::post('/usuario/obtener',               [UserController::class, 'obtener']);
    Route::get('/usuario/listar',                 [UserController::class, 'listar']);
    Route::post('/usuario/crear',                 [UserController::class, 'crear']);
    Route::post('/usuario/actualizar',            [UserController::class, 'actualizar']);
    Route::delete('/usuario/eliminar',            [UserController::class, 'eliminar']);
    Route::get('/usuario/admin-principal',        [UserController::class, 'adminPrincipal']);
    Route::post('/usuario/transferir-admin-principal', [UserController::class, 'transferirAdminPrincipal']);
    Route::post('/usuario/auth-config/obtener', [SeguridadUsuarioAuthConfigController::class, 'obtener']);
    Route::post('/usuario/auth-config/guardar', [SeguridadUsuarioAuthConfigController::class, 'guardar']);
    Route::post('/usuario/auth-config/resetear-2fa', [SeguridadUsuarioAuthConfigController::class, 'resetear2fa']);
    Route::get('/usuario/listar_repartidores', [UserController::class, 'listar_repartidores']);

    //Vendedor
    Route::get('/vendedor/obtener',               [VendedorController::class, 'obtener']);
    Route::get('/vendedor/listar',                [VendedorController::class, 'listar']);
    Route::post('/vendedor/crear',                [VendedorController::class, 'crear']);
    Route::put('/vendedor/actualizar',            [VendedorController::class, 'actualizar']);
    Route::delete('/vendedor/eliminar',           [VendedorController::class, 'eliminar']);

    //Backup
    Route::get('/backup/listar',           [BackupController::class, 'listar']);
    Route::post('/backup/generar',         [BackupController::class, 'generar']);
    Route::get('/backup/descargar',        [BackupController::class, 'descargar']);
    Route::delete('/backup/eliminar',      [BackupController::class, 'eliminar']);


    //Programacion
    Route::get('/programacion/obtener',                             [ProgramacionController::class, 'obtener']);
    Route::get('/programacion/listar',                              [ProgramacionController::class, 'listar']);
    Route::post('/programacion/crear',                              [ProgramacionController::class, 'crear']);
    Route::put('/programacion/actualizar',                          [ProgramacionController::class, 'actualizar']);
    Route::delete('/programacion/eliminar',                         [ProgramacionController::class, 'eliminar']);
    Route::post('/programacion/descargar_reporte',                  [ProgramacionController::class, 'descargar_reporte']);
    Route::get('/programacion/listar_filtrar',                      [ProgramacionController::class, 'listar_filtrar']);
    Route::get('/programacion/descargar_reporte_programacion',      [ProgramacionController::class, 'descargar_reporte_programacion']);

     
     //Productos
    Route::get('/producto/obtener',                         [ProductoController::class, 'obtener']);
    Route::get('/producto/listar',                          [ProductoController::class, 'listar']);
    Route::get('/producto/listar_filtro',                   [ProductoController::class, 'listar_filtro']);
    Route::get('/producto/verificar_codigo',                [ProductoController::class, 'verificarCodigo']);

    Route::post('/producto/crear',                          [ProductoController::class, 'crear']);
    Route::post('/producto/actualizar',                     [ProductoController::class, 'actualizar']);
    Route::post('/producto/actualizar_subproducto',         [ProductoController::class, 'actualizar_subproducto']);
    Route::post('/producto/actualizar_imagen_principal',    [ProductoController::class, 'actualizar_imagen_principal']);

    Route::delete('/producto/eliminar',                     [ProductoController::class, 'eliminar']);
    Route::get('/producto/listar_imagen',                   [ProductoController::class, 'listar_imagen']);
    Route::post('/producto/crear_imagen',                   [ProductoController::class, 'crear_imagen']);
    Route::delete('/producto/eliminar_imagen',              [ProductoController::class, 'eliminar_imagen']);
    Route::put('/producto/actualizar_imagen_orden',         [ProductoController::class, 'actualizar_imagen_orden']);
    Route::post('/producto/actualizar_ficha_tecnica',       [ProductoController::class, 'actualizar_ficha_tecnica']);
    Route::get('/producto/obtener_ficha_tecnica',           [ProductoController::class, 'obtener_ficha_tecnica']);
    Route::get('/producto/obtener_imagen',                  [ProductoController::class, 'obtener_imagen']);
    
    Route::get('/producto/listar_fotos',                    [ProductoController::class, 'listar_fotos']);
    Route::post('/producto/crear_fotos',                    [ProductoController::class, 'crear_fotos']);
    Route::delete('/producto/eliminar_fotos',               [ProductoController::class, 'eliminar_fotos']);
    Route::put('/producto/actualizar_foto_orden',           [ProductoController::class, 'actualizar_foto_orden']);
    Route::get('/producto/obtener_dimension_producto',      [ProductoController::class, 'obtener_dimension_producto']);
    Route::get('/producto/listar_por_fechas',                 [ProductoController::class, 'listar_por_fechas']);
    Route::get('/producto/exportar',                           [ProductoController::class, 'exportar']);
    Route::post('/producto/importar',                         [ProductoImportController::class, 'importar']);
    Route::post('/producto/importar-imagenes',               [ProductoImportController::class, 'importarImagenes']);

    // App tablet POS MAUI — catálogo (JWT middleware auth:api del grupo)
    Route::get('/app/finanza/sync/catalog', [SyncController::class, 'catalog']);

    //Producto Categoria
    Route::get('/producto_categoria/obtener',               [ProductoCategoriaController::class, 'obtener']);
    Route::get('/producto_categoria/listar',                [ProductoCategoriaController::class, 'listar']);
    Route::post('/producto_categoria/crear',                [ProductoCategoriaController::class, 'crear']);
    Route::put('/producto_categoria/actualizar',            [ProductoCategoriaController::class, 'actualizar']);
    Route::delete('/producto_categoria/eliminar',           [ProductoCategoriaController::class, 'eliminar']);
    Route::get('/producto_categoria/listar_tipo',           [ProductoCategoriaController::class, 'listar_tipo']);
    Route::post('/producto_categoria/subir_imagen',         [ProductoCategoriaController::class, 'subirImagen']);

    //Producto Categoria Sub
    Route::get('/producto_categoria_sub/obtener',               [ProductoCategoriaSubController::class, 'obtener']);
    Route::get('/producto_categoria_sub/listar',                [ProductoCategoriaSubController::class, 'listar']);
    Route::post('/producto_categoria_sub/crear',                [ProductoCategoriaSubController::class, 'crear']);
    Route::put('/producto_categoria_sub/actualizar',            [ProductoCategoriaSubController::class, 'actualizar']);
    Route::delete('/producto_categoria_sub/eliminar',           [ProductoCategoriaSubController::class, 'eliminar']);
    Route::get('/producto_categoria_sub/listar_tipo',           [ProductoCategoriaSubController::class, 'listar_tipo']);
    Route::get('/producto_categoria_sub/listar_obtener',        [ProductoCategoriaSubController::class, 'listar_obtener']);

    //Web Header
    Route::get('/web_header/obtener',    [WebHeaderController::class, 'obtener']);
    Route::post('/web_header/actualizar',[WebHeaderController::class, 'actualizar']);
    Route::post('/web_header/sincronizar_nav', [WebHeaderController::class, 'sincronizar_nav']);

    //Web Nuestro Equipo
    Route::get('/web_nuestro_equipo/listar',            [WebNuestroEquipoController::class, 'listar']);
    Route::post('/web_nuestro_equipo/crear',             [WebNuestroEquipoController::class, 'crear']);
    Route::post('/web_nuestro_equipo/actualizar',        [WebNuestroEquipoController::class, 'actualizar']);
    Route::post('/web_nuestro_equipo/actualizar_seccion',[WebNuestroEquipoController::class, 'actualizar_seccion']);
    Route::delete('/web_nuestro_equipo/eliminar',        [WebNuestroEquipoController::class, 'eliminar']);

    //Web Planes
    Route::get('/web_planes/listar',                               [WebPlanesController::class, 'listar']);
    Route::get('/web_planes/obtener',                              [WebPlanesController::class, 'obtener']);
    Route::post('/web_planes/crear',                               [WebPlanesController::class, 'crear']);
    Route::post('/web_planes/actualizar',                          [WebPlanesController::class, 'actualizar']);
    Route::delete('/web_planes/eliminar',                          [WebPlanesController::class, 'eliminar']);

    //Web Footer
    Route::get('/web_footer/obtener',                             [WebFooterController::class, 'obtener']);
    Route::get('/web_footer/listar',                              [WebFooterController::class, 'listar']);
    Route::post('/web_footer/crear',                              [WebFooterController::class, 'crear']);
    Route::post('/web_footer/actualizar',                         [WebFooterController::class, 'actualizar']);
    Route::post('/web_footer/actualizar_logos',                   [WebFooterController::class, 'actualizar_logos']);
    Route::delete('/web_footer/eliminar',                         [WebFooterController::class, 'eliminar']);
    Route::get('/web_footer/obtener_document',                    [WebFooterController::class, 'obtener_document']);
    Route::post('/web_footer/actualizar_document',                [WebFooterController::class, 'actualizar_document']);
       
    //Web About
    Route::get('/web_slider/obtener',                             [WebSliderController::class, 'obtener']);
    Route::get('/web_slider/listar',                              [WebSliderController::class, 'listar']);
    Route::get('/web_slider/configuracion',                       [WebSliderController::class, 'configuracion']);
    Route::post('/web_slider/actualizar_configuracion',           [WebSliderController::class, 'actualizarConfiguracion']);
    Route::post('/web_slider/actualizar',                         [WebSliderController::class, 'actualizar']);
    Route::post('/web_slider/crear',                              [WebSliderController::class, 'crear']);
    Route::delete('/web_slider/eliminar',                          [WebSliderController::class, 'eliminar']);

    //Web About
    Route::get('/web_about/obtener',                             [WebAboutController::class, 'obtener']);
    Route::get('/web_about/listar',                              [WebAboutController::class, 'listar']);
    Route::post('/web_about/actualizar',                         [WebAboutController::class, 'actualizar']);
    
    //Web Preguntas Frecuentes (FAQ)
    Route::get('/web_faq/listar',                                 [WebPreguntasFrecuentesController::class, 'listar']);
    Route::post('/web_faq/crear',                                 [WebPreguntasFrecuentesController::class, 'crear']);
    Route::post('/web_faq/actualizar',                            [WebPreguntasFrecuentesController::class, 'actualizar']);
    Route::post('/web_faq/eliminar',                              [WebPreguntasFrecuentesController::class, 'eliminar']);
    Route::post('/web_faq/actualizar_seccion',                    [WebPreguntasFrecuentesController::class, 'actualizarSeccion']);

    //Web Testimonios
    Route::get('/web_testimonios/listar',                         [WebTestimoniosController::class, 'listar']);
    Route::post('/web_testimonios/crear',                         [WebTestimoniosController::class, 'crear']);
    Route::post('/web_testimonios/actualizar',                    [WebTestimoniosController::class, 'actualizar']);
    Route::delete('/web_testimonios/eliminar',                    [WebTestimoniosController::class, 'eliminar']);
    Route::post('/web_testimonios/actualizar_seccion',            [WebTestimoniosController::class, 'actualizarSeccion']);
    Route::get('/web_testimonios/listar_rossety',                 [WebTestimoniosController::class, 'listarRossety']);
    Route::post('/web_testimonios/actualizar_seccion_rossety',    [WebTestimoniosController::class, 'actualizarSeccionRossety']);
    Route::post('/web_testimonios/ordenar_rossety',               [WebTestimoniosController::class, 'ordenarRossety']);

    Route::get('/web_carrusel/listar',                            [WebCarruselController::class, 'listar']);
    Route::post('/web_carrusel/crear',                            [WebCarruselController::class, 'crear']);
    Route::post('/web_carrusel/actualizar',                       [WebCarruselController::class, 'actualizar']);
    Route::delete('/web_carrusel/eliminar',                       [WebCarruselController::class, 'eliminar']);
    Route::post('/web_carrusel/reordenar',                        [WebCarruselController::class, 'reordenar']);

    // Promo Bloques (Ofertas & Promociones por bloques)
    Route::get('/web_promo_bloque/listar',                        [WebPromoBloqueController::class, 'listar']);
    Route::get('/web_promo_bloque/listar_arbol',                  [WebPromoBloqueController::class, 'listarArbol']);
    Route::get('/web_promo_bloque/asignacion_producto',           [WebPromoBloqueController::class, 'asignacionProducto']);
    Route::post('/web_promo_bloque/asignar_producto_item',        [WebPromoBloqueController::class, 'asignarProductoItem']);
    Route::post('/web_promo_bloque/quitar_producto_item',         [WebPromoBloqueController::class, 'quitarProductoItem']);
    Route::post('/web_promo_bloque/crear',                        [WebPromoBloqueController::class, 'crear']);
    Route::post('/web_promo_bloque/actualizar',                   [WebPromoBloqueController::class, 'actualizar']);
    Route::post('/web_promo_bloque/vincular_productos',           [WebPromoBloqueController::class, 'vincularProductos']);
    Route::delete('/web_promo_bloque/eliminar',                   [WebPromoBloqueController::class, 'eliminar']);
    Route::post('/web_promo_bloque/crear_item',                   [WebPromoBloqueController::class, 'crearItem']);
    Route::post('/web_promo_bloque/actualizar_item',              [WebPromoBloqueController::class, 'actualizarItem']);
    Route::delete('/web_promo_bloque/eliminar_item',              [WebPromoBloqueController::class, 'eliminarItem']);

    // Multiplicadores / etiquetas de precio (ofertas)
    Route::get('/web_promo_multiplicador/listar',                 [WebPromoMultiplicadorController::class, 'listar']);
    Route::post('/web_promo_multiplicador/crear',                 [WebPromoMultiplicadorController::class, 'crear']);
    Route::post('/web_promo_multiplicador/actualizar',            [WebPromoMultiplicadorController::class, 'actualizar']);
    Route::delete('/web_promo_multiplicador/eliminar',            [WebPromoMultiplicadorController::class, 'eliminar']);

    Route::get('/web_lookbook/obtener',                            [\App\Http\Controllers\Api\WebLookbookController::class, 'obtener']);
    Route::post('/web_lookbook/actualizar',                        [\App\Http\Controllers\Api\WebLookbookController::class, 'actualizar']);
    Route::get('/web_footer_banner/obtener',                       [\App\Http\Controllers\Api\WebFooterBannerController::class, 'obtener']);
    Route::post('/web_footer_banner/actualizar',                   [\App\Http\Controllers\Api\WebFooterBannerController::class, 'actualizar']);

    Route::get('/web_pagina_galeria/listar',                      [WebPaginaGaleriaController::class, 'listar']);
    Route::post('/web_pagina_galeria/actualizar_seccion',        [WebPaginaGaleriaController::class, 'actualizar_seccion']);
    Route::post('/web_pagina_galeria/crear',                      [WebPaginaGaleriaController::class, 'crear']);
    Route::post('/web_pagina_galeria/actualizar',                 [WebPaginaGaleriaController::class, 'actualizar']);
    Route::delete('/web_pagina_galeria/eliminar',                 [WebPaginaGaleriaController::class, 'eliminar']);

    // Web Contadores (banda estadísticas)
    Route::get('/web_contadores/listar',                          [WebContadoresController::class, 'listar']);
    Route::post('/web_contadores/crear',                          [WebContadoresController::class, 'crear']);
    Route::post('/web_contadores/actualizar',                     [WebContadoresController::class, 'actualizar']);
    Route::delete('/web_contadores/eliminar',                     [WebContadoresController::class, 'eliminar']);

    // Web About Características
    Route::get('/web_about_caracteristica/listar',                [WebAboutCaracteristicaController::class, 'listar']);
    Route::post('/web_about_caracteristica/crear',                [WebAboutCaracteristicaController::class, 'crear']);
    Route::post('/web_about_caracteristica/actualizar',           [WebAboutCaracteristicaController::class, 'actualizar']);
    Route::delete('/web_about_caracteristica/eliminar',           [WebAboutCaracteristicaController::class, 'eliminar']);

    // Web Confianza Items
    Route::get('/web_confianza_item/listar',                      [WebConfianzaItemController::class, 'listar']);
    Route::get('/web_confianza_item/seccion/obtener',             [WebConfianzaItemController::class, 'seccionObtener']);
    Route::post('/web_confianza_item/seccion/actualizar',         [WebConfianzaItemController::class, 'seccionActualizar']);
    Route::post('/web_confianza_item/crear',                      [WebConfianzaItemController::class, 'crear']);
    Route::post('/web_confianza_item/actualizar',                 [WebConfianzaItemController::class, 'actualizar']);
    Route::delete('/web_confianza_item/eliminar',                 [WebConfianzaItemController::class, 'eliminar']);

    // Web Contadores Sección
    Route::get('/web_contadores_seccion/obtener',                 [WebContadoresSeccionController::class, 'obtener']);
    Route::post('/web_contadores_seccion/actualizar',             [WebContadoresSeccionController::class, 'actualizar']);

    // Web Producto Destacado
    Route::get('/web_producto_destacado/listar',                  [WebProductoDestacadoController::class, 'listar']);
    Route::post('/web_producto_destacado/crear',                  [WebProductoDestacadoController::class, 'crear']);
    Route::post('/web_producto_destacado/actualizar',             [WebProductoDestacadoController::class, 'actualizar']);
    Route::post('/web_producto_destacado/actualizar_seccion',     [WebProductoDestacadoController::class, 'actualizar_seccion']);
    Route::delete('/web_producto_destacado/eliminar',             [WebProductoDestacadoController::class, 'eliminar']);

    Route::get('/web_home_coleccion/listar',                      [WebHomeColeccionController::class, 'listar']);
    Route::post('/web_home_coleccion/toggle',                     [WebHomeColeccionController::class, 'toggle']);
    Route::post('/web_home_coleccion/actualizar_seccion',         [WebHomeColeccionController::class, 'actualizar_seccion']);
    Route::post('/web_home_coleccion/reordenar',                  [WebHomeColeccionController::class, 'reordenar']);
    Route::post('/web_home_coleccion/sincronizar',                [WebHomeColeccionController::class, 'sincronizar']);

    // Web Producto Catálogo
    Route::get('/web_producto_catalogo/listar',                 [WebProductoCatalogoController::class, 'listar']);
    Route::post('/web_producto_catalogo/crear',                   [WebProductoCatalogoController::class, 'crear']);
    Route::post('/web_producto_catalogo/actualizar',              [WebProductoCatalogoController::class, 'actualizar']);
    Route::post('/web_producto_catalogo/actualizar_seccion',      [WebProductoCatalogoController::class, 'actualizar_seccion']);
    Route::delete('/web_producto_catalogo/eliminar',              [WebProductoCatalogoController::class, 'eliminar']);

    // Web Línea Producto
    Route::get('/web_linea_producto/listar',                      [WebLineaProductoController::class, 'listar']);
    Route::post('/web_linea_producto/crear',                      [WebLineaProductoController::class, 'crear']);
    Route::post('/web_linea_producto/actualizar',                 [WebLineaProductoController::class, 'actualizar']);
    Route::post('/web_linea_producto/actualizar_seccion',         [WebLineaProductoController::class, 'actualizar_seccion']);
    Route::delete('/web_linea_producto/eliminar',                 [WebLineaProductoController::class, 'eliminar']);

    // Web Videos (legacy web_video)
    Route::get('/web_video/listar',                               [WebVideoController::class, 'listar']);
    Route::get('/web_video/obtener_seccion',                      [WebVideoController::class, 'obtener_seccion']);
    Route::post('/web_video/crear',                               [WebVideoController::class, 'crear']);
    Route::post('/web_video/actualizar',                          [WebVideoController::class, 'actualizar']);
    Route::post('/web_video/actualizar_seccion',                  [WebVideoController::class, 'actualizar_seccion']);
    Route::delete('/web_video/eliminar',                          [WebVideoController::class, 'eliminar']);

    // Web Videos galería (web_videos)
    Route::get('/web_videos/obtener',                             [WebVideosController::class, 'obtener']);
    Route::get('/web_videos/listar',                              [WebVideosController::class, 'listar']);
    Route::post('/web_videos/actualizar_pagina',                  [WebVideosController::class, 'actualizar_pagina']);
    Route::post('/web_videos/crear',                              [WebVideosController::class, 'crear']);
    Route::post('/web_videos/actualizar',                         [WebVideosController::class, 'actualizar']);
    Route::delete('/web_videos/eliminar',                          [WebVideosController::class, 'eliminar']);

    // Web Clientes
    Route::get('/web_cliente/obtener_seccion',                    [WebClienteController::class, 'obtener_seccion']);
    Route::post('/web_cliente/actualizar_seccion',                [WebClienteController::class, 'actualizar_seccion']);
    Route::get('/web_cliente/listar_estadisticas',                [WebClienteController::class, 'listar_estadisticas']);
    Route::post('/web_cliente/crear_estadistica',                 [WebClienteController::class, 'crear_estadistica']);
    Route::post('/web_cliente/actualizar_estadistica',            [WebClienteController::class, 'actualizar_estadistica']);
    Route::delete('/web_cliente/eliminar_estadistica',            [WebClienteController::class, 'eliminar_estadistica']);
    Route::get('/web_cliente/listar_logos',                       [WebClienteController::class, 'listar_logos']);
    Route::post('/web_cliente/crear_logo',                        [WebClienteController::class, 'crear_logo']);
    Route::post('/web_cliente/actualizar_logo',                   [WebClienteController::class, 'actualizar_logo']);
    Route::delete('/web_cliente/eliminar_logo',                   [WebClienteController::class, 'eliminar_logo']);

    // Web Contacto Mensajes (solo lectura admin)
    Route::get('/web_contacto_mensaje/listar',                    [WebContactoMensajeController::class, 'listar']);
    Route::get('/web_contacto_mensaje/obtener',                   [WebContactoMensajeController::class, 'obtener']);
    Route::delete('/web_contacto_mensaje/eliminar',                [WebContactoMensajeController::class, 'eliminar']);

    Route::get('/web_whatsapp_config/obtener',                    [WebWhatsappConfigController::class, 'obtener']);
    Route::post('/web_whatsapp_config/actualizar',                  [WebWhatsappConfigController::class, 'actualizar']);

    // Configuración de la página "Lista Detalle" (carrito)
    Route::get('/web_carrito_config/obtener',                     [WebCarritoConfigController::class, 'obtener']);
    Route::post('/web_carrito_config/actualizar',                  [WebCarritoConfigController::class, 'actualizar']);

    // Página Nosotros (banner, marcas, galería)
    Route::get('/web_pagina_nosotros/obtener',                    [WebPaginaNosotrosController::class, 'obtener']);
    Route::post('/web_pagina_nosotros/actualizar_pagina',         [WebPaginaNosotrosController::class, 'actualizar_pagina']);
    Route::post('/web_pagina_nosotros/actualizar_quienes',        [WebPaginaNosotrosController::class, 'actualizar_quienes']);
    Route::post('/web_pagina_nosotros/crear_beneficio',           [WebPaginaNosotrosController::class, 'crear_beneficio']);
    Route::post('/web_pagina_nosotros/actualizar_beneficio',      [WebPaginaNosotrosController::class, 'actualizar_beneficio']);
    Route::delete('/web_pagina_nosotros/eliminar_beneficio',      [WebPaginaNosotrosController::class, 'eliminar_beneficio']);
    Route::post('/web_pagina_nosotros/crear_contador',            [WebPaginaNosotrosController::class, 'crear_contador']);
    Route::post('/web_pagina_nosotros/actualizar_contador',       [WebPaginaNosotrosController::class, 'actualizar_contador']);
    Route::delete('/web_pagina_nosotros/eliminar_contador',       [WebPaginaNosotrosController::class, 'eliminar_contador']);
    Route::post('/web_pagina_nosotros/actualizar_producto_seccion',[WebPaginaNosotrosController::class, 'actualizar_producto_seccion']);
    Route::post('/web_pagina_nosotros/crear_producto',            [WebPaginaNosotrosController::class, 'crear_producto']);
    Route::post('/web_pagina_nosotros/actualizar_producto',       [WebPaginaNosotrosController::class, 'actualizar_producto']);
    Route::delete('/web_pagina_nosotros/eliminar_producto',       [WebPaginaNosotrosController::class, 'eliminar_producto']);

    // Página Productos
    Route::get('/web_pagina_productos/obtener',                   [WebPaginaProductosController::class, 'obtener']);
    Route::post('/web_pagina_productos/actualizar_pagina',        [WebPaginaProductosController::class, 'actualizar_pagina']);
    Route::post('/web_pagina_productos/actualizar_galeria_seccion',[WebPaginaProductosController::class, 'actualizar_galeria_seccion']);
    Route::post('/web_pagina_productos/crear_galeria',            [WebPaginaProductosController::class, 'crear_galeria']);
    Route::post('/web_pagina_productos/actualizar_galeria',      [WebPaginaProductosController::class, 'actualizar_galeria']);
    Route::delete('/web_pagina_productos/eliminar_galeria',       [WebPaginaProductosController::class, 'eliminar_galeria']);

    // Página Máquinarias
    Route::get('/web_pagina_maquinarias/obtener',                 [WebPaginaMaquinariasController::class, 'obtener']);
    Route::post('/web_pagina_maquinarias/actualizar_pagina',      [WebPaginaMaquinariasController::class, 'actualizar_pagina']);
    Route::post('/web_pagina_maquinarias/crear_item',             [WebPaginaMaquinariasController::class, 'crear_item']);
    Route::post('/web_pagina_maquinarias/actualizar_item',        [WebPaginaMaquinariasController::class, 'actualizar_item']);
    Route::delete('/web_pagina_maquinarias/eliminar_item',        [WebPaginaMaquinariasController::class, 'eliminar_item']);

    // Página Contacto
    Route::get('/web_pagina_contacto/obtener',                    [WebPaginaContactoController::class, 'obtener']);
    Route::post('/web_pagina_contacto/actualizar',                [WebPaginaContactoController::class, 'actualizar']);
    Route::post('/web_pagina_contacto/crear_columna',             [WebPaginaContactoController::class, 'crear_columna']);
    Route::post('/web_pagina_contacto/actualizar_columna',        [WebPaginaContactoController::class, 'actualizar_columna']);
    Route::delete('/web_pagina_contacto/eliminar_columna',        [WebPaginaContactoController::class, 'eliminar_columna']);

    //Web Lo más vendido
    Route::get('/web_masvendido/listar',                         [WebMasVendidoController::class, 'listar']);
    Route::post('/web_masvendido/actualizar',                    [WebMasVendidoController::class, 'actualizar']);
    //Ventas: Pedidos
    Route::get('/pedidos/listar',                                [PedidosController::class, 'listar']);
    Route::get('/pedidos/obtener',                               [PedidosController::class, 'obtener']);
    Route::get('/pedidos/listar_factura',                        [PedidosController::class, 'listar_factura']);
    Route::match(['put', 'post'], '/pedidos/actualizar_estado_pedido', [PedidosController::class, 'actualizar_estado_pedido']);
    Route::get('/pedidos/listar_filtro',                         [PedidosController::class, 'listar_filtro']);
    Route::put('/pedidos/asignar_usuario',                       [PedidosController::class, 'asignar_pedido_usuario']);
    Route::put('/pedidos/actualizar_estado_devolucion',          [PedidosController::class, 'actualizar_estado_devolucion']);
    Route::put('/pedidos/actualizar_comprobante_conforme',       [PedidosController::class, 'actualizar_comprobante_conforme']);
    Route::delete('/pedidos/eliminar',                           [PedidosController::class, 'eliminar']);
    Route::get('/pedidos/historial_estados',                     [PedidosController::class, 'listar_historial_estados']);
    Route::put('/pedidos/historial_estados/actualizar',          [PedidosController::class, 'actualizar_historial_estado']);
    Route::delete('/pedidos/historial_estados/eliminar',         [PedidosController::class, 'eliminar_historial_estado']);
    Route::delete('/pedidos/historial_estados/eliminar_por_estado', [PedidosController::class, 'eliminar_historial_por_estado']);
    Route::delete('/pedidos/historial_estados/eliminar_todo',    [PedidosController::class, 'eliminar_todo_historial']);
    
    // Balance Ventas
    Route::get('/balance_ventas/mensual',                        [BalanceVentasController::class, 'mensual']);
    Route::get('/balance_ventas/trimestral',                     [BalanceVentasController::class, 'trimestral']);
    Route::get('/balance_ventas/anual',                          [BalanceVentasController::class, 'anual']);
    Route::get('/balance_ventas/descargar_mensual',              [BalanceVentasController::class, 'descargar_mensual']);
    Route::get('/balance_ventas/descargar_trimestral',           [BalanceVentasController::class, 'descargar_trimestral']);
    Route::get('/balance_ventas/descargar_anual',                [BalanceVentasController::class, 'descargar_anual']);
    
    Route::get('/storage/{filename}',                            [FileController::class, 'show']);
    Route::get('/storage_ckeditor/{filename}',                   [FileController::class, 'show_ckeditor']);//PARA DARLE PERSMISOS A LAS IMAGENES DE SUBIDA DE IMAGENES CKEDITO
    //=========== PARA ALMACENAR TODAS LAS IMAGENES DEL EDITOR DE TEXTO CKEDITOR====
    Route::post('/producto/crear_imagen_HtmlEditor',             [ProductoController::class, 'crear_imagen_HtmlEditor']);


    //Promociones
    Route::get('/promocion/obtener',                             [PromocionController::class, 'obtener']);
    Route::get('/promocion/listar',                              [PromocionController::class, 'listar']);
    Route::post('/promocion/crear',                              [PromocionController::class, 'crear']);
    Route::put('/promocion/actualizar',                          [PromocionController::class, 'actualizar']);
    Route::delete('/promocion/eliminar',                         [PromocionController::class, 'eliminar']);
    Route::get('/promocion/listar_membresias',                   [PromocionController::class, 'listar_membresias']);

        // Ofertas del día
    Route::get('/ofertas/obtener',                             [OfertaDelDiaController::class, 'obtener']);
    Route::get('/ofertas/listar',                              [OfertaDelDiaController::class, 'listar']);
    Route::post('/ofertas/crear',                              [OfertaDelDiaController::class, 'crear']);
    Route::put('/ofertas/actualizar',                          [OfertaDelDiaController::class, 'actualizar']);
    Route::delete('/ofertas/eliminar',                         [OfertaDelDiaController::class, 'eliminar']);
    Route::put('/ofertas/desactivar',                          [OfertaDelDiaController::class, 'desactivar']);
    Route::get('/ofertas/listar-productos',                    [OfertaDelDiaController::class, 'listarProductos']);
    
    
    // Metadatos del día
    Route::get('/metadatospagina/obtener',                             [MetadatosPaginaController::class, 'obtener']);
    Route::get('/metadatospagina/listar',                              [MetadatosPaginaController::class, 'listar']);
    Route::post('/metadatospagina/crear',                              [MetadatosPaginaController::class, 'crear']);
    Route::put('/metadatospagina/actualizar',                          [MetadatosPaginaController::class, 'actualizar']);
    Route::put('/metadatospagina/desactivar',                          [MetadatosPaginaController::class, 'desactivar']);
    Route::put('/metadatospagina/restaurar',                           [MetadatosPaginaController::class, 'restaurar']);
    

     // Precios por peso
    Route::get('/precios-peso/listar',                           [PrecioPesoController::class, 'listar']);
    Route::post('/precios-peso/crear',                           [PrecioPesoController::class, 'crear']);
    Route::put('/precios-peso/actualizar/{id}',                  [PrecioPesoController::class, 'actualizar']);
    Route::delete('/precios-peso/eliminar/{id}',                 [PrecioPesoController::class, 'eliminar']);
    Route::post('/precios-peso/importar_excel',                  [PrecioPesoController::class, 'importar_excel']);
    Route::get('/precios-peso/descargar_reporte_x_peso',         [PrecioPesoController::class, 'descargar_reporte_x_peso']);
    Route::get('/precios-peso/listar_productos',                 [PrecioPesoController::class, 'listar_productos']);
    Route::post('/precios-peso/obtener',                          [PrecioPesoController::class, 'obtener']);
    
    // Costos de envío
    Route::get('/costo-envio/listar',                            [CostoEnvioController::class, 'listar']);
    Route::post('/costo-envio/crear',                            [CostoEnvioController::class, 'crear']);
    Route::put('/costo-envio/actualizar/{id}',                   [CostoEnvioController::class, 'actualizar']);
    Route::delete('/costo-envio/eliminar/{id}',                  [CostoEnvioController::class, 'eliminar']);

    // Cálculo de envío
    Route::post('/calcular-envio',                               [CalculoEnvioController::class, 'calcular']);

    // Rutas para Categorías Populares
     Route::get('/categoria-popular/listar',                         [CategoriaPopularController::class, 'listar']);
     Route::get('/categoria-popular/obtener/{id}',                   [CategoriaPopularController::class, 'obtener']);
     Route::post('/categoria-popular/crear',                         [CategoriaPopularController::class, 'crear']);
     Route::post('/categoria-popular/actualizar/{id}',                [CategoriaPopularController::class, 'actualizar']);
     Route::delete('/categoria-popular/eliminar/{id}',               [CategoriaPopularController::class, 'eliminar']);

     // Rutas para Banners Populares
     Route::get('/banner-popular/listar',                            [BannerPopularController::class, 'listar']);
     Route::get('/banner-popular/obtener/{id}',                      [BannerPopularController::class, 'obtener']);
     Route::post('/banner-popular/crear',                            [BannerPopularController::class, 'crear']);
     Route::post('/banner-popular/actualizar/{id}',                   [BannerPopularController::class, 'actualizar']);
     Route::delete('/banner-popular/eliminar/{id}',                  [BannerPopularController::class, 'eliminar']);

     // Pasarelas de Pago
     Route::get('/auth-proveedor/listar',                    [SeguridadAuthProveedorController::class, 'listar']);
     Route::post('/auth-proveedor/habilitar',                [SeguridadAuthProveedorController::class, 'habilitar']);
     Route::post('/auth-proveedor/predeterminado',           [SeguridadAuthProveedorController::class, 'predeterminado']);
     Route::post('/auth-proveedor/actualizar-config',       [SeguridadAuthProveedorController::class, 'actualizarConfig']);

     Route::get('/pasarela/listar',                          [PagoPasarelaController::class, 'listar']);
     Route::get('/pasarela/activa',                          [PagoPasarelaController::class, 'obtenerActiva']);
     Route::post('/pasarela/activar',                        [PagoPasarelaController::class, 'activar']);
     Route::post('/pasarela/cambiar-modo',                   [PagoPasarelaController::class, 'cambiarModo']);
     Route::post('/pasarela/actualizar-config',              [PagoPasarelaController::class, 'actualizarConfig']);

     //Stripe
     Route::post('/pasarela/stripe/payment-intent', [PagoPasarelaController::class, 'stripeCrearPaymentIntent']);
     Route::post('/pasarela/stripe/confirmar',      [PagoPasarelaController::class, 'stripeConfirmarPago']);


     // Libro de Reclamos
     Route::get('/web_reclamos/listar',                     [WebReclamosController::class, 'listar']);
     Route::get('/web_reclamos/obtener',                    [WebReclamosController::class, 'obtener']);
     Route::put('/web_reclamos/actualizar',                 [WebReclamosController::class, 'actualizar']);
     Route::delete('/web_reclamos/eliminar',                [WebReclamosController::class, 'eliminar']);

     //Ubigue
     Route::get('/ubigeo/listar_departamentos',             [UbigeoController::class, 'listar_departamentos']);
     Route::get('/ubigeo/obtener_provicias',                [UbigeoController::class, 'obtener_provicias']);
     Route::get('/ubigeo/obtener_distritos',                [UbigeoController::class, 'obtener_distritos']);

    Route::get('/beneficio/listar', [BeneficioController::class, 'listar']);
    Route::put('/beneficio/actualizar', [BeneficioController::class, 'actualizar']);

    // Favicon panel / web (archivos en public/)
    Route::post('/config/favicon', [AppFaviconController::class, 'actualizar']);

    // ── Configuración de Delivery ─────────────────────────────────────────────
    Route::get('/delivery-config/obtener',    [DeliveryConfigController::class, 'obtener']);
    Route::put('/delivery-config/actualizar', [DeliveryConfigController::class, 'actualizar']);
    Route::put('/delivery-config/estado',     [DeliveryConfigController::class, 'cambiarEstado']);
    Route::post('/delivery-config/calcular',  [DeliveryConfigController::class, 'calcular']);

    // ── Popup Promocional ────────────────────────────────────────────────────
    Route::get('/web-popup/obtener',      [WebPopupPromoController::class, 'obtener']);
    Route::post('/web-popup/actualizar',  [WebPopupPromoController::class, 'actualizar']);
    Route::post('/web-popup/toggle',      [WebPopupPromoController::class, 'toggle']);
    Route::get('/web-popup/historial',              [WebPopupPromoController::class, 'historial']);
    Route::post('/web-popup/historial_guardar',     [WebPopupPromoController::class, 'historial_guardar']);
    Route::post('/web-popup/historial_nueva',       [WebPopupPromoController::class, 'historial_nueva']);
    Route::post('/web-popup/historial_restaurar',   [WebPopupPromoController::class, 'historial_restaurar']);
    Route::delete('/web-popup/historial_eliminar',  [WebPopupPromoController::class, 'historial_eliminar']);

    Route::get('/web_paso_pedido/listar', [WebPasoPedidoController::class, 'listar']);
    Route::post('/web_paso_pedido/crear', [WebPasoPedidoController::class, 'crear']);
    Route::post('/web_paso_pedido/actualizar', [WebPasoPedidoController::class, 'actualizar']);
    Route::delete('/web_paso_pedido/eliminar', [WebPasoPedidoController::class, 'eliminar']);

    Route::get('/web_cuenta_pago/listar', [WebCuentaPagoController::class, 'listar']);
    Route::post('/web_cuenta_pago/crear', [WebCuentaPagoController::class, 'crear']);
    Route::post('/web_cuenta_pago/actualizar', [WebCuentaPagoController::class, 'actualizar']);
    Route::delete('/web_cuenta_pago/eliminar', [WebCuentaPagoController::class, 'eliminar']);

    Route::get('/web_hero_rossety/slides', [WebHeroRossetyController::class, 'listarSlides']);
    Route::post('/web_hero_rossety/slide', [WebHeroRossetyController::class, 'guardarSlide']);
    Route::delete('/web_hero_rossety/slide', [WebHeroRossetyController::class, 'eliminarSlide']);
    Route::get('/web_hero_rossety/cards', [WebHeroRossetyController::class, 'listarCards']);
    Route::post('/web_hero_rossety/card', [WebHeroRossetyController::class, 'guardarCard']);
    Route::delete('/web_hero_rossety/card', [WebHeroRossetyController::class, 'eliminarCard']);

});

// Route::get('/programacion/export_excel',           [ProgramacionController::class, 'export_excel']);


composer require tymon/jwt-auth
Tymon\JWTAuth\Providers\LaravelServiceProvider::class,

php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret

*************** OTRO *********************
https://jwt-auth.readthedocs.io/en/develop/laravel-installation/
https://medium.com/@a3rxander/how-to-implement-jwt-authentication-in-laravel-11-26e6d7be5a41
https://jurin.medium.com/securing-laravel-10-api-using-jwt-a5b6dca58fd7

INSTALANDO SWAGGER
====================
No se pudo
https://medium.com/@mark.tabletpc/set-up-laravel-with-swagger-for-comprehensive-api-documentation-step-by-step-instructions-d30552ca8051
composer require zircote/swagger-php
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
php artisan l5-swagger:generate
http://localhost:8000/api/documentation
 
 Template web
 =============
https://github.com/uilibrary/matx-react

LIMPIAR PORQUE SE PEGA LA BD
==============================
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear

Instalar reporte excel
==========================
https://docs.laravel-excel.com/3.1/getting-started/installation.html
composer require maatwebsite/excel

retornar la version anteior de composer
===================
composer self-update


Run composer install
Run php artisan key:generate
Run php artisan migrate --seed (it has some seeded data for your testing)

Comando para crear reporte
========================
https://docs.laravel-excel.com/3.1/exports/
php artisan make:export UsersExport --model=User

Para que arranque laravel
========================
En la version nueva
Habilitar  en php.ini
extension=gd
Instalar composer y darle comoposer install
Instalar en la documentacion el laravel


npm run dev

============= 
PARA INSTALAR
https://www.itsolutionstuff.com/post/laravel-10-bootstrap-auth-scaffolding-tutorialexample.html

USUARIO
======
 Auth::user()->id;

sessiones
===========
Session::set('variableName', $value);
Session::get('variableName');

{{session()->get('email')}}

Ejemplod de helper
====================
https://github.com/sawastacks/Build-Laravel-10-Multi-Vendor-ECommerce-project/tree/main
https://github.com/busrakk/laravel10-ecommerce/tree/main

Helper
=========
https://stackoverflow.com/questions/28290332/how-to-create-custom-helper-functions-in-laravel

INSTALANDO EL SHOPCARP
=========================
https://github.com/mindscms/laravelshoppingcart

Ejemplod de Libro de Reclamo
========================
https://academico.isur.edu.pe/reclamos/

Ejemplos slider
=================
https://codepen.io/sendstufftodanny/pen/PZXRdP
https://codepen.io/Nairi2001/pen/GRmOQWy

PARA QUE SALGA EN LA CONFIGURACION
====================================
 php artisan vendor:publish --provider="Gloudemans\Shoppingcart\ShoppingcartServiceProvider" --tag="config" 
 Se creara el file: cart.php en config
// Set a default tax rate (10%)

MDEIO DE PAGO
=============
payvalida.com

INSTALANDO POLITICAS Y SEGURIDAD
=================================
https://github.com/whitecube/laravel-cookie-consent

https://www.youtube.com/watch?v=Zx55UJHXxNY

INSTALADO LIVEWIRE
=======================
composer require livewire/livewire
php artisan livewire:publish --config

INSTALANDO SOCIAL
=====================
https://www.itsolutionstuff.com/post/laravel-10-socialite-login-with-google-account-exampleexample.html
https://www.itsolutionstuff.com/post/laravel-10-login-with-facebook-account-exampleexample.html#google_vignette

composer require laravel/socialite
https://console.cloud.google.com/apis/dashboard?hl=es-419&project=bibleapp-d6817

INTALALNDO ALERT
====================
composer remove tdanandeh/sweet-alert
https://realrashid.github.io/sweet-alert/config

CREAR CUENTA IZIPAY
===================
https://secure.micuentaweb.pe/vads-merchant/

ANOTACION
=================
 $message = null;
$message = 'Procesos exitosos.';
 app\Http\Controllers\Api\ProductoController.php
return response()->json([
    'success' => $message != null ? true : false,
    'message' => 'Mesaje de procesos exitosos.',
    'result' => [
        'message' => $message,
    ]
],200);


==========================================================
RECURSOS PARA EL TRABAJO
****************************

https://picsum.photos/
https://www.agrovetmarket.com/productos-veterinarios/c/antiinflamatorios
https://unsplash.com/s/photos/medicament

 @if(!empty($listProductIndex) && is_iterable($listProductIndex))


 ====================
 COMPONENTES A CONOCER
 ************************************************
 C:\xampp\htdocs\ecommerce-Fernando\ecommerce-Back\public\template1\js\custom.js
 public\template1\css\superslides.css
 public\template2\assets\css\style3.css
 public\template1\js\custom.js
 public\template1\css\style.css

https://www.websiteplanet.com/es/webtools/unminify-js/

cloudflared tunnel --url http://localhost:8000

cloudflared tunnel --url http://localhost:8000

Color de la empresa
====================

#3c63c1

https://docs.culqi.com/es/documentacion/checkout/v4/culqi-checkout
INSTALANDO
===========
composer require culqi/culqi-php


CONSULTAS EN TIEMPO REAL
===============================
-- 1. Activar el registro general (si está desactivado)
SET GLOBAL general_log = 'ON';

-- 2. Configurar la salida del log a un archivo (FILE) o a la tabla (TABLE)
SET GLOBAL log_output = 'FILE';  -- Para guardar en archivo .log
-- O si prefieres verlo en una tabla:
-- SET GLOBAL log_output = 'TABLE';

-- 3. Verificar la ubicación del archivo de log
SHOW VARIABLES LIKE 'general_log_file';
 --Abre PowerShell como Administrador y ejecuta:
Get-Content "C:\ProgramData\MySQL\MySQL Server 8.0\Data\DESKTOP-HHOFPM9.log" -Wait
Para desactivar el log después:
SET GLOBAL general_log = 'OFF';

Out-File -FilePath "C:\ProgramData\MySQL\MySQL Server 8.0\Data\DESKTOP-HHOFPM9.log" -Encoding ASCII -NoNewline

 SE INSTALA VITE
 =====================
 Para produccion:
- pnpm run buil (Eso se compila automaticamente)
- pnpm run dev
- pnpm install

ADMINISTRADOR
=============
https://admin.agrovetperu.com/

WEB
=====
https://agrovetperu.com/

USUARIO ADMIN Y CLIENTE (PRUEBAS):
admin@gmail.com
CLAVE: 123456789

WILSON
============
Vegaruizwilson@gmail.com

INSTALANDO
==================
composer require fruitcake/laravel-cors

GENERAR VITE.PHP
====================
php artisan vendor:publish --tag=laravel-vite

CREAR UN COMPONENTE INCLUDE CON PROVIDER - REUTILIZABLE
=========================================================
php artisan make:provider EstrellasComponentsServiceProvider


CREANDO EMAIL NOTIFICACION
============================
php artisan make:mail OrderConfirmationMail




ba29a588-edd1-4663-b53e-6dd94ebf050d
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDLnfTPD5Exm1BU5Qx+gfEocg/H
KX86aIo30HCZWm2Qh9AdhlesEcSDgvPl+zKwUMsvacuB4r8szwsSAN7svOhldvRx
pzUnFY4vgXmr+eOM/BfkTDX2cGfFD7x/c7m2R4Lcev3vZH8z28d1LMbRsq6Mdvvw
fNFZNn/Kjxu8EqEzbwIDAQAB
-----END PUBLIC KEY-----



https://www.facebook.com/reel/1109070684503894
 
 ===============================
 ************ SEO ***************
 ===============================
composer require spatie/laravel-sitemap
php artisan vendor:publish --provider="Spatie\Sitemap\SitemapServiceProvider" --tag=sitemap-config

INSTALAR UN MINIFICADOR HTML
===============================
composer require fahlisaputra/laravel-minify
php artisan make:middleware ForceUtf8Middleware

php artisan optimize:clear

Hay que esperar que surtan efecto porque demora
============
uitlicemos esto de prueba en todo el archivo, esto es para que cargue
==========
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test</title>
</head>
<body>
    <h1>¿Quiénes somos? Éíóúñ</h1>
</body>
</html>
LIMPIAR PORQUE SE PEGA LA BD
==============================
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear


 Log::info('test', [
    'result' => $result,
    'type' => gettype($result),
    'dump' => var_export($result, true)
]);

=====================
COLOR EMPRESA PRESTOMART:
3)	Tipografía Monserrat Categoria de fuentes Sans-Serif, color tono #922b21



  // // Script para navegación entre vistas
    // document.addEventListener('DOMContentLoaded', function() {
    //     const mainView = document.getElementById('cookies-main-view');
    //     const configView = document.getElementById('cookies-config-view');
    //     const showConfigBtn = document.getElementById('cookies-show-config');
    //     const backBtn = document.getElementById('cookies-back-main');

    //     if (showConfigBtn) {
    //         showConfigBtn.addEventListener('click', function(e) {
    //             e.preventDefault();
    //             mainView.style.display = 'none';
    //             configView.style.display = 'block';
    //         });
    //     }

    //     if (backBtn) {
    //         backBtn.addEventListener('click', function(e) {
    //             e.preventDefault();
    //             configView.style.display = 'none';
    //             mainView.style.display = 'block';
    //         });
    //     }
    // });

 php artisan db:seed --class=AdminFullPermissionsSeeder


 c9302b

=============== URI PARA AUTENTICACION REDIRECT ===========
 https://rossety.pe/auth/google/callback
 http://localhost:8000/auth/google/callback


 http://localhost:8000/artisan-clear.php?key=lucdesoft2026
 

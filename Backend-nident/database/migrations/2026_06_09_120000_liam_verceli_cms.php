<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ROYAL_URLS = [
        '/promo-banner/index',
        '/experiencias/index',
        '/pagina-galeria/index',
        '/masaje-faq/index',
        '/pagina-experiencias/index',
        '/pagina-masajes/index',
    ];

    private const LIAM_MENUS = [
        ['nombre' => 'Productos', 'url' => '/producto/index', 'Icon' => 'inventory_2'],
        ['nombre' => 'Categorías', 'url' => '/categoria/index', 'Icon' => 'category'],
        ['nombre' => 'Importar productos', 'url' => '/producto/importar', 'Icon' => 'upload'],
        ['nombre' => 'Pedidos', 'url' => '/pedidos/index', 'Icon' => 'shopping_cart'],
        ['nombre' => 'Promociones', 'url' => '/promocion/index', 'Icon' => 'local_offer'],
        ['nombre' => 'Ofertas del día', 'url' => '/ofertas/index', 'Icon' => 'flash_on'],
        ['nombre' => 'Header tienda', 'url' => '/header/index', 'Icon' => 'web_asset'],
        ['nombre' => 'Slider home', 'url' => '/slider/index', 'Icon' => 'view_carousel'],
        ['nombre' => 'Banners promo', 'url' => '/popular/index', 'Icon' => 'campaign'],
        ['nombre' => 'Carrusel logos', 'url' => '/carrusel/index', 'Icon' => 'view_carousel'],
        ['nombre' => 'Lookbook', 'url' => '/lookbook/index', 'Icon' => 'auto_stories'],
        ['nombre' => 'Banner footer', 'url' => '/footer-banner/index', 'Icon' => 'image'],
        ['nombre' => 'Página nosotros', 'url' => '/pagina-nosotros/index', 'Icon' => 'info'],
        ['nombre' => 'Contadores nosotros', 'url' => '/contadores/index', 'Icon' => 'pin'],
        ['nombre' => 'Nuestro equipo', 'url' => '/nuestro_equipo/index', 'Icon' => 'groups'],
        ['nombre' => 'Página contacto', 'url' => '/pagina-contacto/index', 'Icon' => 'contact_mail'],
        ['nombre' => 'FAQ', 'url' => '/faq/index', 'Icon' => 'help'],
        ['nombre' => 'Footer tienda', 'url' => '/footer/index', 'Icon' => 'footer'],
        ['nombre' => 'Libro reclamaciones', 'url' => '/libro_reclamo/index', 'Icon' => 'menu_book'],
        ['nombre' => 'Envíos', 'url' => '/envios/index', 'Icon' => 'local_shipping'],
        ['nombre' => 'Pasarela pago', 'url' => '/configuracion/pasarela', 'Icon' => 'payments'],
        ['nombre' => 'Metadatos SEO', 'url' => '/metadatospagina/index', 'Icon' => 'travel_explore'],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('web_lookbook')) {
            Schema::create('web_lookbook', function (Blueprint $table) {
                $table->unsignedTinyInteger('id')->primary()->default(1);
                $table->string('subtitulo', 255)->nullable();
                $table->string('titulo', 500)->nullable();
                $table->string('titulo_accent', 255)->nullable();
                $table->text('descripcion')->nullable();
                $table->string('btn_texto', 120)->nullable();
                $table->string('btn_url', 500)->nullable();
                $table->string('url_imagen', 500)->nullable();
                $table->string('url_imagen_lateral', 500)->nullable();
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });

            DB::table('web_lookbook')->insert([
                'id'                 => 1,
                'subtitulo'          => 'Lookbook 2027',
                'titulo'             => '#Liam',
                'titulo_accent'      => 'Vercceli',
                'descripcion'        => 'Ofrecemos Polos de Calidad de Excelencia en variedad de modelos, tallas y colores.',
                'btn_texto'          => 'Ver Colección',
                'btn_url'            => '/tienda',
                'url_imagen'         => 'temp02/img/inicio/4.jpg',
                'url_imagen_lateral' => 'temp02/img/inicio/4.jpg',
                'Activo'             => 'S',
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        if (! Schema::hasTable('web_footer_banner')) {
            Schema::create('web_footer_banner', function (Blueprint $table) {
                $table->unsignedTinyInteger('id')->primary()->default(1);
                $table->string('subtitulo', 255)->nullable();
                $table->string('titulo', 500)->nullable();
                $table->string('btn_texto', 120)->nullable();
                $table->string('btn_url', 500)->nullable();
                $table->string('url_imagen', 500)->nullable();
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });

            DB::table('web_footer_banner')->insert([
                'id'         => 1,
                'subtitulo'  => 'Colección 2027',
                'titulo'     => 'Polos de Moda — Calidad que se nota',
                'btn_texto'  => 'Ver Colección',
                'btn_url'    => '/tienda',
                'url_imagen' => 'temp02/img/inicio/2.jpg',
                'Activo'     => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('web_header') && ! Schema::hasColumn('web_header', 'ticker_items')) {
            Schema::table('web_header', function (Blueprint $table) {
                $table->json('ticker_items')->nullable()->after('nav_items');
            });
        }

        if (Schema::hasTable('web_header')) {
            $nav = [
                ['label' => 'Inicio', 'href' => '/', 'url' => '/'],
                ['label' => 'Tienda', 'href' => '/tienda', 'url' => '/tienda'],
                ['label' => 'Nosotros', 'href' => '/nosotros', 'url' => '/nosotros'],
                ['label' => 'Contacto', 'href' => '/contacto', 'url' => '/contacto'],
            ];
            $ticker = [
                ['texto' => 'Tu imagen en nuestras manos', 'icon' => 'temp02/img/icons/delivery.png'],
                ['texto' => 'La moda te renueva el estilo permanece en tí', 'icon' => 'temp02/img/icons/voucher.png'],
                ['texto' => 'Envíos a todo el Perú', 'icon' => 'temp02/img/icons/sales.png'],
            ];
            DB::table('web_header')->where('id_header', 1)->update([
                'url_logo'      => 'temp02/img/inicio/logo-principal.jpg',
                'nav_items'     => json_encode($nav, JSON_UNESCAPED_UNICODE),
                'ticker_items'  => json_encode($ticker, JSON_UNESCAPED_UNICODE),
                'topbar_promo'  => 'Lima, Perú — contacto@rossety.pe',
                'updated_at'    => now(),
            ]);
        }

        $this->seedSistemaMenu();
    }

    public function down(): void
    {
        Schema::dropIfExists('web_footer_banner');
        Schema::dropIfExists('web_lookbook');

        if (Schema::hasTable('web_header') && Schema::hasColumn('web_header', 'ticker_items')) {
            Schema::table('web_header', function (Blueprint $table) {
                $table->dropColumn('ticker_items');
            });
        }

        if (Schema::hasTable('sistema_menu')) {
            DB::table('sistema_menu')->whereIn('url', [
                '/lookbook/index',
                '/footer-banner/index',
            ])->delete();

            foreach (self::ROYAL_URLS as $url) {
                DB::table('sistema_menu')->where('url', $url)->update(['Activo' => 'S']);
            }
        }
    }

    private function seedSistemaMenu(): void
    {
        if (! Schema::hasTable('sistema_menu')) {
            return;
        }

        DB::table('sistema_menu')->whereIn('url', self::ROYAL_URLS)->update(['Activo' => 'N']);

        $idModulo = $this->resolveTiendaModuloId();
        $maxOrden = (int) DB::table('sistema_menu')->max('orden');

        foreach (self::LIAM_MENUS as $menu) {
            $exists = DB::table('sistema_menu')->where('url', $menu['url'])->first();
            if ($exists) {
                $update = [
                    'nombre'     => $menu['nombre'],
                    'Activo'     => 'S',
                    'updated_at' => now(),
                ];
                if (Schema::hasColumn('sistema_menu', 'id_modulo') && $idModulo) {
                    $update['id_modulo'] = $idModulo;
                }
                if (Schema::hasColumn('sistema_menu', 'Icon') && ! empty($menu['Icon'])) {
                    $update['Icon'] = $menu['Icon'];
                }
                DB::table('sistema_menu')->where('url', $menu['url'])->update($update);
                $this->assignMenuToAllRoles((int) $exists->id_menu);
                continue;
            }

            $maxOrden++;
            $data = [
                'nombre'     => $menu['nombre'],
                'url'        => $menu['url'],
                'Activo'     => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('sistema_menu', 'id_modulo')) {
                $data['id_modulo'] = $idModulo;
            }
            if (Schema::hasColumn('sistema_menu', 'Icon')) {
                $data['Icon'] = $menu['Icon'];
            }
            if (Schema::hasColumn('sistema_menu', 'orden')) {
                $data['orden'] = $maxOrden;
            }

            $idMenu = DB::table('sistema_menu')->insertGetId($data);
            $this->assignMenuToAllRoles($idMenu);
        }
    }

    private function resolveTiendaModuloId(): ?int
    {
        if (! Schema::hasTable('sistema_modulo')) {
            return null;
        }

        $id = DB::table('sistema_modulo')
            ->where('nombre', 'like', '%Tienda Virtual%')
            ->value('id_modulo');

        if ($id) {
            return (int) $id;
        }

        $data = [
            'nombre'     => 'Tienda Virtual',
            'Activo'     => 'S',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('sistema_modulo', 'Orden')) {
            $data['Orden'] = ((int) DB::table('sistema_modulo')->max('Orden')) + 1;
        }

        return (int) DB::table('sistema_modulo')->insertGetId($data);
    }

    private function assignMenuToAllRoles(int $idMenu): void
    {
        if (! Schema::hasTable('seguridad_roles_menu') || ! Schema::hasTable('seguridad_roles')) {
            return;
        }

        foreach (DB::table('seguridad_roles')->pluck('id_roles') as $idRoles) {
            if (! DB::table('seguridad_roles_menu')->where('id_roles', $idRoles)->where('id_menu', $idMenu)->exists()) {
                DB::table('seguridad_roles_menu')->insert([
                    'id_roles' => $idRoles,
                    'id_menu'  => $idMenu,
                ]);
            }
        }
    }
};

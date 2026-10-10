<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sistema_menu')) {
            return;
        }

        $menus = [
            ['nombre' => 'Productos destacados', 'url' => '/producto-destacado/index', 'Icon' => 'inventory'],
            ['nombre' => 'Catálogo productos', 'url' => '/producto-catalogo/index', 'Icon' => 'category'],
            ['nombre' => 'Líneas producto', 'url' => '/linea-producto/index', 'Icon' => 'view_module'],
            ['nombre' => 'Videos', 'url' => '/videos/index', 'Icon' => 'ondemand_video'],
            ['nombre' => 'Clientes', 'url' => '/clientes/index', 'Icon' => 'groups'],
            ['nombre' => 'Contacto landing', 'url' => '/contacto-landing/index', 'Icon' => 'mail'],
            ['nombre' => 'Mensajes contacto', 'url' => '/contacto-mensajes/index', 'Icon' => 'inbox'],
            ['nombre' => 'About características', 'url' => '/about-caracteristica/index', 'Icon' => 'star'],
            ['nombre' => 'Confianza ítems', 'url' => '/confianza-item/index', 'Icon' => 'verified'],
        ];

        $idModulo = null;
        if (Schema::hasTable('sistema_modulo')) {
            $idModulo = DB::table('sistema_modulo')
                ->where('nombre', 'like', '%Paginas%')
                ->orWhere('nombre', 'like', '%Páginas%')
                ->orWhere('nombre', 'like', '%Web%')
                ->value('id_modulo');
        }

        $maxOrden = (int) DB::table('sistema_menu')->max('orden');

        foreach ($menus as $menu) {
            if (DB::table('sistema_menu')->where('url', $menu['url'])->exists()) {
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
            if (Schema::hasColumn('sistema_menu', 'id_menu_padre')) {
                $data['id_menu_padre'] = null;
            }
            $idMenu = DB::table('sistema_menu')->insertGetId($data);

            if (Schema::hasTable('seguridad_roles_menu')) {
                $roles = DB::table('seguridad_roles')->pluck('id_roles');
                foreach ($roles as $idRoles) {
                    if (!DB::table('seguridad_roles_menu')->where('id_roles', $idRoles)->where('id_menu', $idMenu)->exists()) {
                        DB::table('seguridad_roles_menu')->insert([
                            'id_roles' => $idRoles,
                            'id_menu'  => $idMenu,
                        ]);
                    }
                }
            }
        }

        $this->seedClientes();
    }

    private function seedClientes(): void
    {
        if (!Schema::hasTable('web_cliente_seccion') || DB::table('web_cliente_seccion')->count() > 0) {
            return;
        }

        DB::table('web_cliente_seccion')->insert([
            'id'           => 1,
            'tag'          => 'Nuestros Clientes',
            'titulo'       => 'Empresas que confían en nosotros',
            'descripcion'  => 'Trabajamos con librerías, imprentas, emprendedores y empresas de todo el Perú.',
            'cta_texto'    => '¿Quieres ser parte de nuestra red de clientes?',
            'cta_url'      => 'https://wa.me/51981629466',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        if (Schema::hasTable('web_cliente_estadistica') && DB::table('web_cliente_estadistica')->count() === 0) {
            $stats = [
                ['valor' => '+8,000', 'etiqueta' => 'Clientes satisfechos', 'icono_clase' => 'fas fa-users', 'orden' => 1],
                ['valor' => '+640', 'etiqueta' => 'Productos disponibles', 'icono_clase' => 'fas fa-box-open', 'orden' => 2],
                ['valor' => 'Nacional', 'etiqueta' => 'Envíos a todo el Perú', 'icono_clase' => 'fas fa-shipping-fast', 'orden' => 3],
                ['valor' => '+10 años', 'etiqueta' => 'De experiencia', 'icono_clase' => 'fas fa-star', 'orden' => 4],
            ];
            foreach ($stats as $s) {
                DB::table('web_cliente_estadistica')->insert(array_merge($s, ['Activo' => 'S', 'created_at' => now(), 'updated_at' => now()]));
            }
        }

        if (Schema::hasTable('web_cliente_logo') && DB::table('web_cliente_logo')->count() === 0) {
            for ($n = 1; $n <= 8; $n++) {
                DB::table('web_cliente_logo')->insert([
                    'nombre'     => "Cliente {$n}",
                    'url_imagen' => "temp02/assets/img/clients/{$n}.png",
                    'url_enlace' => 'https://wa.me/51981629466',
                    'orden'      => $n,
                    'Activo'     => 'S',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('sistema_menu')) {
            return;
        }
        $urls = [
            '/producto-destacado/index', '/producto-catalogo/index', '/linea-producto/index',
            '/videos/index', '/clientes/index', '/contacto-landing/index', '/contacto-mensajes/index',
            '/about-caracteristica/index', '/confianza-item/index',
        ];
        foreach ($urls as $url) {
            $ids = DB::table('sistema_menu')->where('url', $url)->pluck('id_menu');
            foreach ($ids as $idMenu) {
                if (Schema::hasTable('seguridad_roles_menu')) {
                    DB::table('seguridad_roles_menu')->where('id_menu', $idMenu)->delete();
                }
                DB::table('sistema_menu')->where('id_menu', $idMenu)->delete();
            }
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_paso_pedido')) {
            Schema::create('web_paso_pedido', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('orden')->default(0);
                $table->string('titulo', 160);
                $table->string('texto', 500);
                $table->char('Activo', 1)->default('S');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('web_cuenta_pago')) {
            Schema::create('web_cuenta_pago', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('orden')->default(0);
                $table->string('banco', 40);
                $table->string('tipo', 80)->nullable();
                $table->string('numero', 40);
                $table->string('titular', 160);
                $table->char('Activo', 1)->default('S');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('web_hero_slide')) {
            Schema::create('web_hero_slide', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('orden')->default(0);
                $table->string('eyebrow', 120)->nullable();
                $table->string('titulo', 300);
                $table->string('descripcion', 400)->nullable();
                $table->string('cta_texto', 80)->nullable();
                $table->string('cta_url', 300)->nullable();
                $table->string('url_imagen', 400)->nullable();
                $table->char('Activo', 1)->default('S');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('web_hero_card')) {
            Schema::create('web_hero_card', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('orden')->default(0);
                $table->string('badge', 80)->nullable();
                $table->string('titulo', 200);
                $table->string('precio_texto', 80)->nullable();
                $table->string('url', 300)->nullable();
                $table->string('url_imagen', 400)->nullable();
                $table->char('Activo', 1)->default('S');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('web_motivo_rossety')) {
            Schema::create('web_motivo_rossety', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('orden')->default(0);
                $table->string('icono', 80)->nullable();
                $table->string('titulo', 160);
                $table->string('texto', 400);
                $table->char('Activo', 1)->default('S');
                $table->timestamps();
            });
        }

        $this->seed();
        $this->menu();
    }

    public function down(): void
    {
        Schema::dropIfExists('web_motivo_rossety');
        Schema::dropIfExists('web_hero_card');
        Schema::dropIfExists('web_hero_slide');
        Schema::dropIfExists('web_cuenta_pago');
        Schema::dropIfExists('web_paso_pedido');
    }

    private function seed(): void
    {
        if (DB::table('web_paso_pedido')->count() === 0) {
            $pasos = [
                ['Elige tu detalle', 'Explora la tienda y filtra por categoría, precio o temporada.'],
                ['Escríbenos', 'Presiona "Pedir por WhatsApp" y coordinamos colores, frase y fecha.'],
                ['Realiza el pago', 'Paga por BCP o Yape y envíanos tu comprobante.'],
                ['Recibe tu sorpresa', 'Preparamos tu pedido con mucho cariño y lo entregamos o lo recoges.'],
            ];
            foreach ($pasos as $i => $p) {
                DB::table('web_paso_pedido')->insert([
                    'orden' => $i + 1, 'titulo' => $p[0], 'texto' => $p[1], 'Activo' => 'S',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
        if (DB::table('web_cuenta_pago')->count() === 0) {
            $cuentas = [
                ['BCP', 'Banco · N° de cuenta', '19120693956084', 'Patricia Hancco'],
                ['Yape', 'Billetera · N° de celular', '989 914 154', 'Patricia Hancco'],
            ];
            foreach ($cuentas as $i => $c) {
                DB::table('web_cuenta_pago')->insert([
                    'orden' => $i + 1, 'banco' => $c[0], 'tipo' => $c[1], 'numero' => $c[2], 'titular' => $c[3],
                    'Activo' => 'S', 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
        if (DB::table('web_hero_slide')->count() === 0) {
            $slides = [
                ['Catálogo 2026', "Ramos y Detalles\nQue Enamoran", "Rosas, tulipanes y peluches\ncon luces, globos y chocolates", 'Ver ramos', '/tienda?cat=ramos', 'rossety/assets/images/inicio/hero-catalogo-2026.jpg'],
                ['Temporada girasoles', "La Temporada De Flores\nAmarillas Ya Llegó", "Peluches, cajas y regalos de girasol\nhechos con amor para ti", 'Ver temporada', '/tienda?cat=girasoles', 'rossety/assets/images/inicio/hero-girasoles.jpg'],
                ['Pasión en cada detalle', "Hot Wheels\nY Motos Miniatura", "Productos 100% originales,\nideales para coleccionar", 'Ver colección', '/tienda?cat=hotwheels', 'rossety/assets/images/inicio/hero-hotwheels.jpg'],
            ];
            foreach ($slides as $i => $s) {
                DB::table('web_hero_slide')->insert([
                    'orden' => $i + 1, 'eyebrow' => $s[0], 'titulo' => $s[1], 'descripcion' => $s[2],
                    'cta_texto' => $s[3], 'cta_url' => $s[4], 'url_imagen' => $s[5], 'Activo' => 'S',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
        if (DB::table('web_hero_card')->count() === 0) {
            DB::table('web_hero_card')->insert([
                [
                    'orden' => 1, 'badge' => 'Más pedido', 'titulo' => "Ramo Rubi\n30 Rosas", 'precio_texto' => 'S/ 220.00',
                    'url' => '/tienda?tag=destacado', 'url_imagen' => 'rossety/assets/images/catalogo/ramos/ramo-rubi.jpg',
                    'Activo' => 'S', 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'orden' => 2, 'badge' => 'Nuevo', 'titulo' => "Cajas\nTemáticas", 'precio_texto' => 'S/ 12.00',
                    'url' => '/tienda?cat=cajas', 'url_imagen' => 'rossety/assets/images/catalogo/cajas/caja-snopy.jpg',
                    'Activo' => 'S', 'created_at' => now(), 'updated_at' => now(),
                ],
            ]);
        }
        if (DB::table('web_motivo_rossety')->count() === 0) {
            $motivos = [
                ['ph-gift', 'Detalles únicos', 'Cada arreglo es diseñado a tu medida.'],
                ['ph-paw-print', 'Peluches de calidad', 'Suaves, duraderos y de personajes favoritos.'],
                ['ph-flower-lotus', 'Arreglos florales', 'Rosas, tulipanes, girasoles y lirios.'],
                ['ph-heart', 'Hechos con amor', 'Cuidamos cada detalle y cada lazo.'],
                ['ph-confetti', 'Para cada ocasión', 'Cumpleaños, aniversarios, graduaciones y más.'],
            ];
            foreach ($motivos as $i => $m) {
                DB::table('web_motivo_rossety')->insert([
                    'orden' => $i + 1, 'icono' => $m[0], 'titulo' => $m[1], 'texto' => $m[2],
                    'Activo' => 'S', 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
        if (Schema::hasTable('web_testimonios') && DB::table('web_testimonios')->where('badge_seccion', 'rossety')->count() === 0) {
            $quotes = [
                ['María Quispe', 'Huamanga', 'El ramo con Stich quedó hermoso y llegó a tiempo. Mi hija lloró de emoción. Súper recomendados.', 'rossety/assets/images/avatar/4.png'],
                ['Luis Huamán', 'Ayacucho', 'Pedí cajas temáticas por cantidad para el cumpleaños de mis sobrinos y el precio fue excelente.', 'rossety/assets/images/avatar/2.png'],
                ['Rosa Paredes', 'Relave', 'Atención muy amable por WhatsApp y los detalles son tal cual las fotos. Volveré a comprar.', 'rossety/assets/images/avatar/5.png'],
            ];
            foreach ($quotes as $i => $q) {
                DB::table('web_testimonios')->insert([
                    'tipo' => 'texto',
                    'badge_seccion' => 'rossety',
                    'seccion_titulo' => 'Lo que dicen nuestros clientes',
                    'nombre' => $q[0],
                    'subtitulo' => $q[1],
                    'testimonio' => $q[2],
                    'calificacion' => 5,
                    'url_avatar' => $q[3],
                    'orden' => $i + 1,
                    'Activo' => 'S',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function menu(): void
    {
        if (! Schema::hasTable('sistema_menu') || ! Schema::hasTable('sistema_modulo')) {
            return;
        }
        $idModulo = DB::table('sistema_modulo')->where('nombre', 'Tienda Virtual')->value('id_modulo');
        if (! $idModulo) {
            return;
        }
        DB::table('sistema_menu')->where('id_modulo', $idModulo)->where('url', '/lookbook/index')->update(['Activo' => 'N']);

        $items = [
            ['Header', '/header/index', 'web'],
            ['Hero home', '/hero-slide/index', 'view_carousel'],
            ['Más pedidos', '/producto-destacado/index', 'star'],
            ['Opiniones', '/testimonios/index', 'format_quote'],
            ['Cómo pedir', '/pasos-pedido/index', 'format_list_numbered'],
            ['Cuentas BCP y Yape', '/cuentas-pago/index', 'account_balance'],
            ['Tarjetas del hero', '/hero-card/index', 'dashboard'],
            ['Footer', '/footer/index', 'vertical_align_bottom'],
            ['Nosotros', '/pagina-nosotros/index', 'favorite'],
            ['Contacto', '/pagina-contacto/index', 'mail'],
            ['WhatsApp', '/whatsapp/index', 'chat'],
            ['Metadatos SEO', '/metadatospagina/index', 'travel_explore'],
        ];
        $orden = (int) DB::table('sistema_menu')->where('id_modulo', $idModulo)->max('orden');
        foreach ($items as $item) {
            $exists = DB::table('sistema_menu')->where('url', $item[1])->first();
            if ($exists) {
                DB::table('sistema_menu')->where('id_menu', $exists->id_menu)->update([
                    'nombre' => $item[0],
                    'id_modulo' => $idModulo,
                    'Activo' => 'S',
                    'Icon' => $item[2],
                    'updated_at' => now(),
                ]);
                $idMenu = (int) $exists->id_menu;
            } else {
                $orden++;
                $idMenu = (int) DB::table('sistema_menu')->insertGetId([
                    'nombre' => $item[0],
                    'url' => $item[1],
                    'Activo' => 'S',
                    'id_modulo' => $idModulo,
                    'Icon' => $item[2],
                    'orden' => $orden,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->assign($idMenu);
        }
    }

    private function assign(int $idMenu): void
    {
        if (! Schema::hasTable('seguridad_roles_menu') || ! Schema::hasTable('seguridad_roles')) {
            return;
        }
        $roles = DB::table('seguridad_roles')->pluck('id_roles');
        foreach ($roles as $idRoles) {
            $exists = DB::table('seguridad_roles_menu')
                ->where('id_roles', $idRoles)
                ->where('id_menu', $idMenu)
                ->exists();
            if (! $exists) {
                DB::table('seguridad_roles_menu')->insert([
                    'id_roles' => $idRoles,
                    'id_menu' => $idMenu,
                ]);
            }
        }
    }
};

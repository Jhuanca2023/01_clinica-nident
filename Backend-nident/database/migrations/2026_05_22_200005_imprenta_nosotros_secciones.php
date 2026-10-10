<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $img = 'temp02/assets/img/jh_importaciones/inicio';

        if (Schema::hasTable('web_pagina_nosotros')) {
            Schema::table('web_pagina_nosotros', function (Blueprint $table) {
                if (!Schema::hasColumn('web_pagina_nosotros', 'intro_titulo')) {
                    $table->string('intro_titulo', 255)->nullable();
                    $table->text('intro_descripcion')->nullable();
                    $table->string('intro_btn_texto', 120)->nullable();
                    $table->string('intro_btn_url', 500)->nullable();
                    $table->string('intro_url_logo', 500)->nullable();
                    $table->string('catalogo_url_imagen', 500)->nullable();
                    $table->string('frase_texto', 500)->nullable();
                }
            });

            DB::table('web_pagina_nosotros')->where('id', 1)->update([
                'intro_titulo'       => 'Liam Vercceli',
                'intro_descripcion'  => 'Nos especializamos en la fabricación y distribución de soluciones de alta calidad en encuadernación, laminación y papelería, ofreciendo productos innovadores y confiables para satisfacer las necesidades de nuestros clientes.',
                'intro_btn_texto'    => 'CONTÁCTANOS',
                'intro_btn_url'      => '/contacto',
                'intro_url_logo'     => "{$img}/logo_jh.png",
                'catalogo_url_imagen'=> "{$img}/seccion%201.jpg",
                'frase_texto'        => 'Innovando tus proyectos con calidad y eficiencia en cada detalle.',
                'updated_at'         => now(),
            ]);
        }

        if (!Schema::hasTable('web_nosotros_quienes')) {
            Schema::create('web_nosotros_quienes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('titulo', 255)->nullable();
                $table->longText('descripcion')->nullable();
                $table->string('url_imagen', 500)->nullable();
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });
            DB::table('web_nosotros_quienes')->insert([
                'id'          => 1,
                'titulo'      => '¿Quiénes Somos?',
                'descripcion' => '<p>En <strong>Liam Vercceli</strong>, somos fabricantes y distribuidores especializados en ofrecer soluciones integrales de encuadernación, laminación y papelería para profesionales y empresas. Ofrecemos una amplia gama de productos de alta calidad, como espirales de PVC, guillotinas y encuadernadoras, garantizando un servicio personalizado y eficiente que se adapta a las necesidades de nuestros clientes.</p>',
                'url_imagen'  => "{$img}/advisor_espirales.jpg",
                'Activo'      => 'S',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        if (!Schema::hasTable('web_nosotros_quienes_beneficio')) {
            Schema::create('web_nosotros_quienes_beneficio', function (Blueprint $table) {
                $table->increments('id');
                $table->string('texto', 500);
                $table->integer('orden')->default(0);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });
            $beneficios = [
                'Productos de alta calidad garantizada',
                'Servicio rápido y confiable',
                'Soluciones adaptadas a tus necesidades',
            ];
            foreach ($beneficios as $i => $texto) {
                DB::table('web_nosotros_quienes_beneficio')->insert([
                    'texto'      => $texto,
                    'orden'      => $i + 1,
                    'Activo'     => 'S',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('web_about_contador') && !Schema::hasColumn('web_about_contador', 'descripcion')) {
            Schema::table('web_about_contador', function (Blueprint $table) {
                $table->text('descripcion')->nullable()->after('etiqueta');
            });
        }

        if (Schema::hasTable('web_about_contador')) {
            $stats = [
                ['valor' => '10', 'etiqueta' => 'AÑOS', 'descripcion' => 'Más de cinco años brindando soluciones eficientes en el sector de la encuadernación y papelería especializada.', 'orden' => 1],
                ['valor' => '20', 'etiqueta' => 'PRODUCTOS DIVERSOS', 'descripcion' => 'Contamos con una amplia gama de productos para todas tus necesidades de impresión, laminación y encuadernación.', 'orden' => 2],
                ['valor' => '300', 'etiqueta' => 'CLIENTES', 'descripcion' => 'Hemos atendido a más de 300 clientes en todo el país, garantizando calidad y satisfacción en cada entrega.', 'orden' => 3],
            ];
            $aboutId = DB::table('web_about')->where('Activo', 'S')->value('id_about') ?? 1;
            DB::table('web_about_contador')->where('id_about', $aboutId)->delete();
            foreach ($stats as $s) {
                DB::table('web_about_contador')->insert(array_merge($s, [
                    'id_about'   => $aboutId,
                    'sufijo'     => '',
                    'Activo'     => 'S',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        if (!Schema::hasTable('web_nosotros_producto_seccion')) {
            Schema::create('web_nosotros_producto_seccion', function (Blueprint $table) {
                $table->increments('id');
                $table->string('seccion_titulo', 255)->nullable();
                $table->text('seccion_descripcion')->nullable();
                $table->timestamps();
            });
            DB::table('web_nosotros_producto_seccion')->insert([
                'id'                   => 1,
                'seccion_titulo'       => 'Algunos Productos Que Tenemos',
                'seccion_descripcion'  => 'En Liam Vercceli, ofrecemos una amplia gama de productos de alta calidad para todas tus necesidades de encuadernación, laminación y papelería.',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        if (!Schema::hasTable('web_nosotros_producto')) {
            Schema::create('web_nosotros_producto', function (Blueprint $table) {
                $table->increments('id');
                $table->string('titulo', 255);
                $table->text('descripcion')->nullable();
                $table->string('url_imagen', 500)->nullable();
                $table->string('btn_texto', 120)->default('Me interesa');
                $table->string('btn_url', 500)->nullable();
                $table->integer('orden')->default(0);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });
            $productos = [
                ['titulo' => 'Máquina de Encuadernación Bright Office No. B674', 'descripcion' => 'Encuadernadora profesional para oficina.', 'url_imagen' => "{$img}/advisor_espiraladoras.jpg", 'btn_url' => 'https://wa.me/51981629466', 'orden' => 1],
                ['titulo' => 'Máquina Manual Bright Office', 'descripcion' => 'Ideal para emprendedores y pequeños negocios.', 'url_imagen' => "{$img}/advisor_micas.jpg", 'btn_url' => 'https://wa.me/51981629466', 'orden' => 2],
                ['titulo' => 'Profesional Office Notebook', 'descripcion' => 'Encuadernación de cuadernos y agendas.', 'url_imagen' => "{$img}/jh_importaciones_img_1.jpg", 'btn_url' => 'https://wa.me/51981629466', 'orden' => 3],
            ];
            foreach ($productos as $p) {
                DB::table('web_nosotros_producto')->insert(array_merge($p, [
                    'btn_texto'  => 'Me interesa',
                    'Activo'     => 'S',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('web_nosotros_producto');
        Schema::dropIfExists('web_nosotros_producto_seccion');
        Schema::dropIfExists('web_nosotros_quienes_beneficio');
        Schema::dropIfExists('web_nosotros_quienes');
    }
};

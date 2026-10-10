<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_header', function (Blueprint $table) {
            $table->id('id_header');
            $table->string('url_logo', 400)->default('temp02/assets/img/inicio/impacto-gigante-logo-con-fondo.png');
            $table->json('nav_items')->nullable();
            $table->string('telefono_label', 100)->default('Llámanos:');
            $table->string('telefono_numero', 60)->default('939 691 536');
            $table->string('telefono_href', 120)->default('tel:939691536');
            $table->string('btn_texto', 100)->default('Cotizar Ahora');
            $table->enum('Activo', ['S', 'N'])->default('S');
            $table->timestamps();
        });

        DB::table('web_header')->insert([
            'url_logo'        => 'temp02/assets/img/inicio/impacto-gigante-logo-con-fondo.png',
            'nav_items'       => json_encode([
                ['label' => 'Inicio',    'href' => '#inicio'],
                ['label' => 'Nosotros',  'href' => '#about'],
                ['label' => 'Servicios', 'href' => '#services'],
                ['label' => 'Planes',    'href' => '#pricing'],
                ['label' => 'Contacto',  'href' => '#contact'],
            ]),
            'telefono_label'  => 'Llámanos:',
            'telefono_numero' => '939 691 536',
            'telefono_href'   => 'tel:939691536',
            'btn_texto'       => 'Cotizar Ahora',
            'Activo'          => 'S',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('web_header');
    }
};

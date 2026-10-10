<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_popup_promo', function (Blueprint $table) {
            $table->id('id_popup');
            $table->string('titulo', 120)->default('OBTÉN');
            $table->string('descuento_texto', 60)->default('10% OFF');
            $table->string('subtitulo', 120)->default('EN TU COMPRA');
            $table->string('url_imagen')->nullable();
            $table->string('btn_texto', 80)->default('¡Lo quiero!');
            $table->string('placeholder_email', 80)->default('Email');
            $table->string('placeholder_telefono', 80)->default('Número de teléfono');
            $table->boolean('mostrar_email')->default(true);
            $table->boolean('mostrar_telefono')->default(true);
            $table->string('activo', 1)->default('S');      // S | N
            $table->integer('delay_segundos')->default(3);  // segundos antes de mostrar
            $table->integer('cookie_dias')->default(7);     // días sin volver a mostrar
            $table->string('color_acento', 20)->default('#922b21');
            $table->timestamps();
        });

        // Registro inicial
        DB::table('web_popup_promo')->insert([
            'titulo'               => 'OBTÉN',
            'descuento_texto'      => '10% OFF',
            'subtitulo'            => 'EN TU PRIMERA COMPRA',
            'url_imagen'           => null,
            'btn_texto'            => '¡Lo quiero!',
            'placeholder_email'    => 'Email',
            'placeholder_telefono' => 'Número de teléfono',
            'mostrar_email'        => true,
            'mostrar_telefono'     => true,
            'activo'               => 'N',
            'delay_segundos'       => 3,
            'cookie_dias'          => 7,
            'color_acento'         => '#922b21',
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('web_popup_promo');
    }
};

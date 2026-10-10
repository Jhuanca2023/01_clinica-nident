<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('web_pagina_experiencias', function (Blueprint $table) {
            $table->id();
            // Hero
            $table->string('hero_tag',         120)->default('Royal Sensory Experience Massage');
            $table->string('hero_titulo',      200)->default('Nuestras Experiencias');
            $table->string('hero_url_imagen',  400)->nullable();
            // Stats
            $table->string('stat1_valor',  20)->default('16');
            $table->string('stat1_label',  80)->default('Experiencias únicas');
            $table->string('stat2_valor',  20)->default('4');
            $table->string('stat2_label',  80)->default('Categorías');
            $table->string('stat3_valor',  20)->default('100%');
            $table->string('stat3_label',  80)->default('Privado & Confidencial');
            $table->string('stat4_valor',  20)->default('Solo');
            $table->string('stat4_label',  80)->default('Para Mujeres');
            // Intro
            $table->string('intro_label',  120)->default('Elige tu Experiencia');
            $table->string('intro_titulo', 200)->default('Royal Sensory Experience');
            $table->string('intro_titulo2',200)->default('Diseñado para ti');
            // Categorías
            $table->string('cat_tantrico_titulo',120)->default('Tántricas & Sensoriales');
            $table->text('cat_tantrico_desc')        ->nullable();
            $table->string('cat_bienestar_titulo',120)->default('Bienestar Femenino');
            $table->text('cat_bienestar_desc')       ->nullable();
            $table->string('cat_corporal_titulo',120) ->default('Renovación Corporal');
            $table->text('cat_corporal_desc')        ->nullable();
            $table->string('cat_estetica_titulo',120) ->default('Modelación & Estética');
            $table->text('cat_estetica_desc')        ->nullable();
            // CTA
            $table->string('cta_titulo',200)->nullable();
            $table->string('cta_texto',  300)->nullable();
            $table->string('cta_btn',     80)->default('Reservar por WhatsApp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_pagina_experiencias');
    }
};

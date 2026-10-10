<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('web_popup_promo_historial')) {
            return;
        }

        Schema::create('web_popup_promo_historial', function (Blueprint $table) {
            $table->bigIncrements('id_historial');
            $table->unsignedBigInteger('id_popup')->default(1)->index();
            $table->string('titulo', 120)->nullable();
            $table->string('descuento_texto', 60)->nullable();
            $table->decimal('descuento_porcentaje', 5, 2)->nullable();
            $table->text('subtitulo')->nullable();
            $table->string('btn_texto', 80)->nullable();
            $table->string('url_imagen', 500)->nullable();
            $table->string('modo', 30)->nullable()->default('formulario');
            $table->string('color_acento', 20)->nullable();
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();
            $table->integer('delay_segundos')->nullable();
            $table->integer('cookie_dias')->nullable();
            $table->string('cookie_unidad', 20)->nullable();
            $table->string('whatsapp_numero', 40)->nullable();
            $table->text('whatsapp_mensaje')->nullable();
            $table->boolean('mostrar_email')->nullable();
            $table->boolean('mostrar_telefono')->nullable();
            $table->boolean('mostrar_opciones')->nullable();
            $table->string('opciones_label', 120)->nullable();
            $table->text('opciones')->nullable();
            $table->string('activo', 1)->nullable();
            $table->string('nota', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_popup_promo_historial');
    }
};

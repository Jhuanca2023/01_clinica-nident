<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('campus_proyecto_paquetes')) {
            return;
        }

        Schema::create('campus_proyecto_paquetes', function (Blueprint $table) {
            $table->id('id_paquete');
            $table->unsignedBigInteger('id_proyecto')->unique();
            $table->unsignedBigInteger('id_tipo_origen')->nullable()->comment('Referencia al catálogo al momento de la copia');
            $table->string('slug_origen', 40)->nullable();
            $table->string('titulo', 120);
            $table->string('icon', 60)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('subtexto_emocional', 500)->nullable();
            $table->string('frase_destacada', 500)->nullable();
            $table->string('badge_etiqueta', 120)->nullable();
            $table->string('texto_boton', 120)->nullable();
            $table->decimal('precio_base', 10, 2)->default(0);
            $table->string('moneda', 8)->default('S/');
            $table->string('dias_entrega', 120)->nullable();
            $table->text('nota_custom')->nullable();
            $table->json('includes')->nullable();
            $table->json('funcionalidades')->nullable();
            $table->char('requiere_modulos', 1)->default('N');
            $table->timestamp('copiado_en')->useCurrent();
            $table->timestamps();

            $table->foreign('id_proyecto')
                ->references('id_proyecto')
                ->on('campus_proyectos')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_proyecto_paquetes');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('web_slider') && !Schema::hasColumn('web_slider', 'tipo_slide')) {
            Schema::table('web_slider', function (Blueprint $table) {
                $table->string('tipo_slide', 40)->nullable()->after('slide_tag');
            });
        }

        if (!Schema::hasTable('web_ejemplares_seccion')) {
            Schema::create('web_ejemplares_seccion', function (Blueprint $table) {
                $table->increments('id');
                $table->string('slug', 60)->unique();
                $table->string('anchor_id', 80);
                $table->string('etiqueta', 120)->nullable();
                $table->string('titulo', 255)->nullable();
                $table->string('titulo_destacado', 120)->nullable();
                $table->text('subtitulo')->nullable();
                $table->unsignedSmallInteger('conteo_nav')->default(0);
                $table->integer('orden')->default(0);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('web_ejemplares')) {
            Schema::create('web_ejemplares', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('id_seccion');
                $table->string('slug', 120)->nullable();
                $table->string('nombre', 255)->nullable();
                $table->text('descripcion')->nullable();
                $table->string('url_imagen', 500)->nullable();
                $table->string('numero_badge', 10)->nullable();
                $table->string('overlay_texto', 255)->nullable();
                $table->string('url_demo', 500)->nullable();
                $table->string('mininav_json', 500)->nullable();
                $table->integer('orden')->default(0);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
                $table->index(['id_seccion', 'Activo', 'orden']);
            });
        }

        if (!Schema::hasTable('web_portafolio_seccion')) {
            Schema::create('web_portafolio_seccion', function (Blueprint $table) {
                $table->id('id');
                $table->string('etiqueta', 255)->nullable();
                $table->string('titulo', 500)->nullable();
                $table->text('subtitulo')->nullable();
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('web_portafolio')) {
            Schema::create('web_portafolio', function (Blueprint $table) {
                $table->increments('id');
                $table->string('categoria', 40);
                $table->string('tipo', 60)->nullable();
                $table->string('titulo', 500)->nullable();
                $table->text('descripcion')->nullable();
                $table->string('tecnologias', 500)->nullable();
                $table->string('url_proyecto', 500)->nullable();
                $table->string('texto_enlace', 80)->nullable()->default('Sitio web');
                $table->string('url_imagen', 500)->nullable();
                $table->integer('orden')->default(0);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
                $table->index(['categoria', 'Activo', 'orden']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('web_portafolio');
        Schema::dropIfExists('web_portafolio_seccion');
        Schema::dropIfExists('web_ejemplares');
        Schema::dropIfExists('web_ejemplares_seccion');

        if (Schema::hasTable('web_slider') && Schema::hasColumn('web_slider', 'tipo_slide')) {
            Schema::table('web_slider', function (Blueprint $table) {
                $table->dropColumn('tipo_slide');
            });
        }
    }
};

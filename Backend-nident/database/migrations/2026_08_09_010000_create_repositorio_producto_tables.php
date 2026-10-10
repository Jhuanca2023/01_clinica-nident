<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Repositorio de compra: copia inmutable del producto al momento del pedido.
 * Las consultas de pedido deben leer estas tablas, no el catálogo vivo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('repositorio_producto')) {
            Schema::create('repositorio_producto', function (Blueprint $table) {
                $table->bigIncrements('id_repositorio_producto');
                $table->unsignedBigInteger('id_pedido')->index();
                $table->unsignedBigInteger('id_pedido_producto')->unique();
                $table->unsignedBigInteger('id_producto_origen')->nullable()->index();

                $table->string('codigo_producto', 80)->nullable();
                $table->string('codigo_producto_new', 80)->nullable();
                $table->string('codigo_barra', 80)->nullable();
                $table->string('etiqueta_badge', 40)->nullable();
                $table->string('nombre', 500)->nullable();
                $table->string('slug', 255)->nullable();
                $table->longText('descripcion')->nullable();
                $table->string('ubicacion', 255)->nullable();

                $table->decimal('precio', 12, 2)->nullable();
                $table->decimal('precio_old', 12, 2)->nullable();
                $table->decimal('precio_costo', 12, 2)->nullable();
                $table->decimal('precio_mayorista', 12, 2)->nullable();
                $table->decimal('precio_mayorista_old', 12, 2)->nullable();
                $table->decimal('precio_yape', 12, 2)->nullable();

                $table->integer('stock')->nullable();
                $table->integer('stock_minimo')->nullable();

                $table->unsignedBigInteger('id_producto_categoria')->nullable();
                $table->unsignedBigInteger('id_producto_categoria_sub')->nullable();
                $table->string('nombre_categoria', 255)->nullable();
                $table->string('nombre_subcategoria', 255)->nullable();
                $table->unsignedBigInteger('id_producto_tipo')->nullable();

                $table->string('url_imagen', 500)->nullable();
                $table->string('color', 40)->nullable();
                $table->string('tallas_disponibles', 255)->nullable();
                $table->longText('tallas_variantes')->nullable();
                $table->longText('colores_disponibles')->nullable();

                $table->decimal('peso_kilogramo', 12, 3)->nullable();
                $table->string('paquete_medidas', 255)->nullable();
                $table->string('paquete_dimencion', 255)->nullable();
                $table->string('meta_titulo_producto', 255)->nullable();
                $table->text('meta_descripcion_producto')->nullable();
                $table->decimal('numero_estrellas', 4, 2)->nullable();
                $table->string('Activo', 5)->nullable();

                // Datos de la línea comprada
                $table->string('talla_pedida', 40)->nullable();
                $table->string('color_pedido', 80)->nullable();
                $table->integer('cantidad_pedida')->nullable();
                $table->decimal('precio_unitario_pedido', 12, 2)->nullable();
                $table->string('pro_nombre_linea', 500)->nullable();

                $table->longText('snapshot_json')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('repositorio_producto_fotos')) {
            Schema::create('repositorio_producto_fotos', function (Blueprint $table) {
                $table->bigIncrements('id_repositorio_foto');
                $table->unsignedBigInteger('id_repositorio_producto')->index();
                $table->unsignedBigInteger('id_producto_foto_origen')->nullable();
                $table->string('url_imagen', 500)->nullable();
                $table->integer('orden')->nullable()->default(0);
                $table->string('titulo', 255)->nullable();
                $table->text('descripcion')->nullable();
                $table->decimal('precio', 12, 2)->nullable();
                $table->decimal('precio_old', 12, 2)->nullable();
                $table->integer('stock')->nullable();
                $table->string('codigo_producto', 80)->nullable();
                $table->decimal('numero_estrellas', 4, 2)->nullable();
                $table->string('color', 40)->nullable();
                $table->string('Activo', 5)->nullable();
                $table->timestamps();

                $table->foreign('id_repositorio_producto', 'fk_repo_fotos_producto')
                    ->references('id_repositorio_producto')
                    ->on('repositorio_producto')
                    ->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('repositorio_producto_ficha_tecnica')) {
            Schema::create('repositorio_producto_ficha_tecnica', function (Blueprint $table) {
                $table->bigIncrements('id_repositorio_ficha');
                $table->unsignedBigInteger('id_repositorio_producto')->index();
                $table->unsignedBigInteger('id_ficha_origen')->nullable();
                $table->integer('orden')->nullable()->default(0);
                $table->string('tipo', 20)->nullable()->default('tabla');
                $table->boolean('es_principal')->nullable()->default(false);
                $table->string('titulo', 255)->nullable();
                $table->longText('descripcion')->nullable();
                $table->longText('nuevo_caracteristicas')->nullable();
                $table->string('nuevo_titulo', 255)->nullable();
                $table->longText('nuevo_descripcion')->nullable();
                $table->string('Activo', 5)->nullable();
                $table->timestamps();

                $table->foreign('id_repositorio_producto', 'fk_repo_ficha_producto')
                    ->references('id_repositorio_producto')
                    ->on('repositorio_producto')
                    ->onDelete('cascade');
            });
        }

        if (Schema::hasTable('ope_pedidos_producto')
            && ! Schema::hasColumn('ope_pedidos_producto', 'id_repositorio_producto')) {
            Schema::table('ope_pedidos_producto', function (Blueprint $table) {
                $table->unsignedBigInteger('id_repositorio_producto')->nullable()->after('id_producto')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ope_pedidos_producto')
            && Schema::hasColumn('ope_pedidos_producto', 'id_repositorio_producto')) {
            Schema::table('ope_pedidos_producto', function (Blueprint $table) {
                $table->dropColumn('id_repositorio_producto');
            });
        }

        Schema::dropIfExists('repositorio_producto_ficha_tecnica');
        Schema::dropIfExists('repositorio_producto_fotos');
        Schema::dropIfExists('repositorio_producto');
    }
};

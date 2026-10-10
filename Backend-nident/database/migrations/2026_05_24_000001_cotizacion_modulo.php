<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cotizacion_config')) {
            Schema::create('cotizacion_config', function (Blueprint $table) {
                $table->id();
                $table->string('moneda', 8)->default('S/');
                $table->decimal('descuento_hosting', 10, 2)->default(50);
                $table->decimal('renovacion_dominio', 10, 2)->default(130);
                $table->decimal('soporte_hora', 10, 2)->default(30);
                $table->json('promos')->nullable();
                $table->string('Activo', 1)->default('S');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cotizacion_tipo_proyecto')) {
            Schema::create('cotizacion_tipo_proyecto', function (Blueprint $table) {
                $table->id('id_tipo');
                $table->string('slug', 40)->unique();
                $table->string('titulo', 120);
                $table->string('icon', 60)->nullable();
                $table->text('descripcion')->nullable();
                $table->decimal('precio_base', 10, 2)->default(0);
                $table->string('dias_entrega', 120)->nullable();
                $table->text('nota_custom')->nullable();
                $table->json('includes')->nullable();
                $table->unsignedInteger('orden')->default(0);
                $table->char('requiere_modulos', 1)->default('N');
                $table->string('Activo', 1)->default('S');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cotizacion_modulo')) {
            Schema::create('cotizacion_modulo', function (Blueprint $table) {
                $table->id('id_modulo');
                $table->string('slug', 60)->unique();
                $table->string('nombre', 160);
                $table->decimal('precio', 10, 2)->default(0);
                $table->string('icon', 60)->nullable();
                $table->string('descripcion', 255)->nullable();
                $table->unsignedInteger('orden')->default(0);
                $table->string('Activo', 1)->default('S');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cotizacion_presupuesto')) {
            Schema::create('cotizacion_presupuesto', function (Blueprint $table) {
                $table->id('id_presupuesto');
                $table->string('codigo', 32)->unique();
                $table->unsignedBigInteger('id_tipo')->nullable();
                $table->string('tipo_titulo', 160)->nullable();
                $table->string('cliente_nombre', 160);
                $table->string('cliente_empresa', 160)->nullable();
                $table->string('cliente_whatsapp', 40)->nullable();
                $table->string('cliente_email', 160)->nullable();
                $table->text('cliente_descripcion')->nullable();
                $table->char('tiene_hosting', 1)->default('N');
                $table->decimal('descuento_monto', 10, 2)->default(0);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);
                $table->string('dias_entrega', 120)->nullable();
                $table->string('estado', 20)->default('enviada');
                $table->string('origen', 20)->default('web');
                $table->json('canvas_layout')->nullable();
                $table->unsignedBigInteger('id_usuario')->nullable();
                $table->unsignedBigInteger('id_roles')->nullable();
                $table->string('Activo', 1)->default('S');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cotizacion_presupuesto_detalle')) {
            Schema::create('cotizacion_presupuesto_detalle', function (Blueprint $table) {
                $table->id('id_detalle');
                $table->unsignedBigInteger('id_presupuesto');
                $table->string('tipo_linea', 30);
                $table->string('ref_slug', 60)->nullable();
                $table->string('etiqueta', 200);
                $table->decimal('precio', 12, 2)->default(0);
                $table->unsignedInteger('orden')->default(0);
                $table->timestamps();
                $table->index('id_presupuesto');
            });
        }

        $this->seedMenuAdmin();
    }

    private function seedMenuAdmin(): void
    {
        if (!Schema::hasTable('sistema_menu')) {
            return;
        }

        $url = '/cotizacion/index';
        if (DB::table('sistema_menu')->where('url', $url)->exists()) {
            return;
        }

        $idModulo = null;
        if (Schema::hasTable('sistema_modulo')) {
            $idModulo = DB::table('sistema_modulo')
                ->where(function ($q) {
                    $q->where('nombre', 'like', '%Paginas%')
                        ->orWhere('nombre', 'like', '%Páginas%')
                        ->orWhere('nombre', 'like', '%Web%');
                })
                ->value('id_modulo');
        }

        $maxOrden = (int) DB::table('sistema_menu')->max('orden');
        $data = [
            'nombre'     => 'Cotizador Web',
            'url'        => $url,
            'Activo'     => 'S',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('sistema_menu', 'id_modulo')) {
            $data['id_modulo'] = $idModulo;
        }
        if (Schema::hasColumn('sistema_menu', 'Icon')) {
            $data['Icon'] = 'calculate';
        }
        if (Schema::hasColumn('sistema_menu', 'orden')) {
            $data['orden'] = $maxOrden + 1;
        }
        if (Schema::hasColumn('sistema_menu', 'id_menu_padre')) {
            $data['id_menu_padre'] = null;
        }

        $idMenu = DB::table('sistema_menu')->insertGetId($data);

        if (Schema::hasTable('seguridad_roles_menu')) {
            foreach (DB::table('seguridad_roles')->pluck('id_roles') as $idRoles) {
                if (!DB::table('seguridad_roles_menu')->where('id_roles', $idRoles)->where('id_menu', $idMenu)->exists()) {
                    DB::table('seguridad_roles_menu')->insert([
                        'id_roles' => $idRoles,
                        'id_menu'  => $idMenu,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizacion_presupuesto_detalle');
        Schema::dropIfExists('cotizacion_presupuesto');
        Schema::dropIfExists('cotizacion_modulo');
        Schema::dropIfExists('cotizacion_tipo_proyecto');
        Schema::dropIfExists('cotizacion_config');

        if (Schema::hasTable('sistema_menu')) {
            $ids = DB::table('sistema_menu')->where('url', '/cotizacion/index')->pluck('id_menu');
            foreach ($ids as $idMenu) {
                if (Schema::hasTable('seguridad_roles_menu')) {
                    DB::table('seguridad_roles_menu')->where('id_menu', $idMenu)->delete();
                }
                DB::table('sistema_menu')->where('id_menu', $idMenu)->delete();
            }
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notificacion_config')) {
            Schema::create('notificacion_config', function (Blueprint $table) {
                $table->id('id_config');
                $table->unsignedSmallInteger('intervalo_minutos')->default(10);
                $table->boolean('activo')->default(true);
                $table->boolean('toast_navegador')->default(true);
                $table->boolean('notificacion_sistema')->default(true);
                $table->unsignedTinyInteger('dias_anticipacion_alerta')->default(14);
                $table->timestamp('ultima_evaluacion_at')->nullable();
                $table->timestamps();
            });

            DB::table('notificacion_config')->insert([
                'intervalo_minutos'       => 10,
                'activo'                  => true,
                'toast_navegador'         => true,
                'notificacion_sistema'    => true,
                'dias_anticipacion_alerta'=> 14,
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);
        }

        if (!Schema::hasTable('notificacion_estado')) {
            Schema::create('notificacion_estado', function (Blueprint $table) {
                $table->id('id_estado');
                $table->string('slug', 40)->unique();
                $table->string('nombre', 80);
                $table->string('color_hex', 20)->default('#64748b');
                $table->enum('aplica_a', ['cliente', 'proyecto', 'ambos'])->default('ambos');
                $table->boolean('es_sistema')->default(false);
                $table->unsignedSmallInteger('orden')->default(0);
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });

            $now = now();
            $estados = [
                ['slug' => 'activo',     'nombre' => 'Activo',     'color_hex' => '#22c55e', 'orden' => 1],
                ['slug' => 'inactivo',   'nombre' => 'Inactivo',   'color_hex' => '#94a3b8', 'orden' => 2],
                ['slug' => 'pausado',    'nombre' => 'Pausado',    'color_hex' => '#f59e0b', 'orden' => 3],
                ['slug' => 'entregado',  'nombre' => 'Entregado',  'color_hex' => '#3b82f6', 'orden' => 4],
                ['slug' => 'observado',  'nombre' => 'Observado',  'color_hex' => '#ef4444', 'orden' => 5],
            ];
            foreach ($estados as $e) {
                DB::table('notificacion_estado')->insert(array_merge($e, [
                    'aplica_a'    => 'ambos',
                    'es_sistema'  => true,
                    'activo'      => true,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]));
            }
        }

        if (!Schema::hasTable('notificacion_semaforo')) {
            Schema::create('notificacion_semaforo', function (Blueprint $table) {
                $table->id('id_semaforo');
                $table->string('slug', 40)->unique();
                $table->string('nombre', 80);
                $table->string('color_hex', 20);
                $table->integer('dias_restantes_min')->nullable();
                $table->integer('dias_restantes_max')->nullable();
                $table->unsignedSmallInteger('orden')->default(0);
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });

            $now = now();
            $semaforos = [
                ['slug' => 'vencido',    'nombre' => 'Vencido',    'color_hex' => '#dc2626', 'dias_restantes_min' => null, 'dias_restantes_max' => -1, 'orden' => 1],
                ['slug' => 'hoy',        'nombre' => 'Vence hoy',  'color_hex' => '#ea580c', 'dias_restantes_min' => 0,  'dias_restantes_max' => 0,  'orden' => 2],
                ['slug' => 'critico',    'nombre' => 'Crítico',    'color_hex' => '#f97316', 'dias_restantes_min' => 1,  'dias_restantes_max' => 2,  'orden' => 3],
                ['slug' => 'urgente',    'nombre' => 'Urgente',    'color_hex' => '#eab308', 'dias_restantes_min' => 3,  'dias_restantes_max' => 5,  'orden' => 4],
                ['slug' => 'atencion',   'nombre' => 'Atención',   'color_hex' => '#3b82f6', 'dias_restantes_min' => 6,  'dias_restantes_max' => 10, 'orden' => 5],
                ['slug' => 'a_tiempo',   'nombre' => 'A tiempo',   'color_hex' => '#22c55e', 'dias_restantes_min' => 11, 'dias_restantes_max' => null, 'orden' => 6],
                ['slug' => 'sin_fecha',  'nombre' => 'Sin fecha',  'color_hex' => '#94a3b8', 'dias_restantes_min' => null, 'dias_restantes_max' => null, 'orden' => 99],
            ];
            foreach ($semaforos as $s) {
                DB::table('notificacion_semaforo')->insert(array_merge($s, [
                    'activo'     => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        if (!Schema::hasTable('notificacion_alerta')) {
            Schema::create('notificacion_alerta', function (Blueprint $table) {
                $table->id('id_alerta');
                $table->unsignedBigInteger('id_user');
                $table->unsignedBigInteger('id_proyecto')->nullable();
                $table->unsignedBigInteger('id_cliente')->nullable();
                $table->unsignedBigInteger('id_semaforo')->nullable();
                $table->string('tipo', 30)->default('deadline');
                $table->string('titulo', 200);
                $table->text('mensaje');
                $table->unsignedTinyInteger('prioridad')->default(3);
                $table->boolean('leida')->default(false);
                $table->boolean('descartada')->default(false);
                $table->timestamp('mostrada_at')->nullable();
                $table->timestamps();

                $table->index(['id_user', 'descartada', 'leida']);
                $table->index(['id_proyecto', 'created_at']);
                $table->foreign('id_user')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('id_proyecto')->references('id_proyecto')->on('campus_proyectos')->nullOnDelete();
                $table->foreign('id_cliente')->references('id_cliente')->on('campus_clientes')->nullOnDelete();
                $table->foreign('id_semaforo')->references('id_semaforo')->on('notificacion_semaforo')->nullOnDelete();
            });
        }

        if (Schema::hasTable('campus_clientes') && !Schema::hasColumn('campus_clientes', 'id_notificacion_estado')) {
            Schema::table('campus_clientes', function (Blueprint $table) {
                $table->unsignedBigInteger('id_notificacion_estado')->nullable()->after('estado');
            });
            $activoId = DB::table('notificacion_estado')->where('slug', 'activo')->value('id_estado');
            if ($activoId) {
                DB::table('campus_clientes')->whereNull('id_notificacion_estado')->update(['id_notificacion_estado' => $activoId]);
            }
        }

        if (Schema::hasTable('campus_proyectos') && !Schema::hasColumn('campus_proyectos', 'id_notificacion_estado')) {
            Schema::table('campus_proyectos', function (Blueprint $table) {
                $table->unsignedBigInteger('id_notificacion_estado')->nullable()->after('estado');
            });
            $activoId = DB::table('notificacion_estado')->where('slug', 'activo')->value('id_estado');
            if ($activoId) {
                DB::table('campus_proyectos')->whereNull('id_notificacion_estado')->update(['id_notificacion_estado' => $activoId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('campus_proyectos') && Schema::hasColumn('campus_proyectos', 'id_notificacion_estado')) {
            Schema::table('campus_proyectos', fn (Blueprint $t) => $t->dropColumn('id_notificacion_estado'));
        }
        if (Schema::hasTable('campus_clientes') && Schema::hasColumn('campus_clientes', 'id_notificacion_estado')) {
            Schema::table('campus_clientes', fn (Blueprint $t) => $t->dropColumn('id_notificacion_estado'));
        }
        Schema::dropIfExists('notificacion_alerta');
        Schema::dropIfExists('notificacion_semaforo');
        Schema::dropIfExists('notificacion_estado');
        Schema::dropIfExists('notificacion_config');
    }
};

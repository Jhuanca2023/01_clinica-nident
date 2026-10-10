<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('campus_proyectos') && !Schema::hasColumn('campus_proyectos', 'orden_prioridad')) {
            Schema::table('campus_proyectos', function (Blueprint $table) {
                $table->unsignedTinyInteger('orden_prioridad')->default(5)->after('progreso');
            });
        }

        if (Schema::hasTable('notificacion_config')) {
            Schema::table('notificacion_config', function (Blueprint $table) {
                if (!Schema::hasColumn('notificacion_config', 'intervalo_proximo_rojo_min')) {
                    $table->unsignedSmallInteger('intervalo_proximo_rojo_min')->default(30)->after('intervalo_minutos');
                }
                if (!Schema::hasColumn('notificacion_config', 'intervalo_proximo_amarillo_min')) {
                    $table->unsignedSmallInteger('intervalo_proximo_amarillo_min')->default(120)->after('intervalo_proximo_rojo_min');
                }
                if (!Schema::hasColumn('notificacion_config', 'ultima_eval_proximo_rojo_at')) {
                    $table->timestamp('ultima_eval_proximo_rojo_at')->nullable()->after('ultima_evaluacion_at');
                }
                if (!Schema::hasColumn('notificacion_config', 'ultima_eval_proximo_amarillo_at')) {
                    $table->timestamp('ultima_eval_proximo_amarillo_at')->nullable()->after('ultima_eval_proximo_rojo_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('campus_proyectos') && Schema::hasColumn('campus_proyectos', 'orden_prioridad')) {
            Schema::table('campus_proyectos', function (Blueprint $table) {
                $table->dropColumn('orden_prioridad');
            });
        }

        if (Schema::hasTable('notificacion_config')) {
            Schema::table('notificacion_config', function (Blueprint $table) {
                foreach ([
                    'intervalo_proximo_rojo_min',
                    'intervalo_proximo_amarillo_min',
                    'ultima_eval_proximo_rojo_at',
                    'ultima_eval_proximo_amarillo_at',
                ] as $col) {
                    if (Schema::hasColumn('notificacion_config', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};

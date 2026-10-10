<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cotizacion_config') && !Schema::hasColumn('cotizacion_config', 'dias_vigencia_qr')) {
            Schema::table('cotizacion_config', function (Blueprint $table) {
                $table->unsignedSmallInteger('dias_vigencia_qr')->default(5)->after('soporte_hora');
            });
            DB::table('cotizacion_config')->update(['dias_vigencia_qr' => 5]);
        }

        if (Schema::hasTable('cotizacion_presupuesto')) {
            Schema::table('cotizacion_presupuesto', function (Blueprint $table) {
                if (!Schema::hasColumn('cotizacion_presupuesto', 'qr_token')) {
                    $table->string('qr_token', 64)->nullable()->unique()->after('codigo');
                }
                if (!Schema::hasColumn('cotizacion_presupuesto', 'qr_estado')) {
                    $table->string('qr_estado', 20)->default('vigente')->after('qr_token');
                }
                if (!Schema::hasColumn('cotizacion_presupuesto', 'vigencia_inicio')) {
                    $table->dateTime('vigencia_inicio')->nullable()->after('qr_estado');
                }
                if (!Schema::hasColumn('cotizacion_presupuesto', 'vigencia_fin')) {
                    $table->dateTime('vigencia_fin')->nullable()->after('vigencia_inicio');
                }
                if (!Schema::hasColumn('cotizacion_presupuesto', 'dias_vigencia')) {
                    $table->unsignedSmallInteger('dias_vigencia')->nullable()->after('vigencia_fin');
                }
                if (!Schema::hasColumn('cotizacion_presupuesto', 'qr_escaneos')) {
                    $table->unsignedInteger('qr_escaneos')->default(0)->after('dias_vigencia');
                }
                if (!Schema::hasColumn('cotizacion_presupuesto', 'qr_ultimo_escaneo_at')) {
                    $table->dateTime('qr_ultimo_escaneo_at')->nullable()->after('qr_escaneos');
                }
            });
        }

        if (!Schema::hasTable('cotizacion_presupuesto_qr_log')) {
            Schema::create('cotizacion_presupuesto_qr_log', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_presupuesto');
                $table->string('accion', 30);
                $table->unsignedBigInteger('id_usuario')->nullable();
                $table->string('ip', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->text('nota')->nullable();
                $table->timestamps();
                $table->index(['id_presupuesto', 'created_at']);
            });
        }

        $this->backfillQrTokens();
    }

    private function backfillQrTokens(): void
    {
        if (!Schema::hasTable('cotizacion_presupuesto')) {
            return;
        }

        $diasDefault = 5;
        if (Schema::hasColumn('cotizacion_config', 'dias_vigencia_qr')) {
            $diasDefault = (int) (DB::table('cotizacion_config')->value('dias_vigencia_qr') ?? 5) ?: 5;
        }

        $rows = DB::table('cotizacion_presupuesto')
            ->where(function ($q) {
                $q->whereNull('qr_token')->orWhere('qr_token', '');
            })
            ->get(['id_presupuesto', 'created_at']);

        foreach ($rows as $row) {
            $inicio = $row->created_at ? \Carbon\Carbon::parse($row->created_at) : now();
            $fin = $inicio->copy()->addDays($diasDefault);
            $estado = $fin->isPast() ? 'vencido' : 'vigente';

            DB::table('cotizacion_presupuesto')->where('id_presupuesto', $row->id_presupuesto)->update([
                'qr_token'        => Str::random(48),
                'qr_estado'       => $estado,
                'vigencia_inicio' => $inicio,
                'vigencia_fin'    => $fin,
                'dias_vigencia'   => $diasDefault,
                'updated_at'      => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizacion_presupuesto_qr_log');

        if (Schema::hasTable('cotizacion_presupuesto')) {
            Schema::table('cotizacion_presupuesto', function (Blueprint $table) {
                foreach (['qr_ultimo_escaneo_at', 'qr_escaneos', 'dias_vigencia', 'vigencia_fin', 'vigencia_inicio', 'qr_estado', 'qr_token'] as $col) {
                    if (Schema::hasColumn('cotizacion_presupuesto', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('cotizacion_config') && Schema::hasColumn('cotizacion_config', 'dias_vigencia_qr')) {
            Schema::table('cotizacion_config', function (Blueprint $table) {
                $table->dropColumn('dias_vigencia_qr');
            });
        }
    }
};

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
            return;
        }

        if (!Schema::hasColumn('cotizacion_config', 'info_bloques')) {
            Schema::table('cotizacion_config', function (Blueprint $table) {
                $table->json('info_bloques')->nullable()->after('promos');
            });
        }

        $default = json_encode($this->defaultBloques(), JSON_UNESCAPED_UNICODE);
        DB::table('cotizacion_config')->whereNull('info_bloques')->update(['info_bloques' => $default]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('cotizacion_config', 'info_bloques')) {
            Schema::table('cotizacion_config', function (Blueprint $table) {
                $table->dropColumn('info_bloques');
            });
        }
    }

    private function defaultBloques(): array
    {
        return [
            [
                'id' => 'promo',
                'variant' => 'promo',
                'titulo' => 'Todos los proyectos incluyen',
                'icon' => 'bi-gift',
                'items' => [
                    'Hosting gratis 1 año',
                    'Dominio gratis 1 año',
                    'SSL gratis',
                    '10 correos corporativos',
                    'Soporte técnico',
                ],
            ],
            [
                'id' => 'renovacion',
                'variant' => 'default',
                'titulo' => 'Renovación anual',
                'icon' => 'bi-arrow-repeat',
                'items' => ['Dominio .com: S/ {renovacion_dominio} / año'],
            ],
            [
                'id' => 'soporte',
                'variant' => 'default',
                'titulo' => 'Soporte y mantenimiento',
                'icon' => 'bi-tools',
                'items' => [
                    'Soporte técnico dentro del desarrollo',
                    'Sistemas de terceros: S/ {soporte_hora} / hora',
                ],
            ],
        ];
    }
};

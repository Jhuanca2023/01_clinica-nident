<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notificacion_alerta') && !Schema::hasColumn('notificacion_alerta', 'descartada_at')) {
            Schema::table('notificacion_alerta', function (Blueprint $table) {
                $table->timestamp('descartada_at')->nullable()->after('descartada');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('notificacion_alerta') && Schema::hasColumn('notificacion_alerta', 'descartada_at')) {
            Schema::table('notificacion_alerta', function (Blueprint $table) {
                $table->dropColumn('descartada_at');
            });
        }
    }
};

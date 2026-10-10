<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'es_administrador_principal')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('es_administrador_principal')->default(false)->after('Activo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'es_administrador_principal')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('es_administrador_principal');
            });
        }
    }
};

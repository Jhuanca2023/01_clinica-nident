<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seguridad_roles') && !Schema::hasColumn('seguridad_roles', 'requiere_2fa')) {
            Schema::table('seguridad_roles', function (Blueprint $table) {
                $table->boolean('requiere_2fa')->default(false)->after('Activo');
            });
        }

        if (Schema::hasTable('seguridad_auth_2fa_challenge') && !Schema::hasColumn('seguridad_auth_2fa_challenge', 'tipo')) {
            Schema::table('seguridad_auth_2fa_challenge', function (Blueprint $table) {
                $table->string('tipo', 24)->default('verify')->after('challenge_token');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('seguridad_roles') && Schema::hasColumn('seguridad_roles', 'requiere_2fa')) {
            Schema::table('seguridad_roles', function (Blueprint $table) {
                $table->dropColumn('requiere_2fa');
            });
        }

        if (Schema::hasTable('seguridad_auth_2fa_challenge') && Schema::hasColumn('seguridad_auth_2fa_challenge', 'tipo')) {
            Schema::table('seguridad_auth_2fa_challenge', function (Blueprint $table) {
                $table->dropColumn('tipo');
            });
        }
    }
};

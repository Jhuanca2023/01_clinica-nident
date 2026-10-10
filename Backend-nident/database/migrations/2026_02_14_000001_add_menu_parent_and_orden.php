<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sistema_menu', function (Blueprint $table) {
            if (!Schema::hasColumn('sistema_menu', 'id_menu_padre')) {
                $table->unsignedBigInteger('id_menu_padre')->nullable()->after('id_modulo');
            }
            if (!Schema::hasColumn('sistema_menu', 'orden')) {
                $table->unsignedInteger('orden')->default(0)->after('Activo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sistema_menu', function (Blueprint $table) {
            $table->dropColumn(['id_menu_padre', 'orden']);
        });
    }
};

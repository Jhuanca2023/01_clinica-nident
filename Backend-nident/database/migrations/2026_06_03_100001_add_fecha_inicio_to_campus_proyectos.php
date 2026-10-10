<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('campus_proyectos')) {
            return;
        }

        Schema::table('campus_proyectos', function (Blueprint $table) {
            if (!Schema::hasColumn('campus_proyectos', 'fecha_inicio')) {
                $table->date('fecha_inicio')->nullable()->after('estado');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('campus_proyectos', 'fecha_inicio')) {
            Schema::table('campus_proyectos', function (Blueprint $table) {
                $table->dropColumn('fecha_inicio');
            });
        }
    }
};

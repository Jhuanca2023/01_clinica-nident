<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('administracion_producto', function (Blueprint $table) {
            if (!Schema::hasColumn('administracion_producto', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('nombre');
            }
        });
    }

    public function down(): void
    {
        Schema::table('administracion_producto', function (Blueprint $table) {
            if (Schema::hasColumn('administracion_producto', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};

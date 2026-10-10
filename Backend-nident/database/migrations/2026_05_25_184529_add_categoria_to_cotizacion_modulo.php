<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cotizacion_modulo', function (Blueprint $table) {
            $table->string('categoria', 120)->nullable()->default(null)->after('descripcion');
        });
    }

    public function down(): void
    {
        Schema::table('cotizacion_modulo', function (Blueprint $table) {
            $table->dropColumn('categoria');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_porque_elejirnos', function (Blueprint $table) {
            $table->string('url_imagen_izquierda', 500)->nullable()->after('descripcion');
            $table->string('url_imagen_centro', 500)->nullable()->after('url_imagen_izquierda');
        });
    }

    public function down(): void
    {
        Schema::table('web_porque_elejirnos', function (Blueprint $table) {
            $table->dropColumn(['url_imagen_izquierda', 'url_imagen_centro']);
        });
    }
};

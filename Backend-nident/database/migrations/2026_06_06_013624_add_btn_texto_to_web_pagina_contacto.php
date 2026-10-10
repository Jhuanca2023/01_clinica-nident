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
        Schema::table('web_pagina_contacto', function (Blueprint $table) {
            $table->string('btn_texto', 200)->nullable()->after('form_descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_pagina_contacto', function (Blueprint $table) {
            $table->dropColumn('btn_texto');
        });
    }
};

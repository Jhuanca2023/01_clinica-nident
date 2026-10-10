<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['web_pagina_videos', 'web_pagina_maquinarias', 'web_pagina_contacto'] as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'frase_texto')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->text('frase_texto')->nullable()->after('banner_url_imagen');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['web_pagina_videos', 'web_pagina_maquinarias', 'web_pagina_contacto'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'frase_texto')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('frase_texto');
                });
            }
        }
    }
};

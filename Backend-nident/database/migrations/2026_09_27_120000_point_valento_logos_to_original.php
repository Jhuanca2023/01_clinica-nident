<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Apunta header y footer al logo original (V dorada sobre azul marino).
 * Los archivos subidos en storage_/header y storage_/footer eran otra marca.
 */
return new class extends Migration
{
    private const LOGO = 'imagenes/logo-principal.png';

    public function up(): void
    {
        if (Schema::hasTable('web_header') && Schema::hasColumn('web_header', 'url_logo')) {
            DB::table('web_header')->update(['url_logo' => self::LOGO]);
        }

        if (! Schema::hasTable('web_footer')) {
            return;
        }

        $footer = [];
        if (Schema::hasColumn('web_footer', 'logo_footer')) {
            $footer['logo_footer'] = self::LOGO;
        }
        if (Schema::hasColumn('web_footer', 'logo_menu')) {
            $footer['logo_menu'] = self::LOGO;
        }
        if (Schema::hasColumn('web_footer', 'url_logo_footer')) {
            $footer['url_logo_footer'] = self::LOGO;
        }
        if (Schema::hasColumn('web_footer', 'url_logo')) {
            $footer['url_logo'] = self::LOGO;
        }
        if ($footer !== []) {
            DB::table('web_footer')->update($footer);
        }
    }

    public function down(): void
    {
        // Los archivos anteriores no están en el repo; no hay ruta segura a la que volver.
    }
};

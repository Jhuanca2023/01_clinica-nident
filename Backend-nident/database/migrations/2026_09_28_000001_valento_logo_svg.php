<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Logo oficial de Valento en SVG (logo/valento.svg con el texto convertido a trazos)
 * para header, menú y footer.
 */
return new class extends Migration
{
    private const LOGO = 'temp02/img/inicio/logo-valento.svg';

    public function up(): void
    {
        if (Schema::hasTable('web_header') && Schema::hasColumn('web_header', 'url_logo')) {
            DB::table('web_header')->update(['url_logo' => self::LOGO]);
        }

        if (Schema::hasTable('web_footer')) {
            foreach (['logo_footer', 'logo_menu', 'url_logo_footer'] as $col) {
                if (Schema::hasColumn('web_footer', $col)) {
                    DB::table('web_footer')->update([$col => self::LOGO]);
                }
            }
        }
    }

    public function down(): void
    {
        //
    }
};

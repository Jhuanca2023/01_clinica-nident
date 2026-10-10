<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_popup_promo') || ! Schema::hasColumn('web_popup_promo', 'subtitulo')) {
            return;
        }

        // Ampliar descripción: varchar(120) → TEXT (sin doctrine/dbal)
        DB::statement('ALTER TABLE web_popup_promo MODIFY subtitulo TEXT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('web_popup_promo') || ! Schema::hasColumn('web_popup_promo', 'subtitulo')) {
            return;
        }

        DB::statement("ALTER TABLE web_popup_promo MODIFY subtitulo VARCHAR(120) NULL DEFAULT 'EN TU COMPRA'");
    }
};

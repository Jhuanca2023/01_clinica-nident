<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_lookbook')) {
            return;
        }

        Schema::table('web_lookbook', function (Blueprint $table) {
            if (! Schema::hasColumn('web_lookbook', 'imagen_lateral_texto')) {
                $table->string('imagen_lateral_texto', 255)->nullable()->after('url_imagen_lateral');
            }
            if (! Schema::hasColumn('web_lookbook', 'imagen_lateral_cta')) {
                $table->string('imagen_lateral_cta', 120)->nullable()->after('imagen_lateral_texto');
            }
            if (! Schema::hasColumn('web_lookbook', 'imagen_lateral_url')) {
                $table->string('imagen_lateral_url', 500)->nullable()->after('imagen_lateral_cta');
            }
        });

        $row = DB::table('web_lookbook')->where('id', 1)->first();
        if ($row) {
            $updates = [];
            if (empty($row->imagen_lateral_texto)) {
                $updates['imagen_lateral_texto'] = 'Liam Vercceli';
            }
            if (empty($row->imagen_lateral_cta)) {
                $updates['imagen_lateral_cta'] = 'Ver Colección';
            }
            if (empty($row->imagen_lateral_url)) {
                $updates['imagen_lateral_url'] = $row->btn_url ?: '/tienda';
            }
            if ($updates) {
                $updates['updated_at'] = now();
                DB::table('web_lookbook')->where('id', 1)->update($updates);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('web_lookbook')) {
            return;
        }

        Schema::table('web_lookbook', function (Blueprint $table) {
            foreach (['imagen_lateral_url', 'imagen_lateral_cta', 'imagen_lateral_texto'] as $col) {
                if (Schema::hasColumn('web_lookbook', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

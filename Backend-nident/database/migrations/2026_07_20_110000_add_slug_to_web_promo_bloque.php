<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_promo_bloque')) {
            return;
        }

        Schema::table('web_promo_bloque', function (Blueprint $table) {
            if (! Schema::hasColumn('web_promo_bloque', 'slug')) {
                $table->string('slug', 180)->nullable()->after('subtitulo_seccion');
            }
        });

        $rows = DB::table('web_promo_bloque')->get(['id_promo_bloque', 'subtitulo_seccion', 'titulo_seccion', 'slug']);
        $used = [];
        foreach ($rows as $row) {
            if (! empty($row->slug)) {
                $used[$row->slug] = true;
                continue;
            }
            $base = Str::slug($row->subtitulo_seccion ?: $row->titulo_seccion ?: 'ofertas-promociones');
            if ($base === '') {
                $base = 'ofertas-promociones';
            }
            $slug = $base;
            $n = 1;
            while (isset($used[$slug])) {
                $slug = $base . '-' . $n++;
            }
            $used[$slug] = true;
            DB::table('web_promo_bloque')->where('id_promo_bloque', $row->id_promo_bloque)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('web_promo_bloque')) {
            return;
        }
        Schema::table('web_promo_bloque', function (Blueprint $table) {
            if (Schema::hasColumn('web_promo_bloque', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};

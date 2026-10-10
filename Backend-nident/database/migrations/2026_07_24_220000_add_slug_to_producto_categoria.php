<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('administracion_producto_categoria')
            && ! Schema::hasColumn('administracion_producto_categoria', 'slug')) {
            Schema::table('administracion_producto_categoria', function (Blueprint $table) {
                $table->string('slug', 180)->nullable()->after('nombre');
                $table->unique('slug');
            });
        }

        if (Schema::hasTable('administracion_producto_categoria_sub')
            && ! Schema::hasColumn('administracion_producto_categoria_sub', 'slug')) {
            Schema::table('administracion_producto_categoria_sub', function (Blueprint $table) {
                $table->string('slug', 180)->nullable()->after('nombre');
            });
        }

        // Rellenar slugs SEO desde el nombre
        if (Schema::hasColumn('administracion_producto_categoria', 'slug')) {
            $cats = DB::table('administracion_producto_categoria')->orderBy('id_producto_categoria')->get();
            $used = [];
            foreach ($cats as $cat) {
                $base = Str::slug((string) $cat->nombre) ?: ('categoria-' . $cat->id_producto_categoria);
                if ($base === 'ofertas') {
                    $base = 'categoria-ofertas';
                }
                $slug = $base;
                $n = 2;
                while (isset($used[$slug])) {
                    $slug = $base . '-' . $n++;
                }
                $used[$slug] = true;
                DB::table('administracion_producto_categoria')
                    ->where('id_producto_categoria', $cat->id_producto_categoria)
                    ->update(['slug' => $slug]);
            }
        }

        if (Schema::hasColumn('administracion_producto_categoria_sub', 'slug')) {
            $subs = DB::table('administracion_producto_categoria_sub')->orderBy('id_producto_categoria_sub')->get();
            foreach ($subs as $sub) {
                $base = Str::slug((string) $sub->nombre) ?: ('sub-' . $sub->id_producto_categoria_sub);
                $slug = $base;
                $n = 2;
                // Único dentro de la misma categoría padre
                while (
                    DB::table('administracion_producto_categoria_sub')
                        ->where('id_producto_categoria', $sub->id_producto_categoria)
                        ->where('slug', $slug)
                        ->where('id_producto_categoria_sub', '!=', $sub->id_producto_categoria_sub)
                        ->exists()
                ) {
                    $slug = $base . '-' . $n++;
                }
                DB::table('administracion_producto_categoria_sub')
                    ->where('id_producto_categoria_sub', $sub->id_producto_categoria_sub)
                    ->update(['slug' => $slug]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('administracion_producto_categoria')
            && Schema::hasColumn('administracion_producto_categoria', 'slug')) {
            Schema::table('administracion_producto_categoria', function (Blueprint $table) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            });
        }
        if (Schema::hasTable('administracion_producto_categoria_sub')
            && Schema::hasColumn('administracion_producto_categoria_sub', 'slug')) {
            Schema::table('administracion_producto_categoria_sub', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};

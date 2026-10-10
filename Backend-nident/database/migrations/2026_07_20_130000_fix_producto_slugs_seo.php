<?php

use App\Models\Producto;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Regenera slugs SEO a partir del nombre (guiones) cuando están vacíos,
 * numéricos o no coinciden con el nombre actual.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('administracion_producto') || ! Schema::hasColumn('administracion_producto', 'slug')) {
            return;
        }

        Producto::query()
            ->select(['id_producto', 'nombre', 'slug'])
            ->orderBy('id_producto')
            ->chunkById(100, function ($productos) {
                foreach ($productos as $producto) {
                    $expected = Str::slug((string) ($producto->nombre ?? ''));
                    $current  = trim((string) ($producto->slug ?? ''));

                    $needsFix = $current === ''
                        || ctype_digit($current)
                        || ($expected !== '' && ! str_starts_with($current, $expected));

                    if (! $needsFix) {
                        continue;
                    }

                    $producto->slug = Producto::uniqueSlugFromNombre(
                        (string) $producto->nombre,
                        (int) $producto->id_producto
                    );
                    // updateQuietly evita reentradas del boot; ya calculamos el slug
                    $producto->saveQuietly();
                }
            }, 'id_producto');
    }

    public function down(): void
    {
        // No reversible de forma segura
    }
};

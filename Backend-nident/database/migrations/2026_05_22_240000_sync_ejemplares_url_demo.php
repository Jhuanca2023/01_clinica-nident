<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('web_ejemplares') || !Schema::hasTable('web_ejemplares_seccion')) {
            return;
        }

        $path = database_path('seeders/data/ejemplares.json');
        if (!is_file($path)) {
            return;
        }

        $data = json_decode(file_get_contents($path), true);
        if (!is_array($data['items'] ?? null)) {
            return;
        }

        $sections = DB::table('web_ejemplares_seccion')->pluck('id', 'slug');

        foreach ($data['items'] as $item) {
            $secId = $sections[$item['seccion_slug'] ?? ''] ?? null;
            if (!$secId) {
                continue;
            }

            $demo = trim((string) ($item['url_demo'] ?? ''));
            if ($demo === '' || $demo === '#') {
                continue;
            }

            DB::table('web_ejemplares')
                ->where('id_seccion', $secId)
                ->where('nombre', $item['nombre'] ?? '')
                ->update([
                    'url_demo'      => $demo,
                    'slug'          => $item['slug'] ?? null,
                    'updated_at'    => now(),
                ]);
        }
    }

    public function down(): void
    {
        // Datos de contenido; no revertir URLs demo.
    }
};

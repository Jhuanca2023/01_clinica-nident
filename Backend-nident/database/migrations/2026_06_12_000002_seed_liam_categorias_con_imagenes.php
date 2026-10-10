<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CATEGORIAS = [
        [
            'nombre'    => 'Polo Cuellera',
            'imagen'    => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&h=400&fit=crop&q=80',
            'filename'  => 'cat_polo_cuellera.jpg',
            'tipo'      => 'url',
        ],
        [
            'nombre'    => 'Polo Básico',
            'imagen'    => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=400&h=400&fit=crop&q=80',
            'filename'  => 'cat_polo_basico.jpg',
            'tipo'      => 'url',
        ],
        [
            'nombre'    => 'Polo Estampado',
            'imagen'    => 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=400&h=400&fit=crop&q=80',
            'filename'  => 'cat_polo_estampado.jpg',
            'tipo'      => 'url',
        ],
        [
            'nombre'    => 'Polo Deportivo',
            'imagen'    => 'https://images.unsplash.com/photo-1519058082700-08a0b56da9b4?w=400&h=400&fit=crop&q=80',
            'filename'  => 'cat_polo_deportivo.jpg',
            'tipo'      => 'url',
        ],
        [
            'nombre'    => 'Polo Manga Larga',
            'imagen'    => 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=400&h=400&fit=crop&q=80',
            'filename'  => 'cat_polo_manga_larga.jpg',
            'tipo'      => 'url',
        ],
        [
            'nombre'    => 'Polo Camisero',
            'imagen'    => 'temp02/img/inicio/productos/1.jpg',
            'filename'  => 'cat_polo_camisero.jpg',
            'tipo'      => 'local',
        ],
        [
            'nombre'    => 'Polo Premium',
            'imagen'    => 'https://images.unsplash.com/photo-1574180566232-aaad1b5b8450?w=400&h=400&fit=crop&q=80',
            'filename'  => 'cat_polo_premium.jpg',
            'tipo'      => 'url',
        ],
        [
            'nombre'    => 'Polo Casual',
            'imagen'    => 'temp02/img/inicio/productos/3.jpg',
            'filename'  => 'cat_polo_casual.jpg',
            'tipo'      => 'local',
        ],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('administracion_producto_categoria')) {
            return;
        }

        $destDir = public_path('storage_/categorias');
        if (! is_dir($destDir)) {
            mkdir($destDir, 0775, true);
        }

        foreach (self::CATEGORIAS as $cat) {
            // Skip if already exists
            $exists = DB::table('administracion_producto_categoria')
                ->where('nombre', $cat['nombre'])
                ->first();

            $destPath = $destDir . DIRECTORY_SEPARATOR . $cat['filename'];
            $urlPath  = 'storage_/categorias/' . $cat['filename'];

            // Download / copy image
            if (! file_exists($destPath)) {
                if ($cat['tipo'] === 'url') {
                    $this->downloadUrl($cat['imagen'], $destPath);
                } else {
                    $srcPath = public_path($cat['imagen']);
                    if (file_exists($srcPath)) {
                        copy($srcPath, $destPath);
                    }
                }
            }

            $imgSaved = file_exists($destPath) ? $urlPath : null;

            if ($exists) {
                // Only update url_imagen if not already set
                if (empty($exists->url_imagen) && $imgSaved) {
                    DB::table('administracion_producto_categoria')
                        ->where('id_producto_categoria', $exists->id_producto_categoria)
                        ->update(['url_imagen' => $imgSaved, 'updated_at' => now()]);
                }
            } else {
                // PK has no AUTO_INCREMENT — must supply explicit id
                $maxId = (int) DB::table('administracion_producto_categoria')->max('id_producto_categoria');
                DB::table('administracion_producto_categoria')->insert([
                    'id_producto_categoria' => $maxId + 1,
                    'nombre'                => $cat['nombre'],
                    'url_imagen'            => $imgSaved,
                    'Activo'                => 'S',
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach (self::CATEGORIAS as $cat) {
            $file = public_path('storage_/categorias/' . $cat['filename']);
            if (file_exists($file)) {
                @unlink($file);
            }
            DB::table('administracion_producto_categoria')
                ->where('nombre', $cat['nombre'])
                ->delete();
        }
    }

    private function downloadUrl(string $url, string $dest): void
    {
        $ctx = stream_context_create([
            'http' => [
                'timeout'        => 20,
                'follow_location' => 1,
                'user_agent'     => 'Mozilla/5.0',
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        $data = @file_get_contents($url, false, $ctx);
        if ($data !== false) {
            file_put_contents($dest, $data);
        }
    }
};

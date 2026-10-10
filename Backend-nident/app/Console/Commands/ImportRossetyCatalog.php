<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportRossetyCatalog extends Command
{
    protected $signature = 'rossety:import-catalog {--force : Vuelve a escribir productos PRS-}';

    protected $description = 'Importa el catálogo de Detalles Rossety a productos y categorías';

    public function handle(): int
    {
        $path = public_path('rossety/assets/js/rossety/data/catalog.js');
        if (! is_file($path)) {
            $this->error('No está el catalog.js en public/rossety.');

            return 1;
        }
        $raw = preg_replace('/^\s*\/\*.*?\*\/\s*/s', '', (string) file_get_contents($path));
        $raw = preg_replace('/^\s*window\.ROSSETY\s*=\s*/', '', $raw);
        $raw = preg_replace('/;\s*$/', '', trim($raw));
        $data = json_decode($raw, true);
        if (! is_array($data) || empty($data['productos'])) {
            $this->error('catalog.js no se pudo leer como JSON.');

            return 1;
        }

        $catIds = [];
        $nextId = (int) DB::table('administracion_producto_categoria')->max('id_producto_categoria');
        foreach ($data['categorias'] as $cat) {
            $slug = (string) $cat['slug'];
            $existing = DB::table('administracion_producto_categoria')->where('slug', $slug)->first();
            $imagen = 'rossety/assets/images/inicio/cat-'.$slug.'.jpg';
            if ($existing) {
                DB::table('administracion_producto_categoria')
                    ->where('id_producto_categoria', $existing->id_producto_categoria)
                    ->update([
                        'nombre' => $cat['name'],
                        'url_imagen' => $imagen,
                        'Activo' => 'S',
                        'mostrar_panel_principal' => 'S',
                        'updated_at' => now(),
                    ]);
                $catIds[$slug] = (int) $existing->id_producto_categoria;
                continue;
            }
            $nextId++;
            DB::table('administracion_producto_categoria')->insert([
                'id_producto_categoria' => $nextId,
                'nombre' => $cat['name'],
                'slug' => $slug,
                'url_imagen' => $imagen,
                'Activo' => 'S',
                'mostrar_panel_principal' => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $catIds[$slug] = $nextId;
        }

        $force = (bool) $this->option('force');
        $n = 0;
        foreach ($data['productos'] as $p) {
            $slug = (string) $p['id'];
            $codigo = 'PRS-'.$slug;
            $imagen = (string) ($p['imagen'] ?? '');
            if (str_starts_with($imagen, 'assets/')) {
                $imagen = 'rossety/'.$imagen;
            }
            $incluye = $p['incluye'] ?? [];
            $row = [
                'codigo_producto' => $codigo,
                'codigo_producto_new' => $p['modelo'] ?? null,
                'etiqueta_badge' => $p['tag'] ?? null,
                'nombre' => $p['nombre'],
                'slug' => $slug,
                'descripcion' => is_array($incluye) ? implode("\n", $incluye) : null,
                'ubicacion' => $p['sub'] ?? null,
                'precio' => $p['precio'],
                'stock' => $p['precio'] === null ? '0' : '50',
                'id_producto_categoria' => $catIds[$p['cat']] ?? null,
                'url_imagen' => $imagen,
                'Activo' => 'S',
                'updated_at' => now(),
            ];
            $found = DB::table('administracion_producto')->where('codigo_producto', $codigo)->first();
            if ($found && ! $force) {
                continue;
            }
            if ($found) {
                DB::table('administracion_producto')->where('id_producto', $found->id_producto)->update($row);
            } else {
                $row['created_at'] = now();
                $row['ratings_enabled'] = 0;
                $row['tallas_disponibles'] = '';
                DB::table('administracion_producto')->insert($row);
            }
            $n++;
        }

        $this->info('Categorías Rossety listas. Productos escritos: '.$n);

        if (Schema::hasTable('web_header')) {
            DB::table('web_header')->orderBy('id_header')->limit(1)->update([
                'url_logo' => 'rossety/assets/images/inicio/logo-principal.svg',
                'telefono_numero' => '989 914 154',
                'telefono_href' => 'tel:+51989914154',
                'top_ubicacion' => 'Relave, Ayacucho',
                'topbar_wa_numero' => '51989914154',
                'updated_at' => now(),
            ]);
        }

        return 0;
    }
}

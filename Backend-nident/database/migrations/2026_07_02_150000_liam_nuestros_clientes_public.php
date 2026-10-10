<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MENU_URL = '/carrusel/index';

    private const SITIO_WEB_MODULO = 71;

    public function up(): void
    {
        if (Schema::hasTable('web_cliente_seccion')) {
            DB::table('web_cliente_seccion')->where('id', 1)->update([
                'tag'         => 'Nuestros Clientes',
                'titulo'      => 'Marcas que confían en nosotros',
                'descripcion' => 'Empresas y tiendas que eligen la calidad Liam Vercceli en todo el Perú.',
                'cta_texto'   => null,
                'cta_url'     => null,
                'updated_at'  => now(),
            ]);
        }

        if (Schema::hasTable('web_cliente_logo')) {
            $fallback = 'temp02/img/logos/logo-1.png';
            foreach (DB::table('web_cliente_logo')->where('Activo', 'S')->orderBy('orden')->get() as $row) {
                $path = (string) ($row->url_imagen ?? '');
                if ($path === '' || ! is_file(public_path(ltrim(str_replace('\\', '/', $path), '/')))) {
                    DB::table('web_cliente_logo')->where('id', $row->id)->update([
                        'url_imagen' => $fallback,
                        'updated_at' => now(),
                    ]);
                }
            }

            if (Schema::hasTable('web_carrusel')) {
                $carrusel = DB::table('web_carrusel')->where('Activo', 'S')->orderBy('orden')->get();
                $logos = DB::table('web_cliente_logo')->where('Activo', 'S')->orderBy('orden')->get();
                foreach ($carrusel as $i => $item) {
                    $img = (string) ($item->url_imagen ?? '');
                    if ($img === '' || ! is_file(public_path(ltrim(str_replace('\\', '/', $img), '/')))) {
                        continue;
                    }
                    $logo = $logos[$i] ?? null;
                    if ($logo) {
                        DB::table('web_cliente_logo')->where('id', $logo->id)->update([
                            'nombre'     => $item->label ?: ($logo->nombre ?? 'Cliente'),
                            'url_imagen' => $img,
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        if (Schema::hasTable('sistema_menu')) {
            $update = [
                'nombre'     => 'Nuestros clientes',
                'Activo'     => 'S',
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('sistema_menu', 'id_modulo')) {
                $update['id_modulo'] = self::SITIO_WEB_MODULO;
            }
            if (Schema::hasColumn('sistema_menu', 'Icon')) {
                $update['Icon'] = 'groups';
            }
            if (Schema::hasColumn('sistema_menu', 'orden')) {
                $update['orden'] = 3;
            }

            $row = DB::table('sistema_menu')->where('url', self::MENU_URL)->first();
            if ($row) {
                DB::table('sistema_menu')->where('id_menu', $row->id_menu)->update($update);
                $this->assignMenuToAllRoles((int) $row->id_menu);
            }
        }
    }

    public function down(): void
    {
        // Sin reversión automática de contenido.
    }

    private function assignMenuToAllRoles(int $idMenu): void
    {
        if (! Schema::hasTable('seguridad_roles_menu') || ! Schema::hasTable('seguridad_roles')) {
            return;
        }

        foreach (DB::table('seguridad_roles')->pluck('id_roles') as $idRoles) {
            $exists = DB::table('seguridad_roles_menu')
                ->where('id_roles', $idRoles)
                ->where('id_menu', $idMenu)
                ->exists();
            if (! $exists) {
                DB::table('seguridad_roles_menu')->insert([
                    'id_roles' => $idRoles,
                    'id_menu'  => $idMenu,
                ]);
            }
        }
    }
};

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Añade los ítems de menú "Menús" y "Objetos" (Seguridad) y otorga acceso al rol ACCESO GENERAL.
 * Así aparecen en el sidebar y el usuario admin puede entrar a /menu/index y /objetos/index.
 *
 * Ejecutar: php artisan db:seed --class=AgregarMenuObjetosAccesoSeeder
 */
class AgregarMenuObjetosAccesoSeeder extends Seeder
{
    public function run(): void
    {
        $idModuloSeguridad = 5;
        $idRolAdmin = 1; // ACCESO GENERAL
        $objetos = [23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33];

        // Evitar duplicados: si ya existen menús con estas URLs, no hacer nada
        $existe = DB::table('sistema_menu')
            ->where('id_modulo', $idModuloSeguridad)
            ->whereIn('url', ['/menu/index', '/objetos/index'])
            ->exists();
        if ($existe) {
            $this->command->info('Los ítems Menús y Objetos ya existen. Nada que hacer.');
            return;
        }

        $now = now()->format('Y-m-d H:i:s');

        // 1) Insertar los dos menús
        DB::table('sistema_menu')->insert([
            [
                'id_modulo' => $idModuloSeguridad,
                'nombre' => 'Menús',
                'url' => '/menu/index',
                'created_at' => $now,
                'updated_at' => $now,
                'Activo' => 'S',
            ],
            [
                'id_modulo' => $idModuloSeguridad,
                'nombre' => 'Objetos',
                'url' => '/objetos/index',
                'created_at' => $now,
                'updated_at' => $now,
                'Activo' => 'S',
            ],
        ]);

        $idMenuMenus = DB::table('sistema_menu')->where('url', '/menu/index')->value('id_menu');
        $idMenuObjetos = DB::table('sistema_menu')->where('url', '/objetos/index')->value('id_menu');

        if (!$idMenuMenus || !$idMenuObjetos) {
            $this->command->error('No se pudieron obtener los id_menu insertados.');
            return;
        }

        $this->command->info("Menús insertados: id_menu {$idMenuMenus} (Menús), {$idMenuObjetos} (Objetos).");

        // 2) sistema_menu_objetos: 11 objetos por cada menú
        $idsMenuObjetos = [];
        foreach ([$idMenuMenus, $idMenuObjetos] as $idMenu) {
            foreach ($objetos as $idObjeto) {
                $idMo = DB::table('sistema_menu_objetos')->insertGetId([
                    'id_menu' => $idMenu,
                    'id_objetos' => $idObjeto,
                    'selected' => null,
                    'orden' => null,
                    'estado' => null,
                    'created_at' => null,
                    'updated_at' => null,
                    'Activo' => 'S',
                ]);
                $idsMenuObjetos[] = $idMo;
            }
        }

        // 3) Permisos de menú para el rol administrador
        DB::table('seguridad_roles_menu')->insertOrIgnore([
            ['id_roles' => $idRolAdmin, 'id_menu' => $idMenuMenus],
            ['id_roles' => $idRolAdmin, 'id_menu' => $idMenuObjetos],
        ]);

        // 4) Permisos de objetos por menú para el rol administrador
        if (Schema::hasTable('seguridad_menu_objetos_roles')) {
            foreach ($idsMenuObjetos as $idMo) {
                DB::table('seguridad_menu_objetos_roles')->insertOrIgnore([
                    'id_roles' => $idRolAdmin,
                    'id_menu_objetos' => $idMo,
                ]);
            }
        }

        $this->command->info('Listo. Acceso a Menús y Objetos asignado al rol ACCESO GENERAL. Recarga el front y cierra sesión/entra de nuevo si no ves el menú.');
    }
}

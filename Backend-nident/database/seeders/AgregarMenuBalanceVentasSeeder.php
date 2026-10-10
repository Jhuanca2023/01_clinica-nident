<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * "Balance Ventas" dentro del módulo "Ventas"
 * SP_procedure/2026/03/16/jose/insert_menu_balance_ventas.sql
 */
class AgregarMenuBalanceVentasSeeder extends Seeder
{
    public function run(): void
    {
      
        $moduloVentas = DB::table('sistema_modulo')
            ->where(function($query) {
                $query->where('nombre', 'Ventas')
                      ->orWhere('nombre', 'LIKE', '%Ventas%');
            })
            ->where('Activo', 'S')
            ->first();

        if (!$moduloVentas) {
            $this->command->error('No se encontró el módulo "Ventas". Por favor créalo primero.');
            return;
        }

        $idModuloVentas = $moduloVentas->id_modulo;
        $this->command->info("Módulo Ventas encontrado con ID: {$idModuloVentas}");

        
        $existe = DB::table('sistema_menu')
            ->where('id_modulo', $idModuloVentas)
            ->where('url', '/balance_ventas/index')
            ->where('Activo', 'S')
            ->exists();

        if ($existe) {
            $this->command->info('El menú "Balance Ventas" ya existe. Verificando permisos...');
            $idMenu = DB::table('sistema_menu')
                ->where('id_modulo', $idModuloVentas)
                ->where('url', '/balance_ventas/index')
                ->where('Activo', 'S')
                ->value('id_menu');
        } else {
            
            $maxOrden = (int) DB::table('sistema_menu')
                ->where('id_modulo', $idModuloVentas)
                ->max('orden');

            $now = now()->format('Y-m-d H:i:s');

           
            $idMenu = DB::table('sistema_menu')->insertGetId([
                'id_modulo' => $idModuloVentas,
                'nombre' => 'Balance Ventas',
                'url' => '/balance_ventas/index',
                'Activo' => 'S',
                'orden' => ($maxOrden + 1),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Actualizar el campo Icon
            if (DB::getSchemaBuilder()->hasColumn('sistema_menu', 'Icon')) {
                DB::table('sistema_menu')
                    ->where('id_menu', $idMenu)
                    ->update(['Icon' => 'trending_up']);
            }

            $this->command->info("Menú 'Balance Ventas' creado exitosamente con ID: {$idMenu}");
        }

        $this->command->info("URL: /balance_ventas/index");
        $this->command->info("Módulo: Ventas (ID: {$idModuloVentas})");

        // Asignar permisos (ACCESO GENERAL)
        $rolAdmin = DB::table('seguridad_roles')
            ->where(function($query) {
                $query->where('nombre', 'ACCESO GENERAL')
                      ->orWhere('id_roles', 1);
            })
            ->where('Activo', 'S')
            ->first();

        if ($rolAdmin) {
            $idRolAdmin = $rolAdmin->id_roles;
            
            
            $tienePermiso = DB::table('seguridad_roles_menu')
                ->where('id_roles', $idRolAdmin)
                ->where('id_menu', $idMenu)
                ->exists();

            if (!$tienePermiso) {
                DB::table('seguridad_roles_menu')->insert([
                    'id_roles' => $idRolAdmin,
                    'id_menu' => $idMenu,
                ]);
                $this->command->info("Permisos asignados al rol Administrador (ID: {$idRolAdmin})");
            } else {
                $this->command->info("El rol Administrador ya tiene permisos para este menú");
            }
        } else {
            $this->command->warn("No se encontró el rol Administrador. Asigna los permisos manualmente desde el panel de administración.");
        }
    }
}

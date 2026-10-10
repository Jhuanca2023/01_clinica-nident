<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Módulo "Delivery" (admin): reglas flexibles de envío tomadas de Prestomart
 * + interruptor para activar / desactivar el delivery.
 *
 * Los valores por defecto conservan el cálculo que ya tenía Valento:
 * compras menores al mínimo pagan el costo de "compra baja" (sin bloquear),
 * incluso dentro del radio y en distritos especiales.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('del_configuracion_delivery')) {
            Schema::table('del_configuracion_delivery', function (Blueprint $table) {
                if (! Schema::hasColumn('del_configuracion_delivery', 'delivery_habilitado')) {
                    $table->boolean('delivery_habilitado')->default(true)->after('id')
                        ->comment('Interruptor general: 0 = la tienda no hace delivery');
                }
                if (! Schema::hasColumn('del_configuracion_delivery', 'mensaje_delivery_deshabilitado')) {
                    $table->string('mensaje_delivery_deshabilitado', 300)->nullable()->after('delivery_habilitado')
                        ->comment('Texto del checkout cuando el delivery está desactivado');
                }
                if (! Schema::hasColumn('del_configuracion_delivery', 'monto_minimo_radio')) {
                    $table->decimal('monto_minimo_radio', 8, 2)->default(0)->after('radio_metros_gratis')
                        ->comment('Compra mínima para que el radio cercano sea GRATIS');
                }
                if (! Schema::hasColumn('del_configuracion_delivery', 'distritos_especiales_sin_minimo')) {
                    $table->boolean('distritos_especiales_sin_minimo')->default(false)->after('costo_distritos_especiales')
                        ->comment('1 = distritos especiales pagan su costo fijo aunque no lleguen al mínimo');
                }
                if (! Schema::hasColumn('del_configuracion_delivery', 'bloquear_bajo_minimo')) {
                    $table->boolean('bloquear_bajo_minimo')->default(false)->after('costo_envio_baja_compra')
                        ->comment('1 = bajo el mínimo no se permite comprar; 0 = se cobra el costo de compra baja');
                }
                if (! Schema::hasColumn('del_configuracion_delivery', 'distritos_gratis')) {
                    $table->json('distritos_gratis')->nullable()->after('distritos_especiales')
                        ->comment('Distritos con envío GRATIS al superar monto_minimo_compra');
                }
            });

            // Mismo comportamiento que antes: el radio también exigía el monto mínimo
            DB::table('del_configuracion_delivery')
                ->whereNull('distritos_gratis')
                ->update([
                    'monto_minimo_radio' => DB::raw('monto_minimo_compra'),
                    'distritos_gratis'   => json_encode([]),
                ]);
        }

        $this->registrarMenu();
    }

    public function down(): void
    {
        if (Schema::hasTable('sistema_menu')) {
            $ids = DB::table('sistema_menu')->where('url', '/delivery/index')->pluck('id_menu');
            if ($ids->isNotEmpty() && Schema::hasTable('seguridad_roles_menu')) {
                DB::table('seguridad_roles_menu')->whereIn('id_menu', $ids)->delete();
            }
            DB::table('sistema_menu')->where('url', '/delivery/index')->delete();
        }

        if (Schema::hasTable('del_configuracion_delivery')) {
            Schema::table('del_configuracion_delivery', function (Blueprint $table) {
                foreach ([
                    'delivery_habilitado', 'mensaje_delivery_deshabilitado', 'monto_minimo_radio',
                    'distritos_especiales_sin_minimo', 'bloquear_bajo_minimo', 'distritos_gratis',
                ] as $col) {
                    if (Schema::hasColumn('del_configuracion_delivery', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }

    /** Menú "Delivery" junto a Pasarela de pago (Tienda Virtual), con sus mismos roles. */
    private function registrarMenu(): void
    {
        if (! Schema::hasTable('sistema_menu') || DB::table('sistema_menu')->where('url', '/delivery/index')->exists()) {
            return;
        }

        $pasarela = DB::table('sistema_menu')->where('url', '/configuracion/pasarela')->first();
        $now = now();

        $datos = [
            'id_modulo'     => $pasarela->id_modulo ?? null,
            'id_menu_padre' => $pasarela->id_menu_padre ?? null,
            'nombre'        => 'Delivery',
            'url'           => '/delivery/index',
            'Activo'        => 'S',
            'orden'         => (int) ($pasarela->orden ?? 25),
            'Icon'          => 'delivery_dining',
            'created_at'    => $now,
            'updated_at'    => $now,
        ];

        try {
            $idMenu = DB::table('sistema_menu')->insertGetId($datos);
        } catch (\Throwable $e) {
            // Tablas sin AUTO_INCREMENT en algunos entornos
            $idMenu = (int) DB::table('sistema_menu')->max('id_menu') + 1;
            DB::table('sistema_menu')->insert($datos + ['id_menu' => $idMenu]);
        }

        // Pasarela pasa detrás de Delivery
        if ($pasarela) {
            DB::table('sistema_menu')->where('id_menu', $pasarela->id_menu)->update(['orden' => (int) $pasarela->orden + 1]);
        }

        if (Schema::hasTable('seguridad_roles_menu')) {
            $roles = $pasarela
                ? DB::table('seguridad_roles_menu')->where('id_menu', $pasarela->id_menu)->pluck('id_roles')
                : collect([1]);

            foreach ($roles->unique() as $idRol) {
                DB::table('seguridad_roles_menu')->insert(['id_roles' => $idRol, 'id_menu' => $idMenu]);
            }
        }
    }
};

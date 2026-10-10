<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cotizacion_tipo_proyecto')) {
            return;
        }

        Schema::table('cotizacion_tipo_proyecto', function (Blueprint $table) {
            if (!Schema::hasColumn('cotizacion_tipo_proyecto', 'funcionalidades')) {
                $table->json('funcionalidades')->nullable()->after('nota_custom');
            }
        });

        // Seed funcionalidades por defecto para el tipo "tienda"
        if (DB::table('cotizacion_tipo_proyecto')->where('slug', 'tienda')->exists()) {
            DB::table('cotizacion_tipo_proyecto')->where('slug', 'tienda')->update([
                'funcionalidades' => json_encode([
                    ['nombre' => 'Catálogo de productos',   'incluido' => true,  'descripcion' => 'Listado con imágenes y precios'],
                    ['nombre' => 'Carrito de compras',      'incluido' => true,  'descripcion' => 'Selección y acumulación de productos'],
                    ['nombre' => 'Pedidos por WhatsApp',    'incluido' => true,  'descripcion' => 'Confirmación directa al vendedor'],
                    ['nombre' => 'Panel de administración', 'incluido' => true,  'descripcion' => 'Gestión de productos y pedidos'],
                    ['nombre' => 'Pasarela de pago online', 'incluido' => false, 'descripcion' => 'Módulo adicional disponible'],
                    ['nombre' => 'Gestión de inventario',   'incluido' => false, 'descripcion' => 'Módulo adicional disponible'],
                ], JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('cotizacion_tipo_proyecto')) {
            return;
        }

        Schema::table('cotizacion_tipo_proyecto', function (Blueprint $table) {
            if (Schema::hasColumn('cotizacion_tipo_proyecto', 'funcionalidades')) {
                $table->dropColumn('funcionalidades');
            }
        });
    }
};

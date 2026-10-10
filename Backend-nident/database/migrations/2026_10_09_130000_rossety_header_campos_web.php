<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Header Rossety: campos que la web mostraba fijos en el HTML y ahora se editan desde el admin
 * (mensaje de la barra superior, botón del menú y buscador).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_header')) {
            return;
        }

        Schema::table('web_header', function (Blueprint $table) {
            if (! Schema::hasColumn('web_header', 'nav_promo_texto')) {
                $table->string('nav_promo_texto', 100)->nullable();
            }
            if (! Schema::hasColumn('web_header', 'nav_promo_url')) {
                $table->string('nav_promo_url', 255)->nullable();
            }
            if (! Schema::hasColumn('web_header', 'buscador_placeholder')) {
                $table->string('buscador_placeholder', 100)->nullable();
            }
        });

        DB::table('web_header')->where('id_header', 1)->update([
            'nav_promo_texto'       => 'Temporada girasoles',
            'nav_promo_url'         => '/tienda?cat=girasoles',
            'buscador_placeholder'  => '¿Qué estás buscando?',
            'topbar_mensaje_centro' => 'Detalles únicos para cada ocasión · Pide por WhatsApp',
            'updated_at'            => now(),
        ]);
    }

    public function down(): void
    {
        if (Schema::hasTable('web_header')) {
            Schema::table('web_header', function (Blueprint $table) {
                $table->dropColumn(['nav_promo_texto', 'nav_promo_url', 'buscador_placeholder']);
            });
        }
    }
};

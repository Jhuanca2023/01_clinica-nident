<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_promo_bloque_producto')) {
            Schema::create('web_promo_bloque_producto', function (Blueprint $table) {
                $table->increments('id_promo_bloque_producto');
                $table->unsignedInteger('id_promo_bloque');
                $table->unsignedInteger('id_producto');
                $table->unsignedSmallInteger('orden')->default(0);
                $table->unique(['id_promo_bloque', 'id_producto'], 'uk_web_promo_bloque_producto');
            });
        }

        // Migrar vínculos previos (1 producto por ítem) al nuevo modelo por bloque
        if (Schema::hasTable('web_promo_item') && Schema::hasTable('web_promo_bloque_producto')) {
            $rows = DB::table('web_promo_item')
                ->whereNotNull('id_producto')
                ->where('id_producto', '>', 0)
                ->orderBy('id_promo_bloque')
                ->orderBy('orden')
                ->get(['id_promo_bloque', 'id_producto', 'orden']);

            foreach ($rows as $row) {
                DB::table('web_promo_bloque_producto')->insertOrIgnore([
                    'id_promo_bloque' => $row->id_promo_bloque,
                    'id_producto'     => $row->id_producto,
                    'orden'           => (int) ($row->orden ?? 0),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('web_promo_bloque_producto');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_promo_item_producto')) {
            Schema::create('web_promo_item_producto', function (Blueprint $table) {
                $table->increments('id_promo_item_producto');
                $table->unsignedInteger('id_promo_item');
                $table->unsignedInteger('id_producto');
                $table->unsignedSmallInteger('orden')->default(1);
                $table->unique(['id_promo_item', 'id_producto'], 'uk_web_promo_item_producto');
                $table->index('id_producto');
            });
        }

        // Migrar id_producto legacy del ítem → pivote
        if (Schema::hasTable('web_promo_item') && Schema::hasColumn('web_promo_item', 'id_producto')) {
            $rows = DB::table('web_promo_item')
                ->whereNotNull('id_producto')
                ->where('id_producto', '>', 0)
                ->get(['id_promo_item', 'id_producto']);

            foreach ($rows as $row) {
                DB::table('web_promo_item_producto')->insertOrIgnore([
                    'id_promo_item' => (int) $row->id_promo_item,
                    'id_producto'   => (int) $row->id_producto,
                    'orden'         => 1,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('web_promo_item_producto');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('web_promo_item')) {
            return;
        }

        Schema::table('web_promo_item', function (Blueprint $table) {
            if (!Schema::hasColumn('web_promo_item', 'id_producto')) {
                $table->unsignedInteger('id_producto')->nullable()->after('id_promo_bloque');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('web_promo_item')) {
            return;
        }

        Schema::table('web_promo_item', function (Blueprint $table) {
            if (Schema::hasColumn('web_promo_item', 'id_producto')) {
                $table->dropColumn('id_producto');
            }
        });
    }
};

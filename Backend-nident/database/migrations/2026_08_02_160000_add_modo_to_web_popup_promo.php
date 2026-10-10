<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_popup_promo')) {
            return;
        }

        Schema::table('web_popup_promo', function (Blueprint $table) {
            if (! Schema::hasColumn('web_popup_promo', 'modo')) {
                // formulario | imagen | oferta_tienda
                $table->string('modo', 30)->default('formulario')->after('activo');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('web_popup_promo')) {
            return;
        }

        Schema::table('web_popup_promo', function (Blueprint $table) {
            if (Schema::hasColumn('web_popup_promo', 'modo')) {
                $table->dropColumn('modo');
            }
        });
    }
};

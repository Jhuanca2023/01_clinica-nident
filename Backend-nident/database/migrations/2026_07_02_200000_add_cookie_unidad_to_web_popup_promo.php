<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_popup_promo', function (Blueprint $table) {
            // 'minutos' | 'horas' | 'dias' | 'siempre'
            $table->string('cookie_unidad', 20)->default('dias')->after('cookie_dias');
        });
    }

    public function down(): void
    {
        Schema::table('web_popup_promo', function (Blueprint $table) {
            $table->dropColumn('cookie_unidad');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_popup_promo', function (Blueprint $table) {
            $table->text('opciones')->nullable()->after('cookie_unidad');
            $table->string('opciones_label', 120)->default('Prefiero prendas:')->after('opciones');
            $table->boolean('mostrar_opciones')->default(false)->after('opciones_label');
        });
    }

    public function down(): void
    {
        Schema::table('web_popup_promo', function (Blueprint $table) {
            $table->dropColumn(['opciones', 'opciones_label', 'mostrar_opciones']);
        });
    }
};

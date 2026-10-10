<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_popup_promo', function (Blueprint $table) {
            $table->string('whatsapp_numero', 20)->nullable()->after('color_acento');
            $table->text('whatsapp_mensaje')->nullable()->after('whatsapp_numero');
        });
    }

    public function down(): void
    {
        Schema::table('web_popup_promo', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_numero', 'whatsapp_mensaje']);
        });
    }
};

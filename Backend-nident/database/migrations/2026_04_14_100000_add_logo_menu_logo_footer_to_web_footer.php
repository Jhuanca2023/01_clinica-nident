<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_footer', function (Blueprint $table) {
            $table->string('logo_menu', 500)->nullable()->after('url_mapa');
            $table->string('logo_footer', 500)->nullable()->after('logo_menu');
        });
    }

    public function down(): void
    {
        Schema::table('web_footer', function (Blueprint $table) {
            $table->dropColumn(['logo_menu', 'logo_footer']);
        });
    }
};

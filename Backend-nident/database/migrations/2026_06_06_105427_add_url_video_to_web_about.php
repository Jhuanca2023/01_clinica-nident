<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('web_about', function (Blueprint $table) {
            $table->string('url_video', 500)->nullable()->after('btn_url')
                  ->comment('URL del video (YouTube embed, etc.) para el botón play');
            $table->tinyInteger('mostrar_video')->default(0)->after('url_video')
                  ->comment('1 = mostrar botón play + video, 0 = solo imagen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_about', function (Blueprint $table) {
            $table->dropColumn(['url_video', 'mostrar_video']);
        });
    }
};

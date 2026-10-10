<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('web_about')) {
            return;
        }

        Schema::table('web_about', function (Blueprint $table) {
            if (!Schema::hasColumn('web_about', 'url_imagen_2')) {
                $table->string('url_imagen_2', 500)->nullable()->after('url_imagen');
            }
            if (!Schema::hasColumn('web_about', 'url_video')) {
                $table->string('url_video', 500)->nullable()->after('btn_url');
            }
            if (!Schema::hasColumn('web_about', 'mostrar_video')) {
                $table->tinyInteger('mostrar_video')->default(0)->after('url_video');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('web_about')) {
            return;
        }

        Schema::table('web_about', function (Blueprint $table) {
            foreach (['url_imagen_2', 'url_video', 'mostrar_video'] as $col) {
                if (Schema::hasColumn('web_about', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

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
        Schema::table('web_footer', function (Blueprint $table) {
            $table->string('url_imagen_central', 500)->nullable()->after('logo_footer')
                  ->comment('Imagen decorativa de la columna central del footer');
        });
    }

    public function down(): void
    {
        Schema::table('web_footer', function (Blueprint $table) {
            $table->dropColumn('url_imagen_central');
        });
    }
};

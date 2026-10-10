<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_testimonios', function (Blueprint $table) {
            $table->enum('tipo', ['texto', 'captura'])->default('texto')->after('id_testimonio');
            $table->string('url_captura', 500)->nullable()->after('url_avatar');
        });
    }

    public function down(): void
    {
        Schema::table('web_testimonios', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'url_captura']);
        });
    }
};

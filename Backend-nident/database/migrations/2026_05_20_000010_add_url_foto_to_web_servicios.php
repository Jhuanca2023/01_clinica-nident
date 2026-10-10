<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_servicios', function (Blueprint $table) {
            $table->string('url_foto', 500)->nullable()->after('url_icono');
        });

        // Seed default static photos for the two cards that show big photos
        DB::table('web_servicios')->where('orden', 1)
            ->update(['url_foto' => 'temp02/assets/img/experience-1.jpg']);
        DB::table('web_servicios')->where('orden', 4)
            ->update(['url_foto' => 'temp02/assets/img/experience-2.jpg']);
    }

    public function down(): void
    {
        Schema::table('web_servicios', function (Blueprint $table) {
            $table->dropColumn('url_foto');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_footer', function (Blueprint $table) {
            $table->string('red_social_tiktok', 500)->nullable()->after('red_social_linkedin');
        });
    }

    public function down(): void
    {
        Schema::table('web_footer', function (Blueprint $table) {
            $table->dropColumn('red_social_tiktok');
        });
    }
};

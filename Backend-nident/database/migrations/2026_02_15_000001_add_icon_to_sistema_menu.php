<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sistema_menu', function (Blueprint $table) {
            if (!Schema::hasColumn('sistema_menu', 'Icon')) {
                $table->string('Icon', 64)->nullable()->after('url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sistema_menu', function (Blueprint $table) {
            $table->dropColumn('Icon');
        });
    }
};

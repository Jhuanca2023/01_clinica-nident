<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sistema_modulo', function (Blueprint $table) {
            if (!Schema::hasColumn('sistema_modulo', 'url')) {
                $table->string('url', 500)->nullable()->after('nombre');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sistema_modulo', function (Blueprint $table) {
            $table->dropColumn('url');
        });
    }
};

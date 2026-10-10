<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La columna puede haberse añadido manualmente en BD; se agrega solo si no existe.
        if (! Schema::hasColumn('web_footer', 'customizer_styles')) {
            Schema::table('web_footer', function (Blueprint $table) {
                $table->text('customizer_styles')->nullable()->after('logo_footer');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('web_footer', 'customizer_styles')) {
            Schema::table('web_footer', function (Blueprint $table) {
                $table->dropColumn('customizer_styles');
            });
        }
    }
};

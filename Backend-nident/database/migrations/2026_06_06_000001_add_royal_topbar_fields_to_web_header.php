<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('web_header')) {
            return;
        }

        Schema::table('web_header', function (Blueprint $table) {
            if (!Schema::hasColumn('web_header', 'topbar_mensaje_centro')) {
                $table->string('topbar_mensaje_centro', 500)->nullable()->after('topbar_promo');
            }
            if (!Schema::hasColumn('web_header', 'topbar_wa_numero')) {
                $table->string('topbar_wa_numero', 60)->nullable()->after('topbar_mensaje_centro');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('web_header')) {
            return;
        }

        Schema::table('web_header', function (Blueprint $table) {
            foreach (['topbar_mensaje_centro', 'topbar_wa_numero'] as $col) {
                if (Schema::hasColumn('web_header', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

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
        Schema::table('web_header', function (Blueprint $table) {
            if (!Schema::hasColumn('web_header', 'topbar_wa_texto'))
                $table->string('topbar_wa_texto', 100)->nullable()->after('url_contacto_top');
            if (!Schema::hasColumn('web_header', 'topbar_cta_texto'))
                $table->string('topbar_cta_texto', 100)->nullable()->after('topbar_wa_texto');
            if (!Schema::hasColumn('web_header', 'topbar_bgcolor'))
                $table->string('topbar_bgcolor', 30)->nullable()->default('#1e3a8a')->after('topbar_cta_texto');
            if (!Schema::hasColumn('web_header', 'topbar_btn_bgcolor'))
                $table->string('topbar_btn_bgcolor', 30)->nullable()->default('#e11d48')->after('topbar_bgcolor');
            if (!Schema::hasColumn('web_header', 'nav_bgcolor'))
                $table->string('nav_bgcolor', 30)->nullable()->default('#ffffff')->after('topbar_btn_bgcolor');
            if (!Schema::hasColumn('web_header', 'nav_link_color'))
                $table->string('nav_link_color', 30)->nullable()->default('#1e293b')->after('nav_bgcolor');
        });
    }

    public function down(): void
    {
        Schema::table('web_header', function (Blueprint $table) {
            $table->dropColumn([
                'topbar_wa_texto', 'topbar_cta_texto',
                'topbar_bgcolor', 'topbar_btn_bgcolor',
                'nav_bgcolor', 'nav_link_color',
            ]);
        });
    }
};

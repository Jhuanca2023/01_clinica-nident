<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_header') || ! Schema::hasColumn('web_header', 'topbar_bgcolor')) {
            return;
        }

        DB::table('web_header')->where('id_header', 1)->update([
            'topbar_bgcolor' => '#001f60',
            'nav_bgcolor'    => '#001f60',
            'nav_link_color' => '#ffffff',
            'updated_at'     => now(),
        ]);
    }

    public function down(): void
    {
        // Sin reversión: color corporativo Liam.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_header')) {
            return;
        }

        $update = ['updated_at' => now()];

        if (Schema::hasColumn('web_header', 'nav_bgcolor')) {
            $update['nav_bgcolor'] = '#001f60';
        }
        if (Schema::hasColumn('web_header', 'nav_link_color')) {
            $update['nav_link_color'] = '#ffffff';
        }

        DB::table('web_header')->where('id_header', 1)->update($update);
    }

    public function down(): void
    {
        // Sin reversión: color corporativo Liam.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('web_pagina_nosotros')) {
            return;
        }

        $banner = 'temp02/assets/img/jh_importaciones/inicio/seccion 2.jpg';
        if (!file_exists(public_path($banner))) {
            $banner = 'temp02/assets/img/banner/2.jpg';
        }

        DB::table('web_pagina_nosotros')->where('id', 1)->update([
            'banner_url_imagen' => $banner,
            'banner_titulo'     => 'Nosotros',
            'updated_at'        => now(),
        ]);
    }

    public function down(): void
    {
        //
    }
};

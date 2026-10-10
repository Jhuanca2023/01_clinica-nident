<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('web_cliente_logo') || DB::table('web_cliente_logo')->count() > 0) {
            return;
        }

        $wa = 'https://wa.me/51981629466';
        $nombres = [
            'Cliente 1', 'Cliente 2', 'Cliente 3', 'Cliente 4',
            'Cliente 5', 'Cliente 6', 'Cliente 7', 'Cliente 8',
        ];

        foreach ($nombres as $i => $nombre) {
            $num = $i + 1;
            DB::table('web_cliente_logo')->insert([
                'nombre'     => $nombre,
                'url_imagen' => "temp02/assets/img/clients/{$num}.png",
                'url_enlace' => $wa,
                'orden'      => $num,
                'Activo'     => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('web_cliente_seccion')) {
            DB::table('web_cliente_seccion')->where('id', 1)->update([
                'cta_url'    => $wa,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('web_cliente_logo')) {
            DB::table('web_cliente_logo')->where('url_imagen', 'like', 'temp02/assets/img/clients/%')->delete();
        }
    }
};

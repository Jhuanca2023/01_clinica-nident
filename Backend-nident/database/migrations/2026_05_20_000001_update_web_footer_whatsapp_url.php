<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $mensaje = 'Hola valentostile, quiero información sobre sus servicios.';
        $url = 'https://wa.me/51939691536?text=' . rawurlencode($mensaje);

        DB::table('web_footer')
            ->where('id_footer', 1)
            ->update(['url_whatsapp' => $url]);
    }

    public function down(): void
    {
        DB::table('web_footer')
            ->where('id_footer', 1)
            ->update(['url_whatsapp' => '#']);
    }
};

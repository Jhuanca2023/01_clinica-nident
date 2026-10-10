<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            1 => 'temp02/assets/img/works-icon-1.png',
            2 => 'temp02/assets/img/works-icon-2.png',
            3 => 'temp02/assets/img/works-icon-3.png',
        ];

        foreach ($defaults as $paso => $url) {
            DB::table('web_metodologia')
                ->where('paso', $paso)
                ->where(function ($q) {
                    $q->whereNull('url_imagen')->orWhere('url_imagen', '');
                })
                ->update(['url_imagen' => $url, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // No revert: las rutas por defecto son válidas en producción.
    }
};

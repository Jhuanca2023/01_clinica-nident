<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Slides de Valento sin imagen: usar las del hero de la plantilla temp02 (index.html).
 * Las fotos locales temp02/img/inicio/1-3.jpg eran del cliente anterior.
 */
return new class extends Migration
{
    private const IMAGENES = [
        'Ropa laboral para tu empresa'    => 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=1600&q=75',
        'Textil promocional con tu logo'  => 'https://images.unsplash.com/photo-1562157873-818bc0726f68?auto=format&fit=crop&w=1600&q=75',
        'Stock permanente, despacho ágil' => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?auto=format&fit=crop&w=1600&q=75',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('web_slider')) {
            return;
        }

        foreach (self::IMAGENES as $titulo => $imagen) {
            DB::table('web_slider')
                ->where('titulo', $titulo)
                ->where(fn ($q) => $q->whereNull('url_imagen')->orWhere('url_imagen', '')->orWhere('url_imagen', 'like', 'temp02/img/inicio/%'))
                ->update(['url_imagen' => $imagen]);
        }
    }

    public function down(): void
    {
        //
    }
};

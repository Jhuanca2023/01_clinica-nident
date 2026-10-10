<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('web_testimonios')) return;

        if (!Schema::hasColumn('web_testimonios', 'seccion_eyebrow')) {
            Schema::table('web_testimonios', function (Blueprint $table) {
                $table->string('seccion_eyebrow', 200)->nullable();
            });
        }

        // Reseñas iniciales de Rossety (las que antes estaban fijas en la web).
        if (DB::table('web_testimonios')->where('badge_seccion', 'rossety')->exists()) return;
        $orden = (int) DB::table('web_testimonios')->max('orden');
        $base = [
            'tipo' => 'texto', 'badge_seccion' => 'rossety', 'calificacion' => 5.0, 'Activo' => 'S',
            'seccion_titulo' => 'Lo que dicen nuestros clientes', 'seccion_eyebrow' => 'Opiniones',
            'created_at' => now(), 'updated_at' => now(),
        ];
        $items = [
            ['María Quispe', 'Huamanga', 'El ramo con Stich quedó hermoso y llegó a tiempo. Mi hija lloró de emoción. Súper recomendados.', 'rossety/assets/images/avatar/4.png'],
            ['Luis Huamán', 'Ayacucho', 'Pedí cajas temáticas por cantidad para el cumpleaños de mis sobrinos y el precio fue excelente.', 'rossety/assets/images/avatar/2.png'],
            ['Rosa Paredes', 'Relave', 'Atención muy amable por WhatsApp y los detalles son tal cual las fotos. Volveré a comprar.', 'rossety/assets/images/avatar/5.png'],
        ];
        foreach ($items as $i => [$nombre, $ciudad, $texto, $avatar]) {
            DB::table('web_testimonios')->insert($base + [
                'nombre' => $nombre, 'subtitulo' => $ciudad, 'testimonio' => $texto,
                'url_avatar' => $avatar, 'orden' => $orden + $i + 1,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('web_testimonios', 'seccion_eyebrow')) {
            Schema::table('web_testimonios', function (Blueprint $table) {
                $table->dropColumn('seccion_eyebrow');
            });
        }
    }
};

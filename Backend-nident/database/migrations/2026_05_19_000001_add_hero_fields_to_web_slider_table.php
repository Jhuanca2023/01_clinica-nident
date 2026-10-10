<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_slider', function (Blueprint $table) {
            $table->string('subtitulo', 255)->nullable()->after('titulo');
            $table->string('texto_boton', 120)->nullable()->after('url_link');
        });

        $defaults = [
            1 => [
                'subtitulo'   => 'Agencia de Marketing Digital · Breña, Lima',
                'titulo'      => '¿Publicas pero nadie te compra?',
                'descripcion' => 'No basta con subir diseños bonitos. Tu negocio necesita publicaciones que atraigan clientes, generen mensajes y conviertan interesados en ventas.',
                'texto_boton' => 'Escríbenos por WhatsApp',
                'url_link'    => '#',
            ],
            2 => [
                'subtitulo'   => 'Estrategia · Diseño · Resultados Reales',
                'titulo'      => 'Haz que tu marca brille en redes sociales',
                'descripcion' => 'Nos encargamos de todo: estrategia, diseño, contenido y resultados reales. Aumenta tu visibilidad y convierte seguidores en clientes.',
                'texto_boton' => 'Solicitar Asesoría Gratis',
                'url_link'    => 'http://bit.ly/Asesor%C3%ADa-valentostile',
            ],
            3 => [
                'subtitulo'   => 'Planes desde S/. 120 mensuales',
                'titulo'      => 'Nosotros hacemos que te escriban todos los días',
                'descripcion' => 'Campañas publicitarias, diseños profesionales, seguimiento diario y asesoría directa para que tu negocio venda más en redes sociales.',
                'texto_boton' => 'Ver Nuestros Planes',
                'url_link'    => '#pricing',
            ],
        ];

        foreach ($defaults as $id => $row) {
            DB::table('web_slider')->where('id_slider', $id)->update($row);
        }
    }

    public function down(): void
    {
        Schema::table('web_slider', function (Blueprint $table) {
            $table->dropColumn(['subtitulo', 'texto_boton']);
        });
    }
};

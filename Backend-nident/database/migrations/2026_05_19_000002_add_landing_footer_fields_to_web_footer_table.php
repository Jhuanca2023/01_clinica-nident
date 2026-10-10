<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_footer', function (Blueprint $table) {
            $table->string('contacto_telefono_secundario', 50)->nullable()->after('contacto_telefono');
            $table->string('url_whatsapp', 500)->nullable()->after('red_social_linkedin');
            $table->string('footer_cta_subtitulo', 255)->nullable()->after('url_whatsapp');
            $table->string('footer_cta_titulo', 500)->nullable()->after('footer_cta_subtitulo');
        });

        DB::table('web_footer')->where('id_footer', 1)->update([
            'sobre_la_empresa' => 'Agencia de marketing digital especializada en redes sociales. Ayudamos a negocios en Lima y todo el Perú a crecer, vender más y conectar con sus clientes.',
            'contacto_telefono' => '939691536',
            'contacto_telefono_secundario' => '901967564',
            'contacto_direccion' => 'Breña, Lima, Perú',
            'contacto_email' => '',
            'red_social_facebook' => 'https://www.facebook.com/socialmediaIG/',
            'red_social_instagram' => 'https://www.instagram.com/',
            'url_whatsapp' => '#',
            'footer_cta_subtitulo' => '¿Listo para que tu negocio explote en redes sociales?',
            'footer_cta_titulo' => 'Empieza hoy y recibe mensajes de clientes desde la primera semana',
            'nuestros_horarios' => 'Lunes a sábado · Atención por WhatsApp',
        ]);
    }

    public function down(): void
    {
        Schema::table('web_footer', function (Blueprint $table) {
            $table->dropColumn([
                'contacto_telefono_secundario',
                'url_whatsapp',
                'footer_cta_subtitulo',
                'footer_cta_titulo',
            ]);
        });
    }
};

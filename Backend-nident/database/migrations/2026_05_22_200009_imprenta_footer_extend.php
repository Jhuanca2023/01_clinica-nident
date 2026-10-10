<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('web_footer')) {
            return;
        }

        Schema::table('web_footer', function (Blueprint $table) {
            if (!Schema::hasColumn('web_footer', 'descripcion_footer')) {
                $table->text('descripcion_footer')->nullable();
            }
            if (!Schema::hasColumn('web_footer', 'titulo_sobre_nosotros')) {
                $table->string('titulo_sobre_nosotros', 120)->nullable();
            }
            if (!Schema::hasColumn('web_footer', 'etiqueta_redes')) {
                $table->string('etiqueta_redes', 120)->nullable();
            }
            if (!Schema::hasColumn('web_footer', 'titulo_llamanos')) {
                $table->string('titulo_llamanos', 120)->nullable();
            }
            if (!Schema::hasColumn('web_footer', 'titulo_escribenos')) {
                $table->string('titulo_escribenos', 120)->nullable();
            }
            if (!Schema::hasColumn('web_footer', 'titulo_ubicacion')) {
                $table->string('titulo_ubicacion', 120)->nullable();
            }
            if (!Schema::hasColumn('web_footer', 'footer_telefonos')) {
                $table->text('footer_telefonos')->nullable();
            }
            if (!Schema::hasColumn('web_footer', 'footer_emails')) {
                $table->text('footer_emails')->nullable();
            }
            if (!Schema::hasColumn('web_footer', 'footer_redes')) {
                $table->text('footer_redes')->nullable();
            }
            if (!Schema::hasColumn('web_footer', 'texto_copyright')) {
                $table->string('texto_copyright', 255)->nullable();
            }
        });

        $descripcion = 'En Liam Vercceli, distribuimos y fabricamos productos de alta calidad para tus necesidades de oficina. Con experiencia y compromiso, buscamos superar tus expectativas y ser tu aliado en cada proyecto. ¡Gracias por elegirnos!';

        $redes = json_encode([
            ['tipo' => 'facebook', 'url' => 'https://www.facebook.com/', 'etiqueta' => 'Facebook', 'orden' => 1],
            ['tipo' => 'whatsapp', 'url' => 'https://wa.me/51981629466', 'etiqueta' => 'WhatsApp', 'orden' => 2],
            ['tipo' => 'whatsapp', 'url' => 'https://wa.me/51958444413', 'etiqueta' => 'WhatsApp', 'orden' => 3],
            ['tipo' => 'whatsapp', 'url' => 'https://wa.me/51992886659', 'etiqueta' => 'WhatsApp', 'orden' => 4],
            ['tipo' => 'tiktok', 'url' => 'https://www.tiktok.com/', 'etiqueta' => 'TikTok', 'orden' => 5],
        ], JSON_UNESCAPED_UNICODE);

        $update = [
            'titulo_sobre_nosotros' => 'Sobre Nosotros',
            'descripcion_footer'    => $descripcion,
            'sobre_la_empresa'      => $descripcion,
            'etiqueta_redes'        => 'Nuestras Redes:',
            'titulo_llamanos'       => 'Llámanos',
            'titulo_escribenos'     => 'Escríbenos un mensaje',
            'titulo_ubicacion'      => 'Ubícanos',
            'footer_telefonos'      => "981629466\n958444413\n992886659",
            'footer_emails'         => "jyhimportaciones@hotmail.com\nhuber_06_15@hotmail.com",
            'contacto_telefono'     => '981629466',
            'contacto_email'        => 'jyhimportaciones@hotmail.com',
            'contacto_direccion'    => 'Jr. Junín 1776 interior 20, Cercado de Lima — al frente de la estación de tren.',
            'footer_redes'          => $redes,
            'url_whatsapp'          => 'https://wa.me/51981629466',
            'updated_at'            => now(),
        ];
        if (Schema::hasColumn('web_footer', 'texto_copyright')) {
            $update['texto_copyright'] = 'Web creada por valentostile';
        }

        $filtered = [];
        foreach ($update as $key => $val) {
            if (Schema::hasColumn('web_footer', $key)) {
                $filtered[$key] = $val;
            }
        }
        if (count($filtered) > 0) {
            DB::table('web_footer')->where('id_footer', 1)->update($filtered);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('web_footer')) {
            return;
        }

        Schema::table('web_footer', function (Blueprint $table) {
            foreach ([
                'titulo_sobre_nosotros', 'etiqueta_redes', 'titulo_llamanos',
                'titulo_escribenos', 'titulo_ubicacion', 'footer_telefonos',
                'footer_emails', 'footer_redes',
            ] as $col) {
                if (Schema::hasColumn('web_footer', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

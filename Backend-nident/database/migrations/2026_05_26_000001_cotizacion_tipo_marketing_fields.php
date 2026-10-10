<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cotizacion_tipo_proyecto')) {
            return;
        }

        Schema::table('cotizacion_tipo_proyecto', function (Blueprint $table) {
            if (!Schema::hasColumn('cotizacion_tipo_proyecto', 'subtexto_emocional')) {
                $table->text('subtexto_emocional')->nullable()->after('descripcion');
            }
            if (!Schema::hasColumn('cotizacion_tipo_proyecto', 'frase_destacada')) {
                $table->string('frase_destacada', 255)->nullable()->after('subtexto_emocional');
            }
            if (!Schema::hasColumn('cotizacion_tipo_proyecto', 'badge_etiqueta')) {
                $table->string('badge_etiqueta', 120)->nullable()->after('frase_destacada');
            }
            if (!Schema::hasColumn('cotizacion_tipo_proyecto', 'texto_boton')) {
                $table->string('texto_boton', 120)->nullable()->after('badge_etiqueta');
            }
        });

        $this->seedMarketingContent();
    }

    private function seedMarketingContent(): void
    {
        $planes = [
            'landing' => [
                'titulo'              => 'Landing Page',
                'descripcion'         => 'Convierte visitantes en clientes con una página moderna, rápida y enfocada en resultados.',
                'subtexto_emocional'  => 'Ideal para campañas, emprendimientos y negocios que desean generar más consultas y ventas.',
                'includes'            => [
                    'Diseño moderno y profesional',
                    'Integración directa con WhatsApp',
                    'Formularios para captar clientes',
                    'Adaptable a celulares y tablets',
                    'Dominio + Hosting incluido 1 año',
                    'SSL y seguridad incluida',
                ],
                'frase_destacada'     => 'Ideal para promocionar servicios rápidamente',
                'badge_etiqueta'      => null,
                'texto_boton'         => 'Quiero Mi Landing Page',
            ],
            'web' => [
                'titulo'              => 'Página Web Profesional',
                'descripcion'         => 'Impulsa la imagen digital de tu negocio con una web profesional y administrable.',
                'subtexto_emocional'  => 'Perfecta para empresas y negocios que desean transmitir confianza y presencia online.',
                'includes'            => [
                    'Diseño corporativo moderno',
                    'Panel administrador fácil de usar',
                    'Optimización para Google (SEO)',
                    'Compatible con celulares y PC',
                    'Correos corporativos profesionales',
                    'Hosting + Dominio incluido 1 año',
                ],
                'frase_destacada'     => null,
                'badge_etiqueta'      => 'Más solicitado',
                'texto_boton'         => 'Solicitar Mi Página Web',
            ],
            'tienda' => [
                'titulo'              => 'Tienda Virtual Básica',
                'descripcion'         => 'Empieza a vender tus productos por internet de forma profesional y sencilla.',
                'subtexto_emocional'  => 'Ideal para negocios que desean recibir pedidos online y aumentar sus ventas.',
                'includes'            => [
                    'Catálogo digital profesional',
                    'Pedidos por WhatsApp automáticos',
                    'Carrito de compras básico',
                    'Diseño adaptable a móviles',
                    'Hosting + Dominio incluido',
                    'Gestión sencilla de productos',
                ],
                'frase_destacada'     => 'Empieza a vender online hoy mismo',
                'badge_etiqueta'      => null,
                'texto_boton'         => 'Quiero Mi Tienda Online',
            ],
            'sistema' => [
                'titulo'              => 'Sistema Personalizado',
                'descripcion'         => 'Automatiza procesos y mejora la productividad de tu empresa con un sistema a medida.',
                'subtexto_emocional'  => 'Desarrollado según las necesidades y procesos específicos de tu negocio.',
                'includes'            => [
                    'Módulos personalizados',
                    'Panel administrativo avanzado',
                    'Automatización de procesos',
                    'Escalable y adaptable',
                    'Seguridad y control de usuarios',
                    'Integración con múltiples áreas',
                ],
                'frase_destacada'     => 'Soluciones empresariales a medida',
                'badge_etiqueta'      => null,
                'texto_boton'         => 'Solicitar Cotización',
            ],
            'aula' => [
                'titulo'              => 'Aula Virtual',
                'descripcion'         => 'Gestiona clases, alumnos y contenidos educativos desde una plataforma moderna y profesional.',
                'subtexto_emocional'  => 'Ideal para academias, colegios, capacitaciones y enseñanza online.',
                'includes'            => [
                    'Acceso para alumnos y docentes',
                    'Gestión de cursos y materiales',
                    'Panel administrativo educativo',
                    'Control de usuarios y permisos',
                    'Plataforma adaptable a móviles',
                    'Escalable según crecimiento',
                ],
                'frase_destacada'     => 'Lleva tu enseñanza al entorno digital',
                'badge_etiqueta'      => null,
                'texto_boton'         => 'Quiero Mi Aula Virtual',
            ],
        ];

        foreach ($planes as $slug => $data) {
            if (!DB::table('cotizacion_tipo_proyecto')->where('slug', $slug)->exists()) {
                continue;
            }
            DB::table('cotizacion_tipo_proyecto')->where('slug', $slug)->update([
                'titulo'             => $data['titulo'],
                'descripcion'        => $data['descripcion'],
                'subtexto_emocional' => $data['subtexto_emocional'],
                'includes'           => json_encode($data['includes'], JSON_UNESCAPED_UNICODE),
                'frase_destacada'    => $data['frase_destacada'],
                'badge_etiqueta'     => $data['badge_etiqueta'],
                'texto_boton'        => $data['texto_boton'],
                'updated_at'         => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('cotizacion_tipo_proyecto')) {
            return;
        }

        Schema::table('cotizacion_tipo_proyecto', function (Blueprint $table) {
            foreach (['texto_boton', 'badge_etiqueta', 'frase_destacada', 'subtexto_emocional'] as $col) {
                if (Schema::hasColumn('cotizacion_tipo_proyecto', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

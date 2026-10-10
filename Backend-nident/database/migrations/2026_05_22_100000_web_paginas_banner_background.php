<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $defaultBanner = 'temp02/assets/media/images/title-banner.jpg';

    public function up(): void
    {
        if (Schema::hasTable('web_pagina_nosotros') && !Schema::hasColumn('web_pagina_nosotros', 'banner_url_imagen')) {
            Schema::table('web_pagina_nosotros', function (Blueprint $table) {
                $table->string('banner_url_imagen', 500)->nullable()->after('banner_titulo');
            });
            DB::table('web_pagina_nosotros')->where('id', 1)->update([
                'banner_url_imagen' => $this->defaultBanner,
                'updated_at'        => now(),
            ]);
        }

        if (Schema::hasTable('web_pagina_contacto') && !Schema::hasColumn('web_pagina_contacto', 'banner_url_imagen')) {
            Schema::table('web_pagina_contacto', function (Blueprint $table) {
                $table->string('banner_url_imagen', 500)->nullable()->after('banner_titulo');
            });
            DB::table('web_pagina_contacto')->where('id', 1)->update([
                'banner_url_imagen' => $this->defaultBanner,
                'updated_at'        => now(),
            ]);
        }

        if (!Schema::hasTable('web_pagina_publicaciones_banner')) {
            Schema::create('web_pagina_publicaciones_banner', function (Blueprint $table) {
                $table->increments('id');
                $table->string('listado_banner_titulo', 255)->nullable();
                $table->string('listado_banner_url_imagen', 500)->nullable();
                $table->string('detalle_banner_url_imagen', 500)->nullable();
                $table->timestamps();
            });
            DB::table('web_pagina_publicaciones_banner')->insert([
                'listado_banner_titulo'       => 'Publicaciones',
                'listado_banner_url_imagen'   => $this->defaultBanner,
                'detalle_banner_url_imagen'   => $this->defaultBanner,
                'created_at'                  => now(),
                'updated_at'                  => now(),
            ]);
        }

        if (Schema::hasTable('web_publicaciones') && !Schema::hasColumn('web_publicaciones', 'banner_url_imagen')) {
            Schema::table('web_publicaciones', function (Blueprint $table) {
                $table->string('banner_url_imagen', 500)->nullable()->after('url_imagen');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('web_pagina_publicaciones_banner');

        foreach ([
            'web_pagina_nosotros' => 'banner_url_imagen',
            'web_pagina_contacto' => 'banner_url_imagen',
            'web_publicaciones'   => 'banner_url_imagen',
        ] as $table => $col) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $col)) {
                Schema::table($table, function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
    }
};

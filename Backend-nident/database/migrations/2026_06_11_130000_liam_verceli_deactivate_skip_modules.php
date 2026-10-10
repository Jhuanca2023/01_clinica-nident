<?php



use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;



return new class extends Migration

{

    private const SKIP_URLS = [

        '/testimonios/index',

        '/producto-destacado/index',

        '/producto-catalogo/index',

        '/linea-producto/index',

        '/promo-banner/index',

        '/experiencias/index',

        '/pagina-galeria/index',

        '/masaje-faq/index',

        '/pagina-experiencias/index',

        '/pagina-masajes/index',

        '/planes/index',

        '/portafolio/index',

        '/metodologia/index',

        '/servicios/index',

        '/ejemplares/index',

        '/contacto-landing/index',

    ];



    public function up(): void

    {

        if (! Schema::hasTable('sistema_menu')) {

            return;

        }



        DB::table('sistema_menu')->whereIn('url', self::SKIP_URLS)->update([

            'Activo'     => 'N',

            'updated_at' => now(),

        ]);

    }



    public function down(): void

    {

        if (! Schema::hasTable('sistema_menu')) {

            return;

        }



        DB::table('sistema_menu')->whereIn('url', self::SKIP_URLS)->update([

            'Activo'     => 'S',

            'updated_at' => now(),

        ]);

    }

};



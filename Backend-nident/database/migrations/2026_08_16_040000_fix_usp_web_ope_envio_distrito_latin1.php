<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Recrea USP_WEB_OPE_ENVIO_DISTRITO sin COLLATE utf8mb4_unicode_ci.
 * En cPanel oferta_precios_peso está en latin1 y esa collation fallaba (1253).
 */
return new class extends Migration
{
    public function up(): void
    {
        $file = database_path('procedures/USP_WEB_OPE_ENVIO_DISTRITO.sql');
        if (is_file($file)) {
            DB::unprepared((string) file_get_contents($file));
        }
    }

    public function down(): void
    {
        // El SP se gestiona desde database/procedures
    }
};

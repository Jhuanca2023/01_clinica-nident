<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Recrea USP_WEB_OPE_ENVIO_DISTRITO: acepta "Lima" como
 * "Lima Metropolitana" y no lanza SIGNAL si no hay filas.
 * artisan-clear.php también reaplica database/procedures/*.sql.
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Recrea USP_WEB_OPE_ENVIO_CALCULAR sin DEFINER de IP (error 1449 en cPanel)
 * y sin COLLATE utf8mb4 sobre columnas latin1.
 */
return new class extends Migration
{
    public function up(): void
    {
        $file = database_path('procedures/USP_WEB_OPE_ENVIO_CALCULAR.sql');
        if (is_file($file)) {
            DB::unprepared((string) file_get_contents($file));
        }
    }

    public function down(): void
    {
        // El SP se gestiona desde database/procedures
    }
};

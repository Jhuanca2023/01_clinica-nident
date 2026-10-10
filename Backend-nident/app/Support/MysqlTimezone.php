<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class MysqlTimezone
{
    /**
     * Alinea NOW() de MySQL con la zona horaria de la app (hostings en UTC).
     */
    public static function applySession(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        $timezone = config('database.connections.mysql.timezone', '-05:00');
        DB::statement('SET time_zone = ?', [$timezone]);
    }
}

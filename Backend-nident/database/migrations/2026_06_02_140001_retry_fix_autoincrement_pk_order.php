<?php

use App\Support\DatabaseAutoIncrementFixer;
use Illuminate\Database\Migrations\Migration;

/**
 * Reintento si las migraciones 120001/130001 fallaron con MySQL 1075 en producción.
 */
return new class extends Migration
{
    public function up(): void
    {
        DatabaseAutoIncrementFixer::fixAll();
    }

    public function down(): void
    {
        //
    }
};

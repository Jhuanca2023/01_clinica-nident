<?php

use App\Support\DatabaseAutoIncrementFixer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DatabaseAutoIncrementFixer::fix('administracion_producto_ficha_tecnica', 'id_ficha_tecnica');
    }

    public function down(): void
    {
        //
    }
};

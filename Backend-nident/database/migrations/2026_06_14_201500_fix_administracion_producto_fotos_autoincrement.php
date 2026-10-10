<?php

use App\Support\DatabaseAutoIncrementFixer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DatabaseAutoIncrementFixer::fix('administracion_producto_fotos', 'id_producto_foto');
    }

    public function down(): void
    {
        //
    }
};

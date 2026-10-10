<?php

use App\Support\DatabaseAutoIncrementFixer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DatabaseAutoIncrementFixer::fix('administracion_entidad_etiqueta', 'id');
    }

    public function down(): void
    {
        //
    }
};

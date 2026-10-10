<?php

use App\Support\DatabaseAutoIncrementFixer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DatabaseAutoIncrementFixer::fix('administracion_cliente_direcciones', 'id');
    }

    public function down(): void
    {
        //
    }
};

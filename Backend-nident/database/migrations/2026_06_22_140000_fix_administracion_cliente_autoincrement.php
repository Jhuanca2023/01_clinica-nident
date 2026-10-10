<?php

use App\Support\DatabaseAutoIncrementFixer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DatabaseAutoIncrementFixer::fix('administracion_cliente', 'id_cliente');
    }

    public function down(): void
    {
        // No revertir: la tabla requiere AUTO_INCREMENT en id_cliente.
    }
};

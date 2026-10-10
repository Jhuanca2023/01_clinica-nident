<?php

use App\Support\DatabaseAutoIncrementFixer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DatabaseAutoIncrementFixer::fix('cotizacion_presupuesto_qr_log', 'id');
    }

    public function down(): void
    {
        //
    }
};

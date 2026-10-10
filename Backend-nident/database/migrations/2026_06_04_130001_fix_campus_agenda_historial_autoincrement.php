<?php

use App\Support\DatabaseAutoIncrementFixer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'campus_agenda_historial'    => 'id',
            'campus_agenda_dependencias' => 'id',
            'campus_agenda_canvas'       => 'id',
        ] as $table => $column) {
            DatabaseAutoIncrementFixer::fix($table, $column);
        }
    }

    public function down(): void
    {
        //
    }
};

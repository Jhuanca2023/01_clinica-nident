<?php

use App\Support\DatabaseAutoIncrementFixer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DatabaseAutoIncrementFixer::fix('excalidraw_scenes', 'id_excalidraw_scene');
    }

    public function down(): void
    {
        //
    }
};

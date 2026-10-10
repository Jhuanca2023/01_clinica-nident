<?php

use App\Support\WebHomeColeccionSync;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_home_coleccion')) {
            return;
        }

        WebHomeColeccionSync::syncFromCatalogFallback(WebHomeColeccionSync::defaultLimit(), true);
    }

    public function down(): void
    {
        //
    }
};

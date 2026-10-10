<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('metadatos_paginas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_pagina')->nullable();
            $table->string('titulo_pagina')->nullable();
            $table->string('descripcion_pagina')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metadatos_paginas');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excalidraw_scenes', function (Blueprint $table) {
            $table->bigIncrements('id_excalidraw_scene');
            $table->unsignedBigInteger('id_proyecto')->unique();
            $table->longText('elements_json');
            $table->longText('app_state_json')->nullable();
            $table->longText('files_json')->nullable();
            $table->string('version', 20)->default('1.0');
            $table->unsignedBigInteger('guardado_por')->nullable();
            $table->timestamps();

            $table->foreign('id_proyecto')
                ->references('id_proyecto')
                ->on('campus_proyectos')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excalidraw_scenes');
    }
};

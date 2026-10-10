<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cms_media_holders')) {
            return;
        }

        Schema::create('cms_media_holders', function (Blueprint $table) {
            $table->id();
            $table->string('origen', 80)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_media_holders');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('web_slider_config')) {
            Schema::create('web_slider_config', function (Blueprint $table) {
                $table->unsignedTinyInteger('id')->primary();
                $table->char('autoplay', 1)->default('S');
                $table->unsignedInteger('interval_ms')->default(5000);
                $table->char('pause_on_hover', 1)->default('S');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('web_slider_config') && !DB::table('web_slider_config')->where('id', 1)->exists()) {
            DB::table('web_slider_config')->insert([
                'id'             => 1,
                'autoplay'       => 'S',
                'interval_ms'    => 5000,
                'pause_on_hover' => 'S',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('web_slider_config');
    }
};

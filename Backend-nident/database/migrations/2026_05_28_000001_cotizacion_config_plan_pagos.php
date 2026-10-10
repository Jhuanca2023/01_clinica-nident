<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizacion_config', function (Blueprint $table) {
            $table->json('plan_pagos_config')->nullable()->after('web_panel_admin');
        });
    }

    public function down(): void
    {
        Schema::table('cotizacion_config', function (Blueprint $table) {
            $table->dropColumn('plan_pagos_config');
        });
    }
};

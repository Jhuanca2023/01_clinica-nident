<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campus_onboarding_links', function (Blueprint $table) {
            $table->unsignedTinyInteger('num_cuotas')->default(1)->after('empresa_monto');
            $table->json('cuotas_detalle')->nullable()->after('num_cuotas');
        });
    }

    public function down(): void
    {
        Schema::table('campus_onboarding_links', function (Blueprint $table) {
            $table->dropColumn(['num_cuotas', 'cuotas_detalle']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campus_onboarding_links', function (Blueprint $table) {
            // Campos adicionales del formulario del cliente
            $table->string('form_marca',        150)->nullable()->after('form_direccion');
            $table->string('form_rubro',        150)->nullable()->after('form_marca');
            $table->text  ('form_desc_proyecto')    ->nullable()->after('form_rubro');
            $table->string('form_dominio',      255)->nullable()->after('form_desc_proyecto');
            $table->string('form_colores',      500)->nullable()->after('form_dominio');

            // Datos adicionales de pago del titular
            $table->string('empresa_yape',       30)->nullable()->after('empresa_descripcion');
            $table->string('empresa_dni',        20)->nullable()->after('empresa_yape');
            $table->string('empresa_bcp_cuenta', 40)->nullable()->after('empresa_dni');
            $table->string('empresa_bcp_cci',    40)->nullable()->after('empresa_bcp_cuenta');
        });
    }

    public function down(): void
    {
        Schema::table('campus_onboarding_links', function (Blueprint $table) {
            $table->dropColumn([
                'form_marca', 'form_rubro', 'form_desc_proyecto', 'form_dominio', 'form_colores',
                'empresa_yape', 'empresa_dni', 'empresa_bcp_cuenta', 'empresa_bcp_cci',
            ]);
        });
    }
};

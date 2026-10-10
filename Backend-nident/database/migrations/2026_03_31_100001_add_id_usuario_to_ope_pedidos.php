<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repartidor / usuario interno asignado al pedido (admin asigna desde el listado).
     */
    public function up(): void
    {
        Schema::table('ope_pedidos', function (Blueprint $table) {
            if (! Schema::hasColumn('ope_pedidos', 'id_usuario')) {
                $table->unsignedBigInteger('id_usuario')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ope_pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('ope_pedidos', 'id_usuario')) {
                $table->dropColumn('id_usuario');
            }
        });
    }
};

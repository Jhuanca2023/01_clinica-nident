<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('campus_cliente_proyecto')) {
            return;
        }

        Schema::table('campus_cliente_proyecto', function (Blueprint $table) {
            if (!Schema::hasColumn('campus_cliente_proyecto', 'id_cliente')) {
                $table->unsignedBigInteger('id_cliente')->nullable()->after('id');
            }
        });

        if (Schema::hasColumn('campus_cliente_proyecto', 'id_user')) {
            \Illuminate\Support\Facades\DB::statement(
                'ALTER TABLE campus_cliente_proyecto MODIFY id_user BIGINT UNSIGNED NULL'
            );
        }

        if (Schema::hasTable('campus_clientes') && Schema::hasColumn('campus_cliente_proyecto', 'id_cliente')) {
            Schema::table('campus_cliente_proyecto', function (Blueprint $table) {
                $foreignExists = collect(\Illuminate\Support\Facades\DB::select(
                    "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'campus_cliente_proyecto'
                     AND COLUMN_NAME = 'id_cliente' AND REFERENCED_TABLE_NAME IS NOT NULL"
                ))->isNotEmpty();

                if (!$foreignExists) {
                    $table->foreign('id_cliente')
                        ->references('id_cliente')
                        ->on('campus_clientes')
                        ->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('campus_cliente_proyecto')) {
            return;
        }

        Schema::table('campus_cliente_proyecto', function (Blueprint $table) {
            if (Schema::hasColumn('campus_cliente_proyecto', 'id_cliente')) {
                $table->dropForeign(['id_cliente']);
                $table->dropColumn('id_cliente');
            }
        });
    }
};

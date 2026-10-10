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
        Schema::table('administracion_producto', function (Blueprint $table) {
            if (!Schema::hasColumn('administracion_producto', 'ratings_enabled')) {
                $table->boolean('ratings_enabled')->default(false)->after('id_producto');
            }
            if (!Schema::hasColumn('administracion_producto', 'admin_rating')) {
                $table->decimal('admin_rating', 3, 1)->nullable()->after('ratings_enabled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('administracion_producto', function (Blueprint $table) {
            $table->dropColumn(['ratings_enabled', 'admin_rating']);
        });
    }
};

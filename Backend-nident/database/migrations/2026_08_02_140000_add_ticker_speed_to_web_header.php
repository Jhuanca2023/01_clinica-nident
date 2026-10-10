<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_header')) {
            return;
        }
        Schema::table('web_header', function (Blueprint $table) {
            if (! Schema::hasColumn('web_header', 'ticker_speed')) {
                $table->unsignedSmallInteger('ticker_speed')->default(45)->after('ticker_items');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('web_header')) {
            return;
        }
        Schema::table('web_header', function (Blueprint $table) {
            if (Schema::hasColumn('web_header', 'ticker_speed')) {
                $table->dropColumn('ticker_speed');
            }
        });
    }
};

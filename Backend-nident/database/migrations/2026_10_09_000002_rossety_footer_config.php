<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { if (!Schema::hasColumn('web_footer', 'rossety_footer_config')) Schema::table('web_footer', function(Blueprint $table) { $table->text('rossety_footer_config')->nullable(); }); }
 public function down(): void { Schema::table('web_footer', function(Blueprint $table) { $table->dropColumn('rossety_footer_config'); }); }
};

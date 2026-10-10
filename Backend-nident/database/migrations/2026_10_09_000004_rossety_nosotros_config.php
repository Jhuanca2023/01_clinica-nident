<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('web_pagina_nosotros') && !Schema::hasColumn('web_pagina_nosotros', 'rossety_nosotros')) {
            Schema::table('web_pagina_nosotros', function (Blueprint $table) {
                $table->longText('rossety_nosotros')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('web_pagina_nosotros', 'rossety_nosotros')) {
            Schema::table('web_pagina_nosotros', function (Blueprint $table) {
                $table->dropColumn('rossety_nosotros');
            });
        }
    }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('web_pagina_contacto') && !Schema::hasColumn('web_pagina_contacto', 'rossety_contacto')) {
            Schema::table('web_pagina_contacto', function (Blueprint $table) {
                $table->longText('rossety_contacto')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('web_pagina_contacto', 'rossety_contacto')) {
            Schema::table('web_pagina_contacto', function (Blueprint $table) {
                $table->dropColumn('rossety_contacto');
            });
        }
    }
};

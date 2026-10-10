<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('web_footer')) {
            return;
        }

        if (!Schema::hasColumn('web_footer', 'whatsapp_mensaje')) {
            Schema::table('web_footer', function (Blueprint $table) {
                $table->text('whatsapp_mensaje')->nullable()->after('url_whatsapp');
            });
        }

        $mensaje = '¡Hola! Les escribo desde la web de Liam Vercceli. '
            . 'Me gustaría recibir información y asesoría sobre sus productos y servicios. ¡Muchas gracias!';

        DB::table('web_footer')->where('id_footer', 1)->update([
            'whatsapp_mensaje' => $mensaje,
            'updated_at'       => now(),
        ]);
    }

    public function down(): void
    {
        if (Schema::hasTable('web_footer') && Schema::hasColumn('web_footer', 'whatsapp_mensaje')) {
            Schema::table('web_footer', function (Blueprint $table) {
                $table->dropColumn('whatsapp_mensaje');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_whatsapp_config')) {
            Schema::create('web_whatsapp_config', function (Blueprint $table) {
                $table->id();
                $table->string('wa_numero', 30)->default('51999999999');
                $table->text('wa_mensaje')->nullable();
                $table->string('wa_burbuja_linea1', 200)->nullable();
                $table->string('wa_burbuja_linea2', 200)->nullable();
                $table->string('wa_label', 50)->default('WhatsApp');
                $table->string('wa_badge', 10)->default('1');
                $table->decimal('wa_delay_segundos', 4, 1)->default(2.5);
                $table->enum('Activo', ['S', 'N'])->default('S');
                $table->timestamps();
            });

            DB::table('web_whatsapp_config')->insert([
                'wa_numero'         => '51999999999',
                'wa_mensaje'        => '¡Hola Liam Vercceli! Me gustaría recibir más información sobre sus productos.',
                'wa_burbuja_linea1' => '👕 ¿Buscas polos de calidad?',
                'wa_burbuja_linea2' => '¡Escríbenos y te asesoramos!',
                'wa_label'          => 'WhatsApp',
                'wa_badge'          => '1',
                'wa_delay_segundos' => 2.5,
                'Activo'            => 'S',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        $this->seedMenu();
    }

    private function seedMenu(): void
    {
        if (! Schema::hasTable('sistema_menu')) {
            return;
        }

        $url = '/whatsapp/index';
        if (DB::table('sistema_menu')->where('url', $url)->exists()) {
            return;
        }

        $idModulo = null;
        if (Schema::hasTable('sistema_modulo')) {
            $idModulo = DB::table('sistema_modulo')
                ->where('nombre', 'like', '%Tienda%')
                ->orWhere('nombre', 'like', '%Paginas%')
                ->orWhere('nombre', 'like', '%Páginas%')
                ->value('id_modulo');
        }

        $maxOrden = (int) DB::table('sistema_menu')->max('orden');

        $data = [
            'nombre'     => 'WhatsApp',
            'url'        => $url,
            'Activo'     => 'S',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('sistema_menu', 'id_modulo')) {
            $data['id_modulo'] = $idModulo;
        }
        if (Schema::hasColumn('sistema_menu', 'Icon')) {
            $data['Icon'] = 'chat';
        }
        if (Schema::hasColumn('sistema_menu', 'orden')) {
            $data['orden'] = $maxOrden + 1;
        }
        if (Schema::hasColumn('sistema_menu', 'id_menu_padre')) {
            $data['id_menu_padre'] = null;
        }

        $idMenu = DB::table('sistema_menu')->insertGetId($data);

        if (Schema::hasTable('seguridad_roles_menu') && Schema::hasTable('seguridad_roles')) {
            $roles = DB::table('seguridad_roles')->pluck('id_roles');
            foreach ($roles as $idRoles) {
                if (! DB::table('seguridad_roles_menu')->where('id_roles', $idRoles)->where('id_menu', $idMenu)->exists()) {
                    DB::table('seguridad_roles_menu')->insert([
                        'id_roles' => $idRoles,
                        'id_menu'  => $idMenu,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sistema_menu')) {
            DB::table('sistema_menu')->where('url', '/whatsapp/index')->delete();
        }
        Schema::dropIfExists('web_whatsapp_config');
    }
};

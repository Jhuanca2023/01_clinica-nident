<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('administracion_etiquetas')) {
            Schema::create('administracion_etiquetas', function (Blueprint $table) {
                $table->id('id_etiqueta');
                $table->string('nombre', 120);
                $table->string('slug', 120);
                $table->string('color', 20)->default('#6366f1');
                $table->unsignedBigInteger('id_roles')->nullable()->comment('NULL = recomendada global; valor = etiqueta del rol');
                $table->char('es_recomendada', 1)->default('N');
                $table->char('Activo', 1)->default('S');
                $table->timestamps();
                $table->index(['id_roles', 'Activo']);
                $table->unique(['slug', 'id_roles']);
            });
        }

        if (!Schema::hasTable('administracion_entidad_etiqueta')) {
            Schema::create('administracion_entidad_etiqueta', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_etiqueta');
                $table->unsignedBigInteger('id_menu')->nullable();
                $table->unsignedBigInteger('id_modulo')->nullable();
                $table->unsignedBigInteger('id_roles');
                $table->timestamps();
                $table->index(['id_roles', 'id_menu']);
                $table->index(['id_roles', 'id_modulo']);
                $table->unique(['id_etiqueta', 'id_menu', 'id_roles'], 'ent_etiq_menu_rol_uq');
                $table->unique(['id_etiqueta', 'id_modulo', 'id_roles'], 'ent_etiq_mod_rol_uq');
            });
        }

        $this->seedEtiquetasRecomendadas();
        $this->backfillEtiquetasPorRol();
        $this->seedMenuAdministracionEtiquetas();
    }

    public function down(): void
    {
        if (Schema::hasTable('sistema_menu')) {
            foreach (['/administracion-etiquetas/index'] as $url) {
                $ids = DB::table('sistema_menu')->where('url', $url)->pluck('id_menu');
                foreach ($ids as $idMenu) {
                    if (Schema::hasTable('seguridad_roles_menu')) {
                        DB::table('seguridad_roles_menu')->where('id_menu', $idMenu)->delete();
                    }
                }
                DB::table('sistema_menu')->where('url', $url)->delete();
            }
        }
        Schema::dropIfExists('administracion_entidad_etiqueta');
        Schema::dropIfExists('administracion_etiquetas');
    }

    private function seedEtiquetasRecomendadas(): void
    {
        $path = database_path('seeders/data/etiquetas_recomendadas.json');
        if (!is_file($path)) {
            return;
        }
        $items = json_decode(file_get_contents($path), true);
        if (!is_array($items)) {
            return;
        }

        $now = now();
        foreach ($items as $item) {
            $nombre = trim((string) ($item['nombre'] ?? ''));
            if ($nombre === '') {
                continue;
            }
            $slug = Str::slug($nombre);
            $exists = DB::table('administracion_etiquetas')
                ->where('slug', $slug)
                ->whereNull('id_roles')
                ->exists();
            if ($exists) {
                continue;
            }
            DB::table('administracion_etiquetas')->insert([
                'nombre'         => $nombre,
                'slug'           => $slug,
                'color'          => $item['color'] ?? '#6366f1',
                'id_roles'       => null,
                'es_recomendada' => 'S',
                'Activo'         => 'S',
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    private function backfillEtiquetasPorRol(): void
    {
        if (!Schema::hasTable('administracion_entidad_etiqueta')) {
            return;
        }

        $tagIds = DB::table('administracion_etiquetas')->where('Activo', 'S')->pluck('id_etiqueta')->all();
        if (count($tagIds) < 1) {
            return;
        }

        $roles = Schema::hasTable('seguridad_roles')
            ? DB::table('seguridad_roles')->pluck('id_roles')->all()
            : [1];

        $now = now();

        foreach ($roles as $idRoles) {
            $idRoles = (int) $idRoles;
            if ($idRoles < 1) {
                continue;
            }

            if (Schema::hasTable('sistema_menu')) {
                foreach (DB::table('sistema_menu')->pluck('id_menu') as $idMenu) {
                    $tiene = DB::table('administracion_entidad_etiqueta')
                        ->where('id_roles', $idRoles)
                        ->where('id_menu', $idMenu)
                        ->exists();
                    if ($tiene) {
                        continue;
                    }
                    $this->insertRandomTags($tagIds, $idRoles, (int) $idMenu, null, $now);
                }
            }

            if (Schema::hasTable('sistema_modulo')) {
                foreach (DB::table('sistema_modulo')->pluck('id_modulo') as $idModulo) {
                    $tiene = DB::table('administracion_entidad_etiqueta')
                        ->where('id_roles', $idRoles)
                        ->where('id_modulo', $idModulo)
                        ->exists();
                    if ($tiene) {
                        continue;
                    }
                    $this->insertRandomTags($tagIds, $idRoles, null, (int) $idModulo, $now);
                }
            }
        }
    }

    private function insertRandomTags(array $tagIds, int $idRoles, ?int $idMenu, ?int $idModulo, $now): void
    {
        $pool = $tagIds;
        shuffle($pool);
        $cantidad = min(count($pool), random_int(1, 3));
        $elegidas = array_slice($pool, 0, $cantidad);

        foreach ($elegidas as $idEtiqueta) {
            DB::table('administracion_entidad_etiqueta')->insert([
                'id_etiqueta' => $idEtiqueta,
                'id_menu'     => $idMenu,
                'id_modulo'   => $idModulo,
                'id_roles'    => $idRoles,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    private function seedMenuAdministracionEtiquetas(): void
    {
        if (!Schema::hasTable('sistema_menu')) {
            return;
        }

        $url = '/administracion-etiquetas/index';
        if (DB::table('sistema_menu')->where('url', $url)->exists()) {
            return;
        }

        $idModulo = null;
        if (Schema::hasTable('sistema_modulo')) {
            $idModulo = DB::table('sistema_modulo')
                ->where(function ($q) {
                    $q->where('nombre', 'like', '%Admin%')
                        ->orWhere('nombre', 'like', '%Seguridad%')
                        ->orWhere('nombre', 'like', '%Sistema%');
                })
                ->orderBy('id_modulo')
                ->value('id_modulo');
        }

        $maxOrden = (int) DB::table('sistema_menu')->max('orden');
        $data = [
            'nombre'     => 'Etiquetas menú',
            'url'        => $url,
            'Activo'     => 'S',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('sistema_menu', 'id_modulo')) {
            $data['id_modulo'] = $idModulo;
        }
        if (Schema::hasColumn('sistema_menu', 'Icon')) {
            $data['Icon'] = 'label';
        }
        if (Schema::hasColumn('sistema_menu', 'orden')) {
            $data['orden'] = $maxOrden + 1;
        }
        if (Schema::hasColumn('sistema_menu', 'id_menu_padre')) {
            $data['id_menu_padre'] = null;
        }

        $idMenu = DB::table('sistema_menu')->insertGetId($data);

        if (Schema::hasTable('seguridad_roles_menu')) {
            foreach (DB::table('seguridad_roles')->pluck('id_roles') as $idRoles) {
                if (!DB::table('seguridad_roles_menu')->where('id_roles', $idRoles)->where('id_menu', $idMenu)->exists()) {
                    DB::table('seguridad_roles_menu')->insert([
                        'id_roles' => $idRoles,
                        'id_menu'  => $idMenu,
                    ]);
                }
            }
        }
    }
};
